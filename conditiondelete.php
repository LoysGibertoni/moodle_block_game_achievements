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
 * Delete points system condition page.
 *
 * @package    block_game_achievements
 * @copyright  20016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $OUTPUT, $PAGE, $USER;
 
require_once('../../config.php');
require_once('block_game_achievements_conditiondelete_form.php');
 
// Required variables
$courseid = required_param('courseid', PARAM_INT);
$conditionid = required_param('conditionid', PARAM_INT);
 
// Optional variables
$id = optional_param('id', 0, PARAM_INT);
 
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_game_achievements', $courseid);
}
 
require_login($course);
 
$PAGE->set_url('/blocks/game_achievements/conditiondelete.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('conditiondelete_header', 'block_game_achievements'));
$PAGE->set_title(get_string('conditiondelete_header', 'block_game_achievements')); 

$settingsnode = $PAGE->settingsnav->add(get_string('configpage_nav', 'block_game_achievements'));
$editurl = new moodle_url('/blocks/game_achievements/conditiondelete.php', array('id' => $id, 'courseid' => $courseid, 'conditionid' => $conditionid));
$editnode = $settingsnode->add(get_string('conditiondelete_header', 'block_game_achievements'), $editurl);
$editnode->make_active();

$deleteform = new block_game_achievements_conditiondelete_form();
if($deleteform->is_cancelled())
{
	$achievementid = $DB->get_field('achievements_condition', 'achievementid', array('id' => $conditionid));
	
    $url = new moodle_url('/blocks/game_achievements/conditionmanage.php', array('courseid' => $courseid, 'achievementid' => $achievementid));
    redirect($url);
}
else if($data = $deleteform->get_data())
{
	$achievementid = $DB->get_field('achievements_condition', 'achievementid', array('id' => $conditionid));
	
	$DB->delete_records('achievements_condition', array('id' => $conditionid));
	
    $url = new moodle_url('/blocks/game_achievements/conditionmanage.php', array('courseid' => $courseid, 'achievementid' => $achievementid));
    redirect($url);
}
else
{
	$toform['conditionid'] = $conditionid;
	$toform['courseid'] = $courseid;
	$deleteform->set_data($toform);
	$site = get_site();
	echo $OUTPUT->header();
	$deleteform->display();
	echo $OUTPUT->footer();
}

?>