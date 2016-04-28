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
 * Add achievement page.
 *
 * @package    block_game_achievements
 * @copyright  20016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $OUTPUT, $PAGE, $USER;
 
require_once('../../config.php');
require_once('block_game_achievements_add_form.php');
 
global $DB;
 
// Required variables
$courseid = required_param('courseid', PARAM_INT);
$blockinstanceid = required_param('blockinstanceid', PARAM_INT);
 
// Optional variables
$id = optional_param('id', 0, PARAM_INT);
 
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_game_achievements', $courseid);
}
 
require_login($course);
 
$PAGE->set_url('/blocks/game_achievements/achievementadd.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('achievementadd_header', 'block_game_achievements')); 
$PAGE->set_title(get_string('achievementadd_header', 'block_game_achievements'));

$settingsnode = $PAGE->settingsnav->add(get_string('configpage_nav', 'block_game_achievements'));
$addurl = new moodle_url('/blocks/game_achievements/achievementadd.php', array('id' => $id, 'courseid' => $courseid, 'blockinstanceid' => $blockinstanceid));
$addnode = $settingsnode->add(get_string('achievementadd_header', 'block_game_achievements'), $addurl);
$addnode->make_active();

$addform = new block_game_achievements_add_form();
if($addform->is_cancelled())
{
    $url = new moodle_url('/course/view.php', array('id' => $courseid, 'sesskey' => sesskey(), 'bui_editid' => $blockinstanceid));
    redirect($url);
}
else if($data = $addform->get_data())
{
	$record = new stdClass();
	$record->event = $data->event;
	$record->times = $data->times;
	$record->description = empty($data->description) ? null : $data->description;
	$record->blockinstanceid = $blockinstanceid;
	$record->deleted = 0;
	$achievementid = $DB->insert_record('achievements', $record);
	
	$record = new stdClass();
	$record->achievementid = $achievementid;
	$record->processorid = $USER->id;
	$DB->insert_record('achievements_processor', $record);
	
    $url = new moodle_url('/course/view.php', array('id' => $courseid, 'sesskey' => sesskey(), 'bui_editid' => $blockinstanceid));
    redirect($url);
}
else
{
	$toform['blockinstanceid'] = $blockinstanceid;
	$toform['courseid'] = $courseid;
	$addform->set_data($toform);
	$site = get_site();
	echo $OUTPUT->header();
	$addform->display();
	echo $OUTPUT->footer();
}

?>