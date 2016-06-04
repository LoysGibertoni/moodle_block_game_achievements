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
 * Achievements block functions definitions.
 *
 * @package    block_game_achievements
 * @copyright  20016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('lib.php');

function generate_events_list($showeventname = false)
{
	global $DB;
	
	$eventsarray = array();

	$eventsarray['\block_game_achievements\event\achievement_reached'] = ($showeventname === true ? (\block_game_achievements\event\achievement_reached::get_name() . " (\block_game_achievements\event\achievement_reached)") : \block_game_achievements\event\achievement_reached::get_name());

	$game_points_installed = $DB->record_exists('block', array('name' => 'game_points'));
	if($game_points_installed)
	{
		$eventsarray['\block_game_points\event\points_earned'] = ($showeventname === true ? (\block_game_points\event\points_earned::get_name() . " (\block_game_points\event\points_earned)") : \block_game_points\event\points_earned::get_name());
	}
	
	$eventslist = report_eventlist_list_generator::get_non_core_event_list();
	foreach($eventslist as $value)
	{
		$description = explode("\\", explode(".", strip_tags($value['fulleventname']))[0]);
		$eventsarray[$value['eventname']] = ($showeventname === true ? ($description[0] . " (" . $value['eventname'] . ")") : $description[0]);
	}
	
	return $eventsarray;
}

function satisfies_conditions($conditions, $courseid, $userid)
{
	global $DB;
	
	if(isset($conditions))
	{
		$tree = new \core_availability\tree(json_decode($conditions));
		$course = $DB->get_record('course', array('id' => $courseid));
		$info = new conditions_info($course);
		$result = $tree->check_available(false, $info, true, $userid);
		return $result->is_available();
	}
	
	return true;
}

function satisfies_block_conditions($achievement, $userid)
{
	global $DB;
	$achievement_conditions = $DB->get_records('achievements_condition', array('achievementid' => $achievement->id));
	$satisfies_conditions = $achievement->connective == AND_CONNECTIVE ? true : false;
	if(empty($achievement_conditions))
	{
		$satisfies_conditions = true;
	}
	else
	{
		foreach($achievement_conditions as $achievement_condition)
		{
			if($achievement_condition->type == 0) // Restrição por pontos
			{
				$user_points = null;
				if(isset($achievement_condition->prblockid)) // Se a restrição for por pontos no bloco
				{
					$user_points = get_points($achievement_condition->prblockid, $userid);
				}
				else // Se a restrição for por pontos em um sistema de pontos específico
				{
					$user_points = get_points_system_points($achievement_condition->prpointsystemid, $userid);
				}
				
				
				if($user_points >= $achievement_condition->prpoints) // Se satisfaz a condição
				{
					if($achievement->connective == OR_CONNECTIVE) // E se o conectivo for OR
					{
						$satisfies_conditions = true;
						break;
					}
				}
				else // Se não satisfaz a condição
				{
					if($achievement->connective == AND_CONNECTIVE) // E se o conectivo for AND
					{
						$satisfies_conditions = false;
						break;
					}
				}
			}
			else if($achievement_condition->type == 1) // Restrição por conteúdo desbloqueado
			{
				$sql = "SELECT count(u.id) as times
					FROM
						{content_unlock_log} u
					INNER JOIN {logstore_standard_log} l ON u.logid = l.id
					WHERE l.userid = :userid
						AND  u.unlocksystemid = :unlocksystemid
					GROUP BY l.userid";
				$params['unlocksystemid'] = $achievement_condition->urunlocksystemid;
				$params['userid'] = $userid;
				
				$times = $DB->get_field_sql($sql, $params);

				if(!isset($times))
				{
					$times = 0;
				}
				
				if(($achievement_condition->urmust && $times > 0) || (!$achievement_condition->urmust && $times == 0)) // Se satisfaz a condição
				{
					if($achievement->connective == OR_CONNECTIVE) // E se o conectivo for OR
					{
						$satisfies_conditions = true;
						break;
					}
				}
				else // Se não satisfaz a condição
				{
					if($achievement->connective == AND_CONNECTIVE) // E se o conectivo for AND
					{
						$satisfies_conditions = false;
						break;
					}
				}
			}
			else // Restrição por conquista atingida
			{
				$unlocked_achievement = $DB->record_exists('achievements_log', array('userid' => $userid, 'achievementid' => $achievement_condition->arachievementid));
				if($unlocked_achievement) // Se satisfaz a condição
				{
					if($achievement->connective == OR_CONNECTIVE) // E se o conectivo for OR
					{
						$satisfies_conditions = true;
						break;
					}
				}
				else // Se não satisfaz a condição
				{
					if($achievement->connective == AND_CONNECTIVE) // E se o conectivo for AND
					{
						$satisfies_conditions = false;
						break;
					}
				}
			}
		}
	}
	
	return $satisfies_conditions;
}

function is_student($userid)
{
	return user_has_role_assignment($userid, 5);
}

function get_achievements($blockinstanceid)
{
	global $DB;

	$achievements = $DB->get_records('achievements', array('blockinstanceid' => $blockinstanceid, 'deleted' => 0));
	$links = $DB->get_records('achievements_link', array('blockinstanceid' => $blockinstanceid), '', 'targetblockinstanceid');
	
	foreach($links as $link)
	{
		$link_achievements = get_achievements($link->targetblockinstanceid);
		foreach($link_achievements as $link_achievement)
		{
			$achievements[] = $link_achievement;
		}
	}
	
	usort($achievements, function ($a, $b) {
		if($a->event < $b->event)
		{
			return -1;
		}
		else if($a->event > $b->event)
		{
			return 1;
		}
		else if($a->times < $b->times)
		{
			return -1;
		}
		else if($a->times > $b->times)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	});
	
	return $achievements;
}

class conditions_info extends \core_availability\info
{
    public function __construct($course = null)
	{
        global $SITE;
        if (!$course) {
            $course = $SITE;
        }
        parent::__construct($course, true, null);
    }

    protected function get_thing_name()
	{
        return 'Conditions';
    }

    public function get_context()
	{
        return \context_course::instance($this->get_course()->id);
    }

    protected function get_view_hidden_capability()
	{
        return 'moodle/course:viewhiddensections';
    }

    protected function set_in_database($availability)
	{
    }
	
	public function get_modinfo() {
        return get_fast_modinfo($this->course);
    }
}
