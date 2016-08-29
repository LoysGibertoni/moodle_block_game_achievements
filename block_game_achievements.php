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
 * Achievements block definition.
 *
 * @package    block_game_achievements
 * @copyright  20016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/game_achievements/lib.php');

class block_game_achievements extends block_base
{
	public static $resource_events = array('\block_game_points\event\points_earned', '\block_game_achievements\event\achievement_reached', '\block_game_content_unlock\event\content_unlocked');

    public function init()
	{
        $this->title = get_string('title', 'block_game_achievements');
    }

	public function applicable_formats()
	{
        return array(
            'all'    => true
        );
    }
	
	public function instance_allow_multiple()
	{
	  return true;
	}
	
    public function get_content()
	{
		global $DB, $USER;
		$this->content = new stdClass;
		
		if(is_student($USER->id)) // If user is a student
		{
			$this->content->text = '';
			
			$achievements_text_list = array();
			$unlocked_achievements_text_list = array();
			$group_achievements_text_list = array();
			$group_unlocked_achievements_text_list = array();
			
			$events = generate_events_list();
			$achievements = get_achievements($this->instance->id);
			foreach($achievements as $achievement)
			{
				if($achievement->groupmode)
				{
					$groups = null;
					if($achievement->groupvisibility == VISIBLEGROUPS)
					{
						$groups = groups_get_all_groups($this->page->course->id, 0, $achievement->groupingid);
					}
					else
					{
						$groups = groups_get_all_groups($this->page->course->id, $USER->id, $achievement->groupingid);
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
				
					if($user_group_unlocked_achievement) // Se algum grupo do usuário atingiu a conquista
					{
						if(in_array($achievement->event, self::$resource_events))
						{
							$group_unlocked_achievements_text_list[] = '<li>' . (isset($achievement->name) ? $achievement->name . ': ' : '') . $this->get_block_conditions_text($achievement) . ' (' . implode(', ', $achievement_group_names_list) . ')' . '</li>';
						}
						else
						{
							$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
							$group_unlocked_achievements_text_list[] = '<li>' . (isset($achievement->name) ? $achievement->name . ': ' : '') . $description . ' ' . $achievement->times . ' ' . get_string('block_times', 'block_game_achievements') . ' (' . implode(', ', $achievement_group_names_list) . ')' . '</li>';
						}
					}
					else if(!isset($group_achievements_text_list[$achievement->event])) // Senão
					{
						if(in_array($achievement->event, self::$resource_events))
						{
							$group_achievements_text_list[] = '<li>'. (isset($achievement->name) ? $achievement->name . ': ' : '')  . $this->get_block_conditions_text($achievement) . (!empty($achievement_group_names_list) ? ' (' . implode(', ', $achievement_group_names_list) . ')' : '') . '</li>';
						}
						else
						{
							$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
							$group_achievements_text_list[$achievement->event] = '<li>' . (isset($achievement->name) ? $achievement->name . ': ' : '') . $description . ' ' . $achievement->times . ' ' . get_string('block_times', 'block_game_achievements') . (!empty($achievement_group_names_list) ? ' (' . implode(', ', $achievement_group_names_list) . ')' : '') . '</li>';
						}
					}
				}
				else
				{
					$unlocked_achievement = $DB->record_exists('achievements_log', array('userid' => $USER->id, 'achievementid' => $achievement->id));
					
					if($unlocked_achievement)
					{
						if(in_array($achievement->event, self::$resource_events))
						{
							$unlocked_achievements_text_list[] = '<li>' . (isset($achievement->name) ? $achievement->name . ': ' : '') . $this->get_block_conditions_text($achievement) . '</li>';
						}
						else
						{
							$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
							$unlocked_achievements_text_list[] = '<li>' . (isset($achievement->name) ? $achievement->name . ': ' : '') . $description . ' ' . $achievement->times . ' ' . get_string('block_times', 'block_game_achievements') . '</li>';
						}
					}
					else if(!isset($achievements_text_list[$achievement->event]))
					{
						if(!(satisfies_conditions($achievement->conditions, $this->page->course->id, $USER->id) && (in_array($achievement->event, self::$resource_events) || satisfies_block_conditions($achievement, $this->page->course->id, $USER->id))))
						{
							continue;
						}
						
						$sql = 'SELECT count(*)
									FROM {achievements_events_log} a
										INNER JOIN {logstore_standard_log} l ON l.id = a.logid
									WHERE l.userid = :userid
										AND a.achievementid = :achievementid';
						$params['userid'] = $USER->id;
						$params['achievementid'] = $achievement->id;
						
						$times = $DB->count_records_sql($sql, $params);
						
						if(in_array($achievement->event, self::$resource_events))
						{
							$achievements_text_list[] = '<li>' . (isset($achievement->name) ? $achievement->name . ': ' : '') . $this->get_block_conditions_text($achievement) . '</li>';
						}
						else
						{
							$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
							$achievements_text_list[$achievement->event] = '<li>' . (isset($achievement->name) ? $achievement->name . ': ' : '') . $description . ' ' . $times . '/' . $achievement->times . ' ' . get_string('block_times', 'block_game_achievements') . '</li>';
						}
					}
				}
			}
			
			if(!empty($achievements_text_list))
			{
				$this->content->text .= '<p>' . get_string('block_achievements', 'block_game_achievements') . ':<ul>' . implode($achievements_text_list) . '</ul></p>';
			}
			if(!empty($unlocked_achievements_text_list))
			{
				$this->content->text .= '<p>' . get_string('block_unlocked_achievements', 'block_game_achievements') . ':<ul>' . implode($unlocked_achievements_text_list) . '</ul></p>';
			}
			if(!empty($group_achievements_text_list))
			{
				$this->content->text .= '<p>' . get_string('block_group_achievements', 'block_game_achievements') . ':<ul>' . implode($group_achievements_text_list) . '</ul></p>';
			}
			if(!empty($group_unlocked_achievements_text_list))
			{
				$this->content->text .= '<p>' . get_string('block_group_unlocked_achievements', 'block_game_achievements') . ':<ul>' . implode($group_unlocked_achievements_text_list) . '</ul></p>';
			}
			
			$achievement_list_url = new moodle_url('/blocks/game_achievements/achievementlist.php', array('blockinstanceid' => $this->instance->id, 'courseid' => $this->page->course->id));
			$this->content->footer = html_writer::link($achievement_list_url, get_string('block_achievementlist', 'block_game_achievements'));
		}
		else // If user has any other role
		{
			$this->content->text = 'Hello';
			$this->content->footer = '';
		}
		
		return $this->content;
    }

	public function specialization()
	{
		if(isset($this->config))
		{
			if(empty($this->config->title))
			{
				$this->title = get_string('title', 'block_game_achievements');            
			}
			else
			{
				$this->title = $this->config->title;
			}
		}
	}

	public static function get_block_conditions_text($achievement)
	{
		global $DB;

		$conditions_text = array();
		$achievement_block_conditions = $DB->get_records('achievements_condition', array('achievementid' => $achievement->id));

		foreach($achievement_block_conditions as $achievement_block_condition)
		{
			if($achievement_block_condition->type == 0) // By points
			{
				$block_info = null;
				if(isset($achievement_block_condition->prpointsystemid))
				{
					$block_instance_id = $DB->get_field('points_system', 'blockinstanceid', array('id' => $achievement_block_condition->prpointsystemid));
					$block_info = $DB->get_record('block_instances', array('id' => $block_instance_id));

					$points_system_name = $DB->get_field('points_system', 'name', array('id' => $achievement_block_condition->prpointsystemid));
				}
				else
				{
					$block_info = $DB->get_record('block_instances', array('id' => $achievement_block_condition->prblockid));
				}
				$instance = block_instance('game_points', $block_info);
				
				$conditions_text[] = get_string('block_conditions_reach', 'block_game_achievements') . ' ' . $achievement_block_condition->prpoints . ' ' . get_string('block_conditions_points', 'block_game_achievements') . ' (' . ($achievement_block_condition->prgrupal ? get_string('block_conditions_grupal', 'block_game_achievements') : get_string('block_conditions_individual', 'block_game_achievements')) . ') ' . get_string('block_conditions_on', 'block_game_achievements') . ' ' . (isset($achievement_block_condition->prblockid) ? get_string('block_conditions_block', 'block_game_achievements') . ' ' . $instance->title  : get_string('block_conditions_pointsystem', 'block_game_achievements') . ' ' . (empty($points_system_name) ? $achievement_block_condition->prpointsystemid : $points_system_name . ' (' . $achievement_block_condition->prpointsystemid . ')') . ' (' . get_string('block_conditions_block', 'block_game_achievements') . ' ' . $instance->title . ')' );
			}
			else if($achievement_block_condition->type == 1) // By content unlock
			{
				$condition_unlock_system= $DB->get_record('content_unlock_system', array('id' => $achievement_block_condition->urunlocksystemid));

				$course = $DB->get_record('course', array('id' => $this->page->course->id));
				$info = get_fast_modinfo($course);
				$cm = $info->get_cm($condition_unlock_system->coursemoduleid);

				$block_info = $DB->get_record('block_instances', array('id' => $condition_unlock_system->blockinstanceid));
				$instance = block_instance('game_content_unlock', $block_info);
				
				
				$conditions_text[] = ($achievement_block_condition->urmust ? get_string('block_conditions_have', 'block_game_achievements') : get_string('block_conditions_havenot', 'block_game_achievements')) . ' ' . ($condition_unlock_system->coursemodulevisibility ? get_string('block_conditions_unlocked', 'block_game_achievements') : get_string('block_conditions_locked', 'block_game_achievements')) . ' ' . get_string('block_conditions_resource', 'block_game_achievements') . ' ' . $cm->name . ' (' . get_string('block_conditions_block', 'block_game_achievements') . ' ' . $instance->title . ')';
			}
			else // By achievement reached
			{
				$condition_achievement = $DB->get_record('achievements', array('id' => $achievement_block_condition->arachievementid));

				$block_info = $DB->get_record('block_instances', array('id' => $condition_achievement->blockinstanceid));
				$instance = block_instance('game_achievements', $block_info);
				
				$conditions_text[] = get_string('block_conditions_reach', 'block_game_achievements') . ' ' . get_string('block_conditions_achievement', 'block_game_achievements') . ' ' . (isset($condition_achievement->name) ? $condition_achievement->name . ' (' . $condition_achievement->id . ')' : $condition_achievement->id)   . ' (' . get_string('block_conditions_block', 'block_game_achievements') . ' ' . $instance->title . ')';
			}

		}

		return implode(' ' . ($achievement->connective == AND_CONNECTIVE ? get_string('block_conditions_and', 'block_game_achievements') : get_string('block_conditions_or', 'block_game_achievements')) . ' ', $conditions_text);
	}
}

?>