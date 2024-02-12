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
/**
 * Task to store files
 */
function storedfile($name, $packageid, $scorm) {
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
