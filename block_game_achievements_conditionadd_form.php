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
 * Achievements block add condition form definition.
 *
 * @package    block_game_achievements
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");

class block_game_achievements_conditionadd_form extends moodleform
{
 
	function __construct($achievementid)
	{
		$this->achievementid = $achievementid;
		parent::__construct();
	}
 
    function definition()
	{
		global $DB, $COURSE;
 
		$mform =& $this->_form;
		$mform->addElement('header','displayinfo', get_string('conditionadd_header', 'block_game_points'));

		$condition_types = array();
		$game_points_installed = $DB->record_exists('block', array('name' => 'game_points'));
		if($game_points_installed)
		{
			$condition_types[0] = get_string('conditionadd_typebypointstext', 'block_game_achievements');
		}
		$game_content_unlock_installed = $DB->record_exists('block', array('name' => 'game_content_unlock'));
		if($game_content_unlock_installed)
		{
			$condition_types[1] = get_string('conditionadd_typebyunlocktext', 'block_game_achievements');
		}
		$condition_types[2] = get_string('conditionadd_typebyachievementtext', 'block_game_achievements');
		
		$mform->addElement('select', 'condition_type', get_string('conditionadd_typetext', 'block_game_points'), $condition_types, null);
		$mform->addRule('condition_type', null, 'required', null, 'client');
		
		if($game_points_installed)
		{
			$mform->addElement('html', '<hr></hr>');
			
			$points_systems = array();
			$blocks_info = $DB->get_records('block_instances', array('blockname' => 'game_points'));
			foreach($blocks_info as $info)
			{
				$instance = block_instance('game_points', $info);
				
				$points_systems['block::' . $instance->instance->id] = '- Bloco ' . $instance->title;
				
				$sql = 'SELECT id
							FROM {points_system}
							WHERE blockinstanceid = :blockinstanceid
								AND deleted = :deleted';
								
				$params['blockinstanceid'] = $instance->instance->id;
				$params['deleted'] = 0;
			
				$point_system_ids = $DB->get_fieldset_sql($sql, $params);
				foreach($point_system_ids as $point_system_id)
				{
					$points_systems['pointsystem::' . $point_system_id] = '&nbsp;&nbsp;&nbsp;&nbsp;Sistema de pontos ' . $point_system_id;
				}
			}
			$mform->addElement('select', 'points_condition_blockorpointsystemid', 'Os pontos do bloco', $points_systems, null);
			$mform->disabledIf('points_condition_blockorpointsystemid', 'condition_type', 'neq', 0);
			
			$mform->addElement('text', 'points_condition_points', 'Maiores ou iguais a');
			$mform->disabledIf('points_condition_points', 'condition_type', 'neq', 0);

			$achievement_info = $DB->get_record('achievements', array('id' => $this->achievementid));
			if($achievement_info->groupmode)
			{
				$mform->addElement('advcheckbox', 'points_condition_grupal', 'Grupal', null, null, array(0, 1));
			}
		}
		
		if($game_content_unlock_installed)
		{
			$mform->addElement('html', '<hr></hr>');
			
			$unlock_systems = array();
			$blocks_info = $DB->get_records('block_instances', array('blockname' => 'game_content_unlock'));
			foreach($blocks_info as $info)
			{
				$instance = block_instance('game_content_unlock', $info);
				
				$sql = "SELECT *
						FROM
							{content_unlock_system} u
						WHERE u.deleted = 0
							AND u.blockinstanceid = :blockinstanceid";
				$params['blockinstanceid'] = $instance->instance->id;
				
				$us = $DB->get_records_sql($sql, $params);
				
				foreach($us as $unlock_system)
				{
					$course = $DB->get_record('course', array('id' => $COURSE->id));
					$info = get_fast_modinfo($course);
					$cm = $info->get_cm($unlock_system->coursemoduleid);
					
					$unlock_systems[$unlock_system->id] =  ($unlock_system->coursemodulevisibility ? 'Desbloqueado' : 'Bloqueado') . ' o recurso/atividade ' . $cm->name . ' (bloco ' . $instance->title . ')';
				}
			}
			
			$mform->addElement('select', 'unlock_condition_must', 'O aluno', array(0 => 'Não deve', 1 => 'Deve'), null);
			$mform->setDefault('unlock_condition_must', 1);
			$mform->disabledIf('unlock_condition_must', 'condition_type', 'neq', 1);
			
			$mform->addElement('select', 'unlock_condition_unlocksystemid', 'Ter', $unlock_systems, null);
			$mform->disabledIf('unlock_condition_unlocksystemid', 'condition_type', 'neq', 1);
		}
		
		// Achievement condition
		$mform->addElement('html', '<hr></hr>');
		
		$achievements = array();
		$blocks_info = $DB->get_records('block_instances', array('blockname' => 'game_achievements'));
		foreach($blocks_info as $info)
		{
			$instance = block_instance('game_achievements', $info);
			
			$sql = 'SELECT id
						FROM {achievements}
						WHERE blockinstanceid = :blockinstanceid
							AND deleted = :deleted';
							
			$params['blockinstanceid'] = $instance->instance->id;
			$params['deleted'] = 0;
		
			$achievement_ids = $DB->get_fieldset_sql($sql, $params);
			foreach($achievement_ids as $achievement_id)
			{
				$achievements[$achievement_id] = 'Conquista ' . $achievement_id . ' (bloco ' . $instance->title . ')';
			}
		}
		$mform->addElement('select', 'achievements_condition_achievementid', 'Ter alcançado a', $achievements, null);
		$mform->disabledIf('achievements_condition_achievementid', 'condition_type', 'neq', 2);
		
		$mform->addElement('hidden', 'achievementid');
		$mform->addElement('hidden', 'courseid');
		
		$this->add_action_buttons();
    }
}

?>