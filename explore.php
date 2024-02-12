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
global $CFG, $DB;

$tokensettings = $DB->get_record('config_plugins', ['plugin' => 'local_levitate', 'name' => 'secret'], 'value');
$tokenid = $tokensettings->value;
if (empty($tokenid)) {
    redirect(new moodle_url('/admin/settings.php?section=local_levitate'));
    die();
}

$PAGE->set_context(context_system::instance());

require_login();

$PAGE->set_title(get_string('explorenow', 'local_levitate'));
$PAGE->set_heading(get_string('explorenow', 'local_levitate'));
$PAGE->requires->jquery_plugin('ui');
echo "<div id='topofthePage'></div>";
$PAGE->set_pagelayout('base');

echo $OUTPUT->header();
$curl = curl_init();

curl_setopt_array($curl, [
CURLOPT_URL => 'https://levitate.human-logic.com/webservice/rest/server.php?wstoken='
                    .$tokenid.'&wsfunction=mod_levitateserver_get_tags_categories&moodlewsrestformat=json',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_SSL_VERIFYPEER => false,
CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($curl);
curl_close($curl);
$json = json_decode($response);
$jsondata = json_decode(json_encode($json), true);
foreach ($jsondata as $key => $value) {
    if ($key == "time_params") {
        $timeparams = json_decode(json_decode($value));
        $minval = $timeparams->min_time;
        $maxval = $timeparams->max_time;
    }
}
/**
 * creating Options for the select
 */
function get_option_text ($params, $idvalue) {
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
$jsdata = ["tokenid" => $tokenid, "minval" => $minval, "maxval" => $maxval];
$PAGE->requires->js(new \moodle_url($CFG->wwwroot.'/local/levitate/js/explorescript.js'));
$PAGE->requires->js_init_call('createinti', [$jsdata]);
?>
<html>

<head>
</head>

<body>
    <div class="course_explore">
        <div class="hl-filters-wrapper">
            <div class="hl-base">
             <div id="id_clearfilter" class="clearfilter"> <?php echo get_string('clear_filters', 'local_levitate'); ?> </div>
                <div class="course-search">
                    <input type="text" class="searchTerm" placeholder="<?php echo get_string('findcourse', 'local_levitate'); ?>" />
                    <button type="submit" class="searchButton">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
                <div class="slider-wrapper">
                    <p class="label"><?php echo get_string('duration', 'local_levitate'); ?></p>
                    <div slider id="slider-distance">
                        <div>
                            <div inverse-left style="width:0%;"></div>
                            <div inverse-right style="width:0%;"></div>
                            <div range style="left:0%;right:0%;"></div>
                            <span thumb minthumb style="left:0%;"></span>
                            <span thumb maxthumb style="left:100%;"></span>
                            <div sign style="left:30%;">
                                <span id="value">30</span>
                            </div>
                            <div sign style="left:60%;">
                                <span id="value">60</span>
                            </div>
                        </div>
                        
                        <input id="minDval_input"  data-filtername='time_params' name='filter_checkbox' type="range" tabindex="0" value="<?php echo $minval ?>"
                            max="<?php echo $maxval ?>" min="<?php echo $minval ?>" step="5" oninput="
                    this.value=Math.min(this.value,this.parentNode.childNodes[5].value-1);
                    var value=(100/(parseInt(this.max)-parseInt(this.min)))*parseInt(this.value)
                                        -(100/(parseInt(this.max)-parseInt(this.min)))*parseInt(this.min);
                    var children = this.parentNode.childNodes[1].childNodes;
                    console.log(this.parentNode.childNodes[1]);
                    children[1].style.width=value+'%';
                    children[5].style.left=value+'%';
                    children[7].style.left=value+'%';children[11].style.left=value+'%';
                    children[11].childNodes[1].innerHTML=this.value;
                    changeminDval(this.value)
                    " />
                        <input id="maxDval_input" data-filtername='time_params' name='filter_checkbox' type="range" tabindex="0" value="<?php echo $maxval ?>"
                            max="<?php echo $maxval ?>" min="<?php echo $minval ?>" step="5" oninput="
                    this.value=Math.max(this.value,this.parentNode.childNodes[3].value-(-1));
                    var value=(100/(parseInt(this.max)-parseInt(this.min)))*parseInt(this.value)
                                        -(100/(parseInt(this.max)-parseInt(this.min)))*parseInt(this.min);
                    var children = this.parentNode.childNodes[1].childNodes;
                    children[3].style.width=(100-value)+'%';
                    children[5].style.right=(100-value)+'%';
                    children[9].style.left=value+'%';children[13].style.left=value+'%';
                    children[13].childNodes[1].innerHTML=this.value;
                    changemaxDval(this.value)
                    " />
                        <div class="box-minmax">
                            <span>
                                <select name="minDval" id="minDval">
                                    <!-- <option value="0">Min</option> -->
                                </select>
                            </span>
                            <span>to</span>
                            <span>
                                <select name="maxDval" id="maxDval"></select>
                            </span>
                        </div>
                        <div class="error">
                            <p class="errortext"></p>
                        </div>
                    </div>
                </div>

                <div class="filters-summary">
                <p id="total_course_value" class="hl-filter" hidden><?php echo $jsondata["All_courses_count"]; ?></p>
                    <ul class="hl-filters">
                        <?php
                        foreach ($jsondata as $key => $value) {
                            if ($key !== "All_courses_count" && $key !== "subscribed_courses_count" &&
                                    $key !== "custom_courses_count"
                            && $key !== "time_params") {
                                $name = '';
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
                                echo '<li>
                                <label id="'.$key.'" class="hl-filter ">
                                    <input class="'.$key.'" name="hlfilters" type="radio" value="'.$key.'">
                                    <h4 class="hl-title">'.$name.'</h4>
                                    <span class="chevron-down fa fa-chevron-down"></span>
                                </label>';
                            }
                            if (is_array(json_decode(json_decode($value)))) {
                                if (count(json_decode(json_decode($value))) > 0) {
                                    echo ' <div class="hl-FiltersOptions '.$key.' ">
                                    <ul class="hl-values hl-hidden">';
                                    $tagsoptions = get_option_text(json_decode(json_decode($value)), $key);
                                    echo  $tagsoptions;
                                    echo "</ul>";
                                    echo "</div>";
                                }
                            } else {
                                if ($key !== "All_courses_count" && $key !== "subscribed_courses_count" &&
                                    $key !== "custom_courses_count" && $key !== "time_params") {
                                    $array = json_decode(json_encode(json_decode(json_decode($value))), true);
                                    if (count($array) > 0) {
                                        echo ' <div class="hl-FiltersOptions '.$key.' ">
                                                <ul class="hl-values hl-hidden">';
                                        $tagsoptions = get_option_text($array, $key);
                                        echo  $tagsoptions;
                                        echo "</ul>";
                                        echo "</div>";
                                    }
                                }
                            }
                            echo '</li>';
                        }
                        echo "</ul>";
                ?>
                </div>
            </div>
        </div>
        <div class="courselist_wrapper">
            <div class="filter-summary-selectedFilterContainer filter-summary-reduceTopPadding">
                <ul class="filter-summary-filterList"></ul>
                <div>
                    <form autocomplete="off" method="post" id="course_selection" action="./selected_courses.php">
                        <!-- <form id="course_selection"> -->
                        <div class="text-container">
                            <div class="course_count_div">
                                <p class='filterd_courses'></p>
                                <p>out of</p>
                                <p class='total_courses'></p>
                            </div>
                            <div class="course_submit">
                        <input type="submit" value="<?php echo get_string('create_courses', 'local_levitate');?>" disabled />
                            </div>
                        </div>
                        <div class='explorecourses'>
                        </div>
                        <div class="text-container">
                        <input type="submit" value="<?php echo get_string('create_courses', 'local_levitate');?>" disabled />
                        </div>
                        <div class='explore-details-wrapper clearfix' id='explore-details-wrapper-actual'
                            style='display: none;'>
                            <div class='explore-details-pointer' style='left: 82.0005px;'>
                                <div class='explore-empty'></div>
                            </div>
                            <div class='explore-details'>
                                <div class='explore-details-content'>
                                    <div class='explore-details-top clearfix'>
                                        <a role='button' onclick='closewrapper()'
                                            class='explore-details-close pull-right'>×</a>
                                        <h4 class='pull-left coursename'></h4>
                                    </div>
                                    <div class='explore-details-header clearfix'>
                                        <div class='pull-left'>
                                            <div class='explore-header-details-cell'>
                                                <div class='explore-details-delivery'>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='row'>
                                        <div class='explore-details-main col-md-8'>
                                            <div class='explore-details-description'>
                                                <h4><?php echo get_string('about_course', 'local_levitate'); ?></h4>
                                                <div></div>
                                            </div>
                                            <div class='explore-learning-objectives'>
                                                <h4><?php echo get_string('learning_objectives', 'local_levitate'); ?></h4>
                                                <div></div>
                                            </div>
                                        </div>
                                        <div class='col-md-4'>
                                            <div class='explore-details-img'>
                                                <div class='explore-thumbnail-img'></div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
</body>

</html>
<?php
echo $OUTPUT->footer();
