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

global $CFG, $PAGE;
$PAGE->requires->js(new \moodle_url($CFG->wwwroot.'/local/levitate/js/levitate.js'), true);
$PAGE->requires->js_init_call('update_token', []);

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('cat_levitate', get_string('pluginname', 'local_levitate')));
    $ADMIN->add('cat_levitate', new admin_externalpage('explorecourses', get_string('explore_courses', 'local_levitate'),
                                                  "$CFG->wwwroot/local/levitate/explore.php"));
    $ADMIN->add('cat_levitate', new admin_externalpage('analytics', get_string('analytics', 'local_levitate'),
                                                  "$CFG->wwwroot/local/levitate/analytics.php"));
    $settings = new admin_settingpage('local_levitate', get_string('gettoken', 'local_levitate'));
    $ADMIN->add('cat_levitate', $settings);

    $ADMIN->add('root', new admin_category('levitateroot', get_string('pluginname', 'local_levitate')));

    $ADMIN->add('courses', new admin_externalpage('explorecourses', get_string('pluginname', 'local_levitate'),
                                                  "$CFG->wwwroot/local/levitate/explore.php"));
    if ($ADMIN->fulltree) {
        $string = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $encoded = urlencode($string);

        $settings->add(new admin_setting_configpasswordunmask('local_levitate/secret', get_string('secret', 'local_levitate'),
                          "<a href='https://levitate.human-logic.com/create_token.php?$encoded'>"
                          .get_string('secret', 'local_levitate')."</a>", ''));
    }
}
