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
require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot . '/local/levitate/lib.php');
global $CFG, $DB;
$errorparam = optional_param('errorcode', null, PARAM_TEXT);
if ($errorparam == 'invalidtoken') {
    redirect(new moodle_url('/admin/settings.php?section=locallevitategettoken'),
    \core\notification::info(get_string('invalidtoken', 'local_levitate')));
}

require_login();

if (!has_capability('local/levitate:view_levitate_catalog', context_system::instance())) {
         \core\notification::add(
               get_string('catalog_capability', 'local_levitate'),
                \core\notification::ERROR
            );
    redirect(new moodle_url('/my/') );
}

$PAGE->set_context(context_system::instance());



$tokensettings = get_config('local_levitate');
$tokenid = $tokensettings->secret;
if (empty($tokenid)) {
    redirect(new moodle_url('/admin/settings.php?section=locallevitategettoken'));
}



$PAGE->set_title(get_string('explorenow', 'local_levitate'));
$PAGE->set_heading(get_string('explorenow', 'local_levitate'));
$PAGE->requires->jquery_plugin('ui');
echo "<div id='topofthePage'></div>";
$PAGE->set_pagelayout('base');
$PAGE->set_url('/local/levitate/explore.php');
$response = local_levitate_curlcall('mod_levitateserver_get_tags_categories');
echo $OUTPUT->header();




$json = json_decode($response);
$jsondata = json_decode(json_encode($json), true);
foreach ($jsondata as $key => $value) {
    if ($key == "time_params") {
        $timeparams = json_decode(json_decode($value));
        $minval = $timeparams->min_time;
        $maxval = $timeparams->max_time;
    }
}

$jsdata = ["tokenid" => $tokenid, "minval" => $minval, "maxval" => $maxval];
$PAGE->requires->js(new \moodle_url($CFG->wwwroot.'/local/levitate/js/explorescript.js'));
$PAGE->requires->js_init_call('createinti', [$jsdata]);
$data = [];
foreach ($jsondata as $key => $value) {
    if ($key !== "All_courses_count" && $key !== "subscribed_courses_count" &&
        $key !== "custom_courses_count" && $key !== "time_params") {
        $name = '';
        $tagsoptions = '';
        switch ($key) {
            case "language_params":
                $name = get_string('language', 'local_levitate');
                break;
            case "category_params":
                $name = get_string('program', 'local_levitate');
                break;
            case "tags_params":
                $name = get_string('keywords', 'local_levitate');
                break;
            default:
                $name = $key;
        }
        if (is_array(json_decode(json_decode($value)))) {
            $array = json_decode(json_decode($value));
        } else {
            $array = json_decode(json_encode(json_decode(json_decode($value))), true);
        }
        if (count($array) > 0) {
            $tagsoptions = local_levitate_get_option_text($array, $key);
        }
        $data[] = [
            'key' => $key,
            'name' => $name,
            'isTagOptions' => (count($array) > 0),
            'tagsoptions' => $tagsoptions,
        ];
    }
}
$exploreparams = (object) [
                'clear_filters' => get_string('clear_filters', 'local_levitate'),
                'findcourse' => get_string('findcourse', 'local_levitate'),
                'duration' => get_string('duration', 'local_levitate'),
                'maxval' => $maxval,
                'minval' => $minval,
                'to' => get_string('to', 'local_levitate'),
                'All_courses_count' => $jsondata["All_courses_count"],
                'jsondata' => $data,
            ];
$params = (object) [
                'create_courses' => get_string('create_courses', 'local_levitate'),
                'levitate_logo' => $OUTPUT->image_url('levitate-logo', 'local_levitate'),
                'loading' => get_string('loading', 'local_levitate'),
                'no_course_found' => get_string('no_course_found', 'local_levitate'),
                'about_course' => get_string('about_course', 'local_levitate'),
                'learning_objectives' => get_string('learning_objectives', 'local_levitate'),
            ];

echo $OUTPUT->render_from_template('local_levitate/explore', $exploreparams);

echo $OUTPUT->render_from_template('local_levitate/explore_footer', $params);

echo $OUTPUT->footer();
