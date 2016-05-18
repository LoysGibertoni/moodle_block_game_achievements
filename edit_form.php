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
 * Achievements block edit form definition.
 *
 * @package    block_game_achievements
 * @copyright  20016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_game_achievements_edit_form extends block_edit_form {
 
    protected function specific_definition($mform)
	{
 		global $COURSE, $DB, $USER, $PAGE;
 
		$context = context_course::instance($COURSE->id);
		if(has_capability('block/game_achievements:manageachievements', $context))
		{
			$mform->addElement('header', 'configheader', get_string('configpage_header', 'block_game_achievements'));
			
			$mform->addElement('text', 'config_title', get_string('configpage_titletext', 'block_game_achievements'));
			$mform->setType('config_title', PARAM_TEXT);
			
			$eventsarray = generate_events_list(true);
			
			$sql = "SELECT *
				FROM {achievements} a
					INNER JOIN {achievements_processor} p ON a.id = p.achievementid
				WHERE p.processorid = :processorid
					AND a.blockinstanceid = :blockinstanceid
					AND a.deleted = :deleted";
			$params['processorid'] = $USER->id;
			$params['blockinstanceid'] = $this->block->instance->id;
			$params['deleted'] = 0;
			$achievements = $DB->get_records_sql($sql, $params);
			
			$html = '<table>
						<tr>
							<th>' . get_string('configpage_idtableheader', 'block_game_achievements') . '</th>
							<th>' . get_string('configpage_eventtableheader', 'block_game_achievements') . '</th>
							<th>' . get_string('configpage_timestableheader', 'block_game_achievements') . '</th>
							<th>' . get_string('configpage_descriptiontableheader', 'block_game_achievements') . '</th>
							<th>' . get_string('configpage_manageconditionstableheader', 'block_game_achievements') . '</th>
							<th>' . get_string('configpage_edittableheader', 'block_game_achievements') . '</th>
							<th>' . get_string('configpage_deletetableheader', 'block_game_achievements') . '</th>
						</tr>';
			foreach($achievements as $achievement)
			{
				$achievement_edit_url = new moodle_url('/blocks/game_achievements/achievementedit.php', array('courseid' => $COURSE->id, 'achievementid' => $achievement->id));
				$achievement_delete_url = new moodle_url('/blocks/game_achievements/achievementdelete.php', array('courseid' => $COURSE->id, 'achievementid' => $achievement->id));
				$condition_manage_url = new moodle_url('/blocks/game_achievements/conditionmanage.php', array('courseid' => $COURSE->id, 'achievementid' => $achievement->id));
				
				$html .= '<tr>
							<td>' . $achievement->id . '</td>
							<td>' . $eventsarray[$achievement->event] . '</td>
							<td>' . $achievement->times . '</td>
							<td>' . $achievement->description . '</td>
							<td>' . html_writer::link($condition_manage_url, get_string('configpage_manageconditionstableheader', 'block_game_achievements')) . '</td>
							<td>' . html_writer::link($achievement_edit_url, get_string('configpage_edittableheader', 'block_game_achievements')) . '</td>
							<td>' . html_writer::link($achievement_delete_url, get_string('configpage_deletetableheader', 'block_game_achievements')) . '</td>
						  </tr>';
			}
			
			$achievement_add_url = new moodle_url('/blocks/game_achievements/achievementadd.php', array('blockinstanceid' => $this->block->instance->id, 'courseid' => $COURSE->id));
			$html .= '</table>' . html_writer::link($achievement_add_url, get_string('configpage_achievementaddtext', 'block_game_achievements'));
			
			$mform->addElement('html', $html);
			
			// Block links
			$mform->addElement('header', 'linkheader', get_string('configpage_linkheader', 'block_game_achievements'));
			
			$sql = "SELECT *
						FROM {achievements_link} l
							INNER JOIN {achievements_link_processor} p ON l.id = p.linkid
						WHERE p.processorid = :processorid
							AND l.blockinstanceid = :blockinstanceid";
			$params['processorid'] = $USER->id;
			$params['blockinstanceid'] = $this->block->instance->id;
			$links = $DB->get_records_sql($sql, $params);
			
			$blocks_info = $DB->get_records('block_instances', array('blockname' => 'game_achievements'));
			
			$html = '<table>
						<tr>
							<th>' . get_string('configpage_idtableheader', 'block_game_achievements') . '</th>
							<th>' . get_string('configpage_targettableheader', 'block_game_achievements') . '</th>
							<th>' . get_string('configpage_deletetableheader', 'block_game_achievements') . '</th>
						</tr>';
			foreach($links as $link)
			{
				$link_delete_url = new moodle_url('/blocks/game_achievements/linkdelete.php', array('courseid' => $COURSE->id, 'linkid' => $link->id));
				$instance = block_instance('game_achievements', $blocks_info[$link->targetblockinstanceid]);
				
				$html .= '<tr>
							<td>' . $link->id . '</td>
							<td>' . $instance->title . '</td>
							<td>' . html_writer::link($link_delete_url, get_string('configpage_deletetableheader', 'block_game_achievements')) . '</td>
						  </tr>';
			}
			
			$link_add_url = new moodle_url('/blocks/game_achievements/linkadd.php', array('blockinstanceid' => $this->block->instance->id, 'courseid' => $COURSE->id));
			$html .= '</table>' . html_writer::link($link_add_url, get_string('configpage_linkaddtext', 'block_game_achievements'));
			
			$mform->addElement('html', $html);
		}			
	}
}

?>