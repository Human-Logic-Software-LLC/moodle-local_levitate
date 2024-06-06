<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Plugin administration pages are defined here.
 *
 * @package     local_levitate
 * @copyright   2023, Human Logic Software LLC
 * @author     Sreenu Malae <sreenivas@human-logic.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_levitate\task;

use context_system;
use dml_exception;
/**
 * Task to create courses which are selected by user
 */
class create_course extends \core\task\scheduled_task {

    // Use the logging trait to get some nice, juicy, logging.
    use \core\task\logging_trait;

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('create_course_task', 'local_levitate');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/local/levitate/lib.php');
        require_once($CFG->libdir . '/datalib.php');
        $tokensettings = get_config('local_levitate');
        $tokenid = $tokensettings->secret;
        $taskdetails = $DB->get_records('local_levitate_task_details', ['taskexecuted' => 0]);
        $taskids = [];

        foreach ($taskdetails as $taskid => $tasks) {
            $taskids[] = $tasks->id;
            $formdata = json_decode($tasks->formdata);
            $coursedata = json_decode($tasks->coursedata);
            $contextids = json_decode($coursedata->context_id);
            $enrollusers = json_decode($coursedata->enrollusers);
            $coursedescvalues = json_decode($coursedata->coursedescvalue);
            $image_urls = json_decode($coursedata->image_urls);
            $courseshortnames = get_courses();
            foreach ($courseshortnames as $courseshort) {
                $shortnames[] = $courseshort->shortname;
            }
            // Take the default category in case the given does not exist.
            $category = $DB->record_exists('course_categories', ['id' => $formdata->coursecategory]) ?
                $formdata->coursecategory :
                \core_course_category::get_default()->id;
            if ($formdata->course_type == 1) {
                $coursedata = new \stdClass();
                $coursedata->coursetype = $formdata->course_type;
                $coursedata->category = $category;
                $coursedata->fullname = $formdata->coursefullname;
                $coursedata->shortname = $formdata->courseshortname;
                $coursedata->format = 'topics';
                $scormsection = 1;
                if (!in_array($formdata->courseshortname, $shortnames)) {
                    $newcourse = create_course($coursedata);
                    foreach ($contextids as $key => $cmid) {
                        $this->log($cmid);
                        $jsondata = ['cmid' => $cmid];
                        $tinyscorm = local_levitate_curlcall('mod_levitateserver_get_tiny_scorms', $jsondata);
                        $output = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($enrollusers->$key));
                        local_levitate_add_scorm_module($newcourse, html_entity_decode($output, ENT_COMPAT, 'UTF-8'), '', '', '',
                                           $scormsection, $tinyscorm);
                    }
                }
            } else if ($formdata->course_type == 0) {
                foreach ($contextids as $key => $cmid) {
                    $output = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($enrollusers->$key));
                    $coursedata = new \stdClass();
                    $coursedata->coursetype = $formdata->course_type;
                    $coursedata->category = $category;
                    $coursedata->fullname = html_entity_decode($output, ENT_COMPAT, 'UTF-8');
                    $coursedata->shortname = html_entity_decode($output, ENT_COMPAT, 'UTF-8');
                    $coursedata->summary = $coursedescvalues->$key;

                    if ($formdata->courseformat == 0) {
                        $coursedata->format = 'singleactivity';
                        $coursedata->activitytype = 'scorm';
                        $scormsection = 0;
                    } else {
                        $coursedata->format = 'topics';
                        $scormsection = 1;
                    }
                    $coursedata->shortname = $coursedata->shortname.'_'.time();
                    $jsondata = ['cmid' => $cmid];
                    $tinyscorm = local_levitate_curlcall('mod_levitateserver_get_tiny_scorms', $jsondata);
                    $newcourse = create_course($coursedata);
                   
                    if($newcourse->id){
                        mtrace("Course Created {$newcourse->id}...");
                        
                    local_levitate_add_scorm_module($newcourse, html_entity_decode($output, ENT_COMPAT, 'UTF-8'), '', '', '',
                                        $scormsection, $tinyscorm);
                        $context = \context_course::instance($newcourse->id, MUST_EXIST);
                        
                        // $draftitemid = file_get_unused_draft_itemid();
                            $file = file_get_contents(urldecode($image_urls->$key)); // to get file
                        // $name = basename($url); // to get file name
                        $ext = pathinfo(urldecode($image_urls->$key), PATHINFO_EXTENSION); // to get extension
                        $name =pathinfo(urldecode($image_urls->$key), PATHINFO_FILENAME);
                        $timestamp =time();

                        $image = file_get_contents(urldecode($image_urls->$key));
                        file_put_contents($CFG->dataroot.'/'.$name.'_'.$timestamp.'.'.$ext, $image);
                        $fs = get_file_storage();
                        $fileinfo = array(
                                    'contextid' => $context->id, 
                                    'component' => 'course',
                                    'filearea' => 'overviewfiles',
                                    'itemid' => 0,
                                    'filepath' => '/',
                                    'filename' => $name.'_'.$timestamp.'.'.$ext);

                        $fileuploaded = $fs->create_file_from_pathname($fileinfo, $CFG->dataroot.'/'.$name.'_'.$timestamp.'.'.$ext);

                    }

                }
            }
        }
        foreach ($taskids as $taskid) {
            $dbupdate = new \stdclass();
            $dbupdate->id = $taskid;
            $dbupdate->taskexecuted = 1;
            $updated = $DB->update_record('local_levitate_task_details', $dbupdate);
        }
    }
}
