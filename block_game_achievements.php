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
					$user_groups = groups_get_all_groups($this->page->course->id, $USER->id);
					$achievement_group_names_list = array();
					foreach($user_groups as $user_group)
					{
						$group_unlocked_achievement = $DB->record_exists('achievements_groups_log', array('groupid' => $user_group->id, 'achievementid' => $achievement->id));
						if($group_unlocked_achievement)
						{
							$achievement_group_names_list[] = $user_group->name;
						}
					}
					
					if(!empty($achievement_group_names_list)) // Se algum grupo atingiu a conquista
					{
						$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
						$group_unlocked_achievements_text_list[] = '<li>' . $description . ' ' . $achievement->times . ' ' . get_string('block_times', 'block_game_achievements') . ' (' . implode(', ', $achievement_group_names_list) . ')' . '</li>';
					}
					else if(!isset($group_achievements_text_list[$achievement->event])) // Senão
					{
						$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
						$group_achievements_text_list[$achievement->event] = '<li>' . $description . ' ' . $achievement->times . ' ' . get_string('block_times', 'block_game_achievements') . '</li>';
					}
				}
				else
				{
					$unlocked_achievement = $DB->record_exists('achievements_log', array('userid' => $USER->id, 'achievementid' => $achievement->id));
					
					if($unlocked_achievement)
					{
						$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
						$unlocked_achievements_text_list[] = '<li>' . $description . ' ' . $achievement->times . ' ' . get_string('block_times', 'block_game_achievements') . '</li>';
					}
					else if(!isset($achievements_text_list[$achievement->event]))
					{
						if(!(satisfies_conditions($achievement->conditions, $this->page->course->id, $USER->id) && satisfies_block_conditions($achievement, $USER->id)))
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
						
						$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
						$achievements_text_list[$achievement->event] = '<li>' . $description . ' ' . $times . '/' . $achievement->times . ' ' . get_string('block_times', 'block_game_achievements') . '</li>';
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
}

?>