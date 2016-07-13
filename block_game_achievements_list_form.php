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
 * Achievements list form definition.
 *
 * @package    block_game_achievements
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");
require_once('lib.php');

class block_game_achievements_list_form extends moodleform {
 
    function definition()
	{ 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('achievementlist_header', 'block_game_achievements'));
		
		// Hidden elements
		$mform->addElement('hidden', 'blockinstanceid');
		$mform->setType('blockinstanceid', PARAM_INT);
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		
		//$this->add_action_buttons(true, get_string('achievementadd_submit', 'block_game_achievements'));
    }
	
	public function definition_after_data()
	{
		global $DB, $USER;
        parent::definition_after_data();
				
        $mform =& $this->_form;
		
        $blockinstanceid_element = $mform->getElement('blockinstanceid');
        $blockinstanceid = $blockinstanceid_element->getValue();
		
		$courseid_element = $mform->getElement('courseid');
        $courseid = $courseid_element->getValue();
		
		$content = '';
		$achievements_text_list = array();
		$group_achievements_text_list = array();
		
		$events = generate_events_list();
		$achievements = get_achievements($blockinstanceid);
		foreach($achievements as $achievement)
		{
			if($achievement->groupmode)
			{
				$groups = null;
				if($achievement->groupvisibility == VISIBLEGROUPS)
				{
					$groups = groups_get_all_groups($courseid, 0, $achievement->groupingid);
				}
				else
				{
					$groups = groups_get_all_groups($courseid, $USER->id, $achievement->groupingid);
				}
				
				$user_group_unlocked_achievement = false;
				$achievement_group_names_list = array();
				foreach($groups as $group)
				{
					$group_unlocked_achievement = $DB->record_exists('achievements_groups_log', array('groupid' => $group->id, 'achievementid' => $achievement->id));
					if($group_unlocked_achievement)
					{
						if(groups_is_member($group->id, $USER->id))
						{
							$user_group_unlocked_achievement = true;
						}
						$achievement_group_names_list[] = $group->name;
					}
				}
				
				if(in_array($achievement->event, block_game_achievements::$resource_events))
				{
					$group_achievements_text_list[] = '<li>' . block_game_achievements::get_block_conditions_text($achievement) . (!empty($achievement_group_names_list) ? ' (' . implode(', ', $achievement_group_names_list) . ')' : '') . ($user_group_unlocked_achievement ? ' (' . get_string('achievementlist_unlocked', 'block_game_achievements') . ')' : '') . '</li>';
				}
				else
				{
					$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
					$group_achievements_text_list[] = '<li>' . $description . ' ' . $achievement->times . ' ' . get_string('block_times', 'block_game_achievements') . (!empty($achievement_group_names_list) ? ' (' . implode(', ', $achievement_group_names_list) . ')' : '') . ($user_group_unlocked_achievement ? ' (' . get_string('achievementlist_unlocked', 'block_game_achievements') . ')' : '') . '</li>';
				}
			}
			else
			{
				$unlocked_achievement = $DB->record_exists('achievements_log', array('userid' => $USER->id, 'achievementid' => $achievement->id));
				if($unlocked_achievement)
				{
					if(in_array($achievement->event, block_game_achievements::$resource_events))
					{
						$achievements_text_list[] = '<li>' . block_game_achievements::get_block_conditions_text($achievement) . ' (' . get_string('achievementlist_unlocked', 'block_game_achievements') . ')' . '</li>';
					}
					else
					{
						$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
						$achievements_text_list[] = '<li>' . $description . ' ' . $achievement->times . ' ' . get_string('achievementlist_times', 'block_game_achievements') . ' (' . get_string('achievementlist_unlocked', 'block_game_achievements') . ')' . '</li>';
					}
				}
				else
				{
					$sql = 'SELECT count(*)
								FROM {achievements_events_log} a
									INNER JOIN {logstore_standard_log} l ON l.id = a.logid
								WHERE l.userid = :userid
									AND a.achievementid = :achievementid';
					$params['userid'] = $USER->id;
					$params['achievementid'] = $achievement->id;
					
					$times = $DB->count_records_sql($sql, $params);
					
					if(in_array($achievement->event, block_game_achievements::$resource_events))
					{
						$achievements_text_list[] = '<li>' . block_game_achievements::get_block_conditions_text($achievement) . '</li>';
					}
					else
					{
						$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
						$achievements_text_list[] = '<li>' . $description . ' ' . $times . '/' . $achievement->times . ' ' . get_string('achievementlist_times', 'block_game_achievements') . '</li>';
					}
				}
			}
		}
		
		if(empty($achievements_text_list) && empty($group_achievements_text_list))
		{
			$content = '<p>' . get_string('achievementlist_noachievements', 'block_game_achievements') . '</p>';
		}
		if(!empty($achievements_text_list))
		{
			$content = '<p>' . get_string('achievementlist_achievements', 'block_game_achievements') . ':<ul>' . implode($achievements_text_list) . '</ul></p>';
		}
		if(!empty($group_achievements_text_list))
		{
			$content .= '<p>' . get_string('achievementlist_group_achievements', 'block_game_achievements') . ':<ul>' . implode($group_achievements_text_list) . '</ul></p>';
		}
		
		$mform->addElement('html', $content);
		
    }

}

?>