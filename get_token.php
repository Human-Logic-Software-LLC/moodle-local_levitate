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
global $CFG, $DB, $PAGE;
require_login();
$tokensettings = $DB->get_record(
    "config_plugins",
    ["plugin" => "local_levitate", "name" => "secret"],
    "value"
);
$tokenid = $tokensettings->value;
$endpoint =
    "https://levitate.human-logic.com/webservice/rest/server.php?wstoken=";
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL =>
        $endpoint .
        $tokenid .
        "&wsfunction=mod_levitateserver_get_analytics&moodlewsrestformat=json",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
]);

$response = curl_exec($curl);

curl_close($curl);
echo $response;
