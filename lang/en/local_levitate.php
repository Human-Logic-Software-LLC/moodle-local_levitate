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

$string['pluginname'] = 'Levitate';
$string['headerconfig'] = 'Configuration';
$string['headerconfig_desc'] = 'Configuration';
$string['secret'] = 'Token';
$string['secret_desc'] = 'Token';
$string['explorenow'] = 'Explore catalog';
$string['addnewcourse'] = 'Create new course';
$string['requiredfields'] = 'There are required fields in this form marked *.';
$string['coursesettings'] = 'Course settings';
$string['coursecreation'] = 'Course format';
$string['multi_activity_course'] = 'Topics format';
$string['single_activity_course'] = 'Single activity format';
$string['coursefullname'] = 'Course full name';
$string['courseshortname'] = 'Course short name';
$string['coursecategory'] = 'Course category';
$string['coursetype'] = 'Course creation';
$string['single_coursetype'] = 'Single course';
$string['multi_coursetype'] = 'Multiple courses';
$string['cancel'] = 'Cancel';
$string['submit'] = 'Create courses';
$string['selectcategory'] = 'Select category';
$string['coursetype_help'] = 'Single course : create single course with the selected content items <br> Multiple courses : create multiple courses for the selected content items';
$string['coursecreation_help'] = 'Single activity format : create course with single topic <br> Topics format : create course with multiple topics';
$string['single_course_creation_success'] = 'Course created successfully';
$string['single_course_creation_fail'] = 'Course creation failed';
$string['multi_course_creation_success'] = 'Courses created successfully';
$string['multi_course_creation_fail'] = 'Courses creation failed';
$string['create_course_task'] = 'Create courses';
$string['heading'] = 'Explore catalog';
$string['title'] = 'Explore catalog';
$string['shortname_exists'] = 'Shortname exists, change it';
$string['author'] = 'Levitate';
$string['task_time'] = 'Courses will be created in next schedule task execution on {$a} ';
$string['execute_now1'] = 'To execute now -';
$string['execute_now2'] = 'Click here and run create courses task';
$string['get_token'] = 'Configuration settings';
$string['clear_filters'] = 'Clear filters';
$string['findcourse'] = 'Find a course...';
$string['duration'] = 'Duration';
$string['language'] = 'Language';
$string['program'] = 'Program';
$string['keywords'] = 'Keywords';
$string['create_courses'] = 'Create courses';
$string['about_course'] = 'About the course';
$string['learning_objectives'] = 'Learning objectives';
$string['total_users'] = 'Active / Total seats';
$string['total_minutes'] = 'Total minutes';
$string['total_courses'] = 'Total courses';
$string['course_statistics'] = 'Course statistics (courses enrolled)';
$string['completion_statistics'] = 'Completion statistics';
$string['total_enrolls'] = 'Total enrollments';
$string['total_completions'] = 'Total completions';
$string['popular_courses'] = 'Popular courses';
$string['my_details'] = 'My details';
$string['contact_person'] = 'Contact person';
$string['contact_details'] = 'Contact details';
$string['access_domain'] = 'Access domain(s)';
$string['subscripton_start'] = 'Subscription start & end';
$string['seat_utilization'] = 'Seat utilization';
$string['seats_bought'] = 'Total seats';
$string['seats_used'] = 'Remaining seats';
$string['explore_courses'] = 'Explore catalog';
$string['analytics'] = 'Analytics';
$string['gettoken'] = 'Get token';
$string['heading_analytics'] = 'Analytics dashboard';
$string['catalog_capability'] = 'You have no capabilities to explore the levitate catalog';
$string['analytics_capability'] = 'You have no capabilities to explore the levitate analytics';
$string['privacy:metadata'] = 'Levitate plugin only displays existing course metadata in levitate server.';
$string['to'] = 'to';
$string['no_course_found'] = 'No courses found for the selected filters';
$string['loading'] = 'loading...';
$string['levitate:view_levitate_analytics'] = 'Capability to view Analytics';
$string['levitate:view_levitate_catalog'] = 'Capability to explore Course Catalog';
