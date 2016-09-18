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
 * Add achievement advanced condition page.
 *
 * @package    block_game_achievements
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $OUTPUT, $PAGE, $USER;
 
require_once('../../config.php');
require_once('block_game_achievements_advancedconditionadd_form.php');
 
global $DB;
 
// Required variables
$courseid = required_param('courseid', PARAM_INT);
$achievementid = required_param('achievementid', PARAM_INT);
 
// Optional variables
$id = optional_param('id', 0, PARAM_INT);
 
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_game_achievements', $courseid);
}
 
require_login($course);
 
$PAGE->set_url('/blocks/game_achievements/advancedconditionadd.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('advancedconditionaddheading', 'block_game_achievements')); 
$PAGE->set_title(get_string('advancedconditionaddheading', 'block_game_achievements'));

$settingsnode = $PAGE->settingsnav->add(get_string('configpage_nav', 'block_game_achievements'));
$editurl = new moodle_url('/blocks/game_achievements/advancedconditionadd.php', array('id' => $id, 'courseid' => $courseid, 'achievementid' => $achievementid));
$editnode = $settingsnode->add(get_string('advancedconditionaddheading', 'block_game_achievements'), $editurl);
$editnode->make_active();

$addform = new block_game_achievements_advancedconditionadd_form();
if($addform->is_cancelled())
{
    $url = new moodle_url('/blocks/game_achievements/conditionmanage.php', array('courseid' => $courseid, 'achievementid' => $achievementid));
    redirect($url);
}
else if($data = $addform->get_data())
{
	$record = new stdClass();
	$record->achievementid = $achievementid;
	$record->whereclause = $data->whereclause;
	$record->trueif = $data->trueif;
	$record->count = empty($data->count) ? null : $data->count;
	$DB->insert_record('achievements_advcondition', $record);
	
    $url = new moodle_url('/blocks/game_achievements/conditionmanage.php', array('courseid' => $courseid, 'achievementid' => $achievementid));
    redirect($url);
}
else
{
	$toform['achievementid'] = $achievementid;
	$toform['courseid'] = $courseid;
	$addform->set_data($toform);
	$site = get_site();
	echo $OUTPUT->header();
	$addform->display();
	echo $OUTPUT->footer();
}

?>