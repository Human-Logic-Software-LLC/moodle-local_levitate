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
        $mform = $this->_form;
        $passdata = $this->_customdata;
        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'course_type', '',
                            get_string('multi_coursetype', 'local_levitate'), 0);
        $radioarray[] = $mform->createElement('radio', 'course_type', '',
                            get_string('single_coursetype', 'local_levitate'), 1);
        $mform->addGroup($radioarray, 'radioar', get_string('coursetype', 'local_levitate'), [''], false);
        $mform->setDefault('course_type', 0);
        $mform->addHelpButton ( 'radioar', 'coursetype', 'local_levitate');
        $radioarray1 = [];
        $radioarray1[] = $mform->createElement('radio', 'courseformat', '',
                                   get_string('single_activity_course', 'local_levitate'), 0);
        $radioarray1[] = $mform->createElement('radio', 'courseformat', '',
                                   get_string('multi_activity_course', 'local_levitate'), 1);
        $mform->addGroup($radioarray1, 'radioar1', get_string('coursecreation', 'local_levitate'), [''], false);
        $mform->setDefault('courseformat', 0);
        $mform->addHelpButton ( 'radioar1', 'coursecreation', 'local_levitate');
        $mform->addElement('text', 'coursefullname', get_string('coursefullname', 'local_levitate'));
        $mform->setType('coursefullname', PARAM_TEXT);
        $mform->addElement('text', 'courseshortname', get_string('courseshortname', 'local_levitate'));
        $mform->setType('courseshortname', PARAM_TEXT);
        $coursecategories[0] = get_string('selectcategory', 'local_levitate');
        $categories = $DB->get_records('course_categories');
        foreach ($categories as $category) {
            $coursecategories[$category->id] = $category->name;
        }
        $mform->addElement('select', 'coursecategory', get_string('coursecategory', 'local_levitate'), $coursecategories);
        $mform->addElement('hidden', 'previous_form_values', json_encode( $passdata ));
        $mform->setType('previous_form_values', PARAM_TEXT);
        $this->add_action_buttons(get_string('cancel', 'local_levitate'), get_string('submit', 'local_levitate'));
    }
}
/**
 * local_levitate_storedfile to store the file
 *
 * @param string $name give file name.
 * @param string $packageid contains the scormid
 * @param string $scorm has scorm content to store
 * @return string the file path.
 */
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
/**
 * local_levitate_curlcall to make the curl calls
 * @param string $fnname function name to make the curl call.
 * @param string $jsondata params for curl call.
 * @return string the contents of the requested call.
 */
function local_levitate_curlcall($fnname = '', $jsondata='') {
    global $CFG;
    $tokensettings = get_config('local_levitate');
    $tokenid = $tokensettings->secret;
    $serverurl = 'https://levitate.human-logic.com/webservice/rest/server.php?wstoken=';
    $url = $serverurl.$tokenid.'&wsfunction='.$fnname.'&moodlewsrestformat=json';
    $curl = new curl();
    $response = $curl->post($url, $jsondata);
    if (isset(json_decode($response)->errorcode)){
         
       redirect(new moodle_url('/admin/settings.php?section=locallevitategettoken'), get_string('invalidtoken', 'local_levitate'));
        die();
    }
    return $response;
}
/**
 * local_levitate_get_option_text to create options for select
 * @param string $params list of option values.
 * @param string $idvalue params.
 * @return string the list of options for the requested select.
 */
function local_levitate_get_option_text ($params, $idvalue) {
    $optiontext = '';
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
/**
 * local_levitate_add_scorm_module to add scorm to the course
 * @param string $course course id.
 * @param string $name scorm name.
 * @param string $itemid activity id.
 * @param string $descriptionhtml description for scorm.
 * @param string $assessable params.
 * @param string $section decides the activity type.
 * @param string $scormcontentvalue scorm value.
 * @return string the module info id.
 */
function local_levitate_add_scorm_module($course, $name, $itemid, $descriptionhtml,
$assessable, $section = 0, $scormcontentvalue=null) {
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
