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
        $tokensettings = $DB->get_record('config_plugins', ['plugin' => 'local_levitate', 'name' => 'secret'], 'value');
        $tokenid = $tokensettings->value;
        /**
         * Task to create scorm activity in the course
         */
        function add_scorm_module($course, $name, $itemid, $descriptionhtml, $assessable, $section = 0, $scormcontentvalue=null) {
            global $CFG, $DB;
            require_once($CFG->dirroot.'/mod/scorm/lib.php');
            require_once($CFG->dirroot . '/course/modlib.php');
            $moduleinfo = new \stdClass();
            $moduleinfo->name = $name;
            $moduleinfo->modulename = 'scorm';
            $moduleinfo->module = $DB->get_field('modules', 'id', ['name' => 'scorm'], MUST_EXIST);
            $moduleinfo->cmidnumber = "";

            $moduleinfo->visible = 1;
            $moduleinfo->section = $section;

            $moduleinfo->intro = '';
            $moduleinfo->introformat = FORMAT_HTML;

            $moduleinfo->popup = 1;
            $moduleinfo->width = 100;
            $moduleinfo->height = 100;
            $moduleinfo->skipview = 2;
            $moduleinfo->hidebrowse = 1;
            $moduleinfo->displaycoursestructure = 0;
            $moduleinfo->hidetoc = 3;
            $moduleinfo->nav = 1;
            $moduleinfo->displayactivityname = false;
            $moduleinfo->displayattemptstatus = 1;
            $moduleinfo->forcenewattempt = 1;
            $moduleinfo->maxattempt = 0;

            $moduleinfo->scormtype = SCORM_TYPE_LOCAL;
            $packagefile = storedfile($name, $itemid, $scormcontentvalue);
            $moduleinfo->packagefile = $packagefile->get_itemid();
            return add_moduleinfo($moduleinfo, $course);
        }
        /**
         * Task to create scorm activity in the course
         */
        function get_cmids ($clientname) {
            $curl = curl_init();
            curl_setopt_array($curl, [
            CURLOPT_URL => 'https://levitate.human-logic.com/webservice/rest/server.php?wstoken='
                                .$tokenid.'&wsfunction=mod_scormremote_get_courseids&moodlewsrestformat=json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => ['clientname' => $clientname],
            ]);
            $responsesub = curl_exec($curl);
            curl_close($curl);
            return $responsesub;
        }
        /**
         * Task to get tiny scorm using api
         */
        function get_tiny_scorm ($cmid, $tokenvalue) {

            $curl = curl_init();
                curl_setopt_array($curl, [
                CURLOPT_URL => 'https://levitate.human-logic.com/webservice/rest/server.php?wstoken='
                                    .$tokenvalue.'&wsfunction=mod_levitateserver_get_tiny_scorms',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => ['cmid' => $cmid],
                ]);

                $tinyscorm = curl_exec($curl);
                curl_close($curl);
                return $tinyscorm;
        }
        $taskdetails = $DB->get_records('levitate_task_details', ['taskexecuted' => 0]);
        $taskids = [];

        foreach ($taskdetails as $taskid => $tasks) {
            $taskids[] = $tasks->id;
            $formdata = json_decode($tasks->formdata);
            $coursedata = json_decode($tasks->coursedata);
            $contextids = json_decode($coursedata->context_id);
            $enrollusers = json_decode($coursedata->enrollusers);
            $query = "SELECT id, shortname FROM {course}";
            $courseshortnames = $DB->get_records_sql($query);
            foreach ($courseshortnames as $courseshort) {
                $shortnames[] = $courseshort->shortname;
            }
            if ($formdata->course_type == 1) {
                $this->log("ica me here  -> ");
                $coursedata = new \stdClass();
                $coursedata->coursetype = $formdata->course_type;
                $coursedata->category = $formdata->coursecategory;
                $coursedata->fullname = $formdata->coursefullname;
                $coursedata->shortname = $formdata->courseshortname;
                $coursedata->format = 'topics';
                $scormsection = 1;
                if (!in_array($formdata->courseshortname, $shortnames)) {
                    $newcourse = create_course($coursedata);
                    foreach ($contextids as $key => $cmid) {
                        $tinyscorm = get_tiny_scorm($cmid, $tokenid);
                        $output = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($enrollusers->$key));
                        add_scorm_module($newcourse, html_entity_decode($output, null, 'UTF-8'), '', '', '',
                                          $scormsection, $tinyscorm);
                    }
                }
            } else if ($formdata->course_type == 0) {
                foreach ($contextids as $key => $cmid) {
                    $output = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($enrollusers->$key));
                    $coursedata = new \stdClass();
                    $coursedata->coursetype = $formdata->course_type;
                    $coursedata->category = $formdata->coursecategory;
                    $coursedata->fullname = html_entity_decode($output, null, 'UTF-8');
                    $coursedata->shortname = html_entity_decode($output, null, 'UTF-8');
                    if ($formdata->courseformat == 0) {
                        $coursedata->format = 'singleactivity';
                        $coursedata->activitytype = 'scorm';
                        $scormsection = 0;
                    } else {
                        $coursedata->format = 'topics';
                        $scormsection = 1;
                    }

                    if (in_array($coursedata->shortname, $shortnames)) {
                        $coursedata->shortname = $coursedata->shortname.'_'.time();
                        $tinyscorm = get_tiny_scorm($cmid, $tokenid);
                        $newcourse = create_course($coursedata);
                        add_scorm_module($newcourse, html_entity_decode($output, null, 'UTF-8'), '', '', '',
                                          $scormsection, $tinyscorm);
                    }
                }
            }
        }
        foreach ($taskids as $taskid) {
            $dbupdate = new \stdclass();
            $dbupdate->id = $taskid;
            $dbupdate->taskexecuted = 1;
            $updated = $DB->update_record('levitate_task_details', $dbupdate);
        }
    }
}


