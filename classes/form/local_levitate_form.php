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

require_once($CFG->libdir . '/formslib.php');
/**
 * Allows to fill form for course creation
 *
 * @package    local_levitate
 * @copyright  2023, Human Logic Software LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        $mform->addRule('coursecategory',
        get_string('coursecategory_required', 'local_levitate'), 'nonzero', '', 'client', false, false);
        $mform->addElement('hidden', 'previous_form_values', json_encode( $passdata ));
        $mform->setType('previous_form_values', PARAM_TEXT);
        $this->add_action_buttons(get_string('cancel', 'local_levitate'), get_string('submit', 'local_levitate'));
    }
}
