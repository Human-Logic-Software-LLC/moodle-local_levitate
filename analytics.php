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
global $CFG, $DB, $PAGE, $OUTPUT;
require_once($CFG->dirroot . '/local/levitate/lib.php');
$PAGE->set_context(context_system::instance());
require_login();

if (!has_capability('local/levitate:view_levitate_analytics', context_system::instance())) {
    $url = $CFG->wwwroot.'/my/';
    redirect($url, get_string('analytics_capability', 'local_levitate'));
}

$PAGE->requires->jquery_plugin('ui');

$PAGE->requires->js(new \moodle_url('https://levitate.human-logic.com/blocks/levitate_report/javascript/datatables.min.js'), true);
$PAGE->requires->css(new \moodle_url('https://levitate.human-logic.com/blocks/levitate_report/javascript/datatables.min.css'));
$PAGE->requires->js(new \moodle_url('https://d3js.org/d3.v4.js'), true);
$PAGE->requires->css(new \moodle_url($CFG->wwwroot.'/local/levitate/css/styles.css'));

$PAGE->requires->js(new \moodle_url($CFG->wwwroot.'/local/levitate/js/report.js'), true);
$PAGE->set_title(get_string('heading_analytics', 'local_levitate'));
$PAGE->set_heading(get_string('heading_analytics', 'local_levitate'));
echo "<div id='topofthePage'></div>";
$PAGE->set_pagelayout('base');
$PAGE->set_url('/local/levitate/analytics.php');
$response = local_levitate_curlcall('mod_levitateserver_get_analytics');
echo $OUTPUT->header();

$tokensettings = get_config('local_levitate');
$tokenid = $tokensettings->secret;
if (empty($tokenid)) {
    redirect(new moodle_url('/admin/settings.php?section=locallevitategettoken'));
    die();
}


$json = json_decode($response);
$jsondata = json_decode(json_encode($json), true);

$totalusers = $jsondata['participant_count'];
$totalseats = $jsondata['total_seats'];
$totalcourses = $jsondata['total_courses'];
$totaltimespent = floor($jsondata['total_timespent'] / 60000);
$levitatedata = json_decode($jsondata['userinfo']);

$totalpercent = (int)$levitatedata->seats;
$utilized = (int)$jsondata["participant_count"];
$utilizationpercentage = ($utilized / $totalpercent) * 100;
$utilizationpercentage = number_format($utilizationpercentage, 1);
$graphvalue = ($utilizationpercentage / 100) * 630;
$logourl = $levitatedata->CompanyLogoUrl ? $levitatedata->CompanyLogoUrl : $OUTPUT->image_url('company-logo', 'local_levitate');

$datevalues = date("M d, Y", $levitatedata->subscriptionstart).' - '.date("M d, Y", $levitatedata->subscriptionend);
$params = (object) [
                'totalusers' => $totalusers,
                'totaltimespent' => $totaltimespent,
                'totalcourses' => $totalcourses,
                'logourl' => $logourl,
                'contact-person_svg' => $OUTPUT->image_url('contact-person', 'local_levitate'),
                'contact-details_svg' => $OUTPUT->image_url('contact-details', 'local_levitate'),
                'access-domain_svg' => $OUTPUT->image_url('access-domain', 'local_levitate'),
                'calendar_svg' => $OUTPUT->image_url('calendar', 'local_levitate'),
                'contactname' => $levitatedata->contactname,
                'profesion' => $levitatedata->profession,
                'contactemail' => $levitatedata->contactemail,
                'mobile' => $levitatedata->mobile,
                'primarydomain' => $levitatedata->primarydomain,
                'datevalues' => $datevalues,
                'utilizationpercentage' => $utilizationpercentage,
                'graphvalue' => $graphvalue,
                'totalseats' => $levitatedata->seats,
                'remainingseats' => $levitatedata->seats - $totalusers,
            ];

echo $OUTPUT->render_from_template('local_levitate/analytics', $params);
echo $OUTPUT->footer();
