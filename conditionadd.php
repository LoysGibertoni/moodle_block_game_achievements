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
 * Add achievement condition page.
 *
 * @package    block_game_achievements
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $OUTPUT, $PAGE, $USER;
 
require_once('../../config.php');
require_once('block_game_achievements_conditionadd_form.php');
 
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
 
$PAGE->set_url('/blocks/game_achievements/conditionadd.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('conditionadd_header', 'block_game_achievements'));
$PAGE->set_title(get_string('conditionadd_header', 'block_game_achievements')); 

$settingsnode = $PAGE->settingsnav->add(get_string('configpage_nav', 'block_game_achievements'));
$editurl = new moodle_url('/blocks/game_achievements/conditionadd.php', array('id' => $id, 'courseid' => $courseid, 'achievementid' => $achievementid));
$editnode = $settingsnode->add(get_string('conditionadd_header', 'block_game_achievements'), $editurl);
$editnode->make_active();

$addform = new block_game_achievements_conditionadd_form($achievementid);
if($addform->is_cancelled())
{
    $url = new moodle_url('/blocks/game_achievements/conditionmanage.php', array('courseid' => $courseid, 'achievementid' => $achievementid));
    redirect($url);
}
else if($data = $addform->get_data())
{
	$record = new stdClass();
	$record->achievementid = $achievementid;
	$record->type = $data->condition_type;
	if($data->condition_type == 0) // Se for restrição por pontos
	{
		$block_or_pointsystem_info = explode("::", $data->points_condition_blockorpointsystemid);
		$type = $block_or_pointsystem_info[0];
		$id = $block_or_pointsystem_info[1];
		
		if($type == 'block')
		{
			$record->prblockid = $id;
		}
		else
		{
			$record->prpointsystemid = $id;
		}
		$record->prpoints = $data->points_condition_points;
		$record->prgrupal = empty($data->points_condition_grupal) ? 0 : $data->points_condition_grupal;
	}
	else if($data->condition_type == 1) // Se for restrição por desbloqueio de conteúdo
	{
		$record->urmust = $data->unlock_condition_must;
		$record->urunlocksystemid = $data->unlock_condition_unlocksystemid;
	}
	else
	{
		$record->arachievementid = $data->achievements_condition_achievementid;
	}

	$DB->insert_record('achievements_condition', $record);
	
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