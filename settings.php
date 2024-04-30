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

global $CFG, $PAGE, $OUTPUT;
$PAGE->requires->js(new \moodle_url($CFG->wwwroot.'/local/levitate/js/levitate.js'), true);
$PAGE->requires->js_init_call('update_token', []);

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_levitate_settings', new lang_string('pluginname', 'local_levitate')));
    $ADMIN->add('local_levitate_settings', new admin_externalpage('locallevitateexplorecourses',
     new lang_string('explore_courses', 'local_levitate') , "$CFG->wwwroot/local/levitate/explore.php"));

    $ADMIN->add('local_levitate_settings', new admin_externalpage('locallevitateanalytics',
    new lang_string('analytics', 'local_levitate') , "$CFG->wwwroot/local/levitate/analytics.php"));

    $settings = new admin_settingpage('locallevitategettoken', new lang_string('gettoken', 'local_levitate'));
    $ADMIN->add('local_levitate_settings', $settings);
    if ($ADMIN->fulltree) {
        $string = new \moodle_url($CFG->wwwroot.'/admin/settings.php?section=locallevitategettoken');
        $encoded = urlencode($string);

        $settings->add(new admin_setting_configpasswordunmask('local_levitate/secret', new lang_string('secret', 'local_levitate'),
                          new lang_string('secret_help', 'local_levitate').
                          "<br><a href='https://levitate.human-logic.com/create_token.php?$encoded'>"
                          .new lang_string('secret_url', 'local_levitate')."</a>", ''));
        $settings->add(new admin_setting_configtext('local_levitate/server_url', get_string('server_url',
            'local_levitate'), null, 'https://server.levitate.coach'));
    }
}
