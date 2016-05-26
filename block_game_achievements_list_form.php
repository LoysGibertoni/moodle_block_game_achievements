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
		
		$content = null;
		$achievements_text_list = array();
		
		$events = generate_events_list();
		$achievements = get_achievements($blockinstanceid);
		foreach($achievements as $achievement)
		{
			$unlocked_achievement = $DB->record_exists('achievements_log', array('userid' => $USER->id, 'achievementid' => $achievement->id));
			
			if($unlocked_achievement)
			{
				$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
				$achievements_text_list[] = '<li>' . $description . ' ' . $achievement->times . ' ' . get_string('achievementlist_times', 'block_game_achievements') . ' (' . get_string('achievementlist_unlocked', 'block_game_achievements') . ')' . '</li>';
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
				
				$description = is_null($achievement->description) ? $events[$achievement->event] : $achievement->description;
				$achievements_text_list[] = '<li>' . $description . ' ' . $times . '/' . $achievement->times . ' ' . get_string('achievementlist_times', 'block_game_achievements') . '</li>';
			}
		}
		
		if(empty($achievements_text_list))
		{
			$content = '<p>' . get_string('achievementlist_noachievements', 'block_game_achievements') . '</p>';
		}
		else
		{
			$content = '<p><ul>' . implode($achievements_text_list) . '</ul></p>';
		}
		
		$mform->addElement('html', $content);
		
    }

}

?>