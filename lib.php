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

defined('MOODLE_INTERNAL') || die();
global $DB, $CFG;
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir . '/formslib.php');
/**
 * Task to store files
 */
 class local_levitate_form extends moodleform {
    /**
     * Instantiate simplehtml_form
     */
    public function definition() {
        global $DB, $CFG;
        $imageurls = optional_param('image_urls', '', PARAM_TEXT);
        $contextid = optional_param('context_id', '', PARAM_TEXT);
        $enrollusers = optional_param('enrollusers', '', PARAM_TEXT);
        $wstoken = optional_param('wstoken', '', PARAM_TEXT);
        $passdata = [
            "image_urls" => json_encode($imageurls),
            "context_id" => json_encode($contextid),
            "enrollusers" => json_encode($enrollusers),
            "wwstoken" => $wstoken,
        ];
        $mform = $this->_form;
        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'course_type', '',
                            get_string('multi_coursetype', 'local_levitate'), 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'course_type', '',
                            get_string('single_coursetype', 'local_levitate'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('coursetype', 'local_levitate'), [''], false);
        $mform->setDefault('course_type', 0);
        $mform->addHelpButton ( 'radioar', 'coursetype', 'local_levitate');
        $radioarray1 = [];
        $radioarray1[] = $mform->createElement('radio', 'courseformat', '',
                                   get_string('single_activity_course', 'local_levitate'), 0, $attributes);
        $radioarray1[] = $mform->createElement('radio', 'courseformat', '',
                                   get_string('multi_activity_course', 'local_levitate'), 1, $attributes);
        $mform->addGroup($radioarray1, 'radioar1', get_string('coursecreation', 'local_levitate'), [''], false);
        $mform->setDefault('courseformat', 0);
        $mform->addHelpButton ( 'radioar1', 'coursecreation', 'local_levitate');
        $mform->addElement('text', 'coursefullname', get_string('coursefullname', 'local_levitate'));
        $mform->addElement('text', 'courseshortname', get_string('courseshortname', 'local_levitate'));
        $coursecategories[0] = get_string('selectcategory', 'local_levitate');
        $query = "SELECT * FROM {course_categories}";
        $categories = $DB->get_records_sql($query);
        foreach ($categories as $category) {
            $coursecategories[$category->id] = $category->name;
        }
        $mform->addElement('select', 'coursecategory', get_string('coursecategory', 'local_levitate'), $coursecategories);
        $mform->addElement('hidden', 'previous_form_values', json_encode( $passdata ));
        $this->add_action_buttons(get_string('cancel', 'local_levitate'), get_string('submit', 'local_levitate'));
    }
}
function local_levitate_storedfile($name, $packageid, $scorm) {
    global $USER;

    $fs = get_file_storage();

    $itemid = file_get_unused_draft_itemid();
    $usercontext = context_user::instance($USER->id);
    $now = time();

    // Prepare file record.
    $record = new \stdClass();
    $record->filepath = "/";
    $record->filename = clean_filename($name . ".zip");
    $record->component = 'user';
    $record->filearea = 'draft';
    $record->itemid = $itemid;
    $record->license = "allrightsreserved";
    $record->author = get_string('author', 'local_levitate');
    $record->contextid = $usercontext->id;
    $record->timecreated = $now;
    $record->timemodified = $now;
    $record->userid = $USER->id;
    $record->sortorder = 0;

    return $fs->create_file_from_string($record, $scorm);
}
function local_levitate_curlcall($function_name = '',$jsondata='') {
    $tokensettings =get_config('local_levitate');
    $tokenid = $tokensettings->secret;
    $url = 'https://levitate.human-logic.com/webservice/rest/server.php?wstoken='.$tokenid.'&wsfunction='.$function_name.'&moodlewsrestformat=json';
    $curl = new curl();
    $response = $curl->post($url, $jsondata);
    return $response;
}
function local_levitate_get_option_text ($params, $idvalue) {
    foreach ($params as $trmparr) {
        $optiontext = $optiontext.'<li>
        <label class="common-customCheckbox">
            <input name="filter_checkbox" data-filtername="'.$idvalue.'" type="checkbox" value="'.$trmparr.'" />
            <span>'.$trmparr.'</span>
            <div class="common-checkboxIndicator"></div>
        </label>
    </li>';
    }
    return $optiontext;
}
function local_levitate_add_scorm_module($course, $name, $itemid, $descriptionhtml, $assessable, $section = 0, $scormcontentvalue=null) {
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
    $packagefile = local_levitate_storedfile($name, $itemid, $scormcontentvalue);
    $moduleinfo->packagefile = $packagefile->get_itemid();
    return add_moduleinfo($moduleinfo, $course);
}

