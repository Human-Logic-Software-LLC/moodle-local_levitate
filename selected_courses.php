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

require_once('../../config.php');
require_once('../../course/externallib.php');
require_once("../../lib/formslib.php");
require_once('../../lib/adminlib.php');
require_once('../../mod/scorm/locallib.php');
require_once('../../course/modlib.php');
global $CFG, $USER, $DB;
require_once($CFG->dirroot . '/local/levitate/lib.php');

require_login();

$PAGE->requires->jquery_plugin('ui');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('addnewcourse', 'local_levitate'));
$PAGE->set_heading(get_string('addnewcourse', 'local_levitate'));

$PAGE->set_pagelayout('base');
echo $OUTPUT->header();

echo "<h4>".get_string('coursesettings', 'local_levitate')."</h4>";
if (!has_capability('local/levitate:view_levitate_catalog', context_system::instance())) {
    $url = $CFG->wwwroot.'/my/';
    redirect($url, get_string('catalog_capability', 'local_levitate'));
}
$mform = new local_levitate_form();
$mform->display();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/levitate/explore.php'));
} else if ($formdata = $mform->get_data()) {
    $previousform = json_decode($formdata->previous_form_values);
    $imageurls = json_decode($previousform->image_urls);
    $imgstring = new stdClass;
    foreach ($imageurls as $urlkey => $url) {
        $imgstring->$urlkey = $url;
    }
    $previousform->image_urls = json_encode($imgstring);
    $formvalues = new \stdclass();
    $formvalues->taskexecuted = 0;

    $formvalues->coursedata = $formdata->previous_form_values;
    unset($formdata->previous_form_values);
    $currentform = json_encode($formdata);
    $formvalues->formdata = $currentform;
    $formvalues->userid = $USER->id;
    $formvalues->timecreated = time();
    $query = "SELECT id, shortname FROM {course}";
    $courseshortnames = $DB->get_records_sql($query);
    foreach ($courseshortnames as $courseshort) {
        $shortnames[] = $courseshort->shortname;
    }
    if (in_array($formdata->courseshortname, $shortnames)) {
        $notification = get_string('shortname_exists', 'local_levitate');
        \core\notification::info($notification);
    } else {
        $updated = $DB->insert_record('levitate_task_details', $formvalues);
        $nexttaskruntime = $DB->get_field('task_scheduled', 'nextruntime',
                                  ['classname' => '\local_levitate\task\create_course'], MUST_EXIST);
        $notificationtext = get_string('task_time', 'local_levitate', date('l, d F Y, g:i A', $nexttaskruntime).PHP_EOL);
        $notificationtextnextline = get_string('execute_now1', 'local_levitate')
                                        .' <a href="'.$CFG->wwwroot.'/admin/tool/task/scheduledtasks.php">'
                                        .get_string('execute_now2', 'local_levitate').'</a>';
        \core\notification::info($notificationtext);
        echo "<br>";
        \core\notification::info($notificationtextnextline);
    }
}

echo $OUTPUT->footer();

?>

<script>
    
$(document).ready(function(){
    var course_type_name='<?php echo $formdata->course_type; ?>';
    $('input[type=radio][name=course_type]').change(function() {
        
        if (this.value == 0) {
            $('#fitem_id_coursefullname').css('display','none');
            $('#fitem_id_courseshortname').css('display','none');
            $('#fgroup_id_radioar1').css('display','flex');

        }
        else if (this.value == 1) {
            $('#fitem_id_coursefullname').css('display','flex');
            $('#fitem_id_courseshortname').css('display','flex');
            $('#fgroup_id_radioar1').css('display','none');
        }
    });
    
    if (course_type_name == 0) {
            $('#fitem_id_coursefullname').css('display','none');
            $('#fitem_id_courseshortname').css('display','none');
            $('#fgroup_id_radioar1').css('display','flex');

        }
    else if (course_type_name == 1) {

        $('#fitem_id_coursefullname').css('display','flex');
        $('#fitem_id_courseshortname').css('display','flex');
        $('#fgroup_id_radioar1').css('display','none');
    }
    
});
    
</script>
