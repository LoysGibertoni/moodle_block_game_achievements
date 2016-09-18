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
 * Achievements block manage conditions form definition.
 *
 * @package    block_game_achievements
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");
 
define("EQUAL", 0);
define("GREATER", 1);
define("LESS", 2);
define("EQUALORGREATER", 3);
define("EQUALORLESS", 4);
define("BETWEEN", 5);
define("AND_CONNECTIVE", 0);
define("OR_CONNECTIVE", 1);
 
class block_game_achievements_conditionmanage_form extends moodleform
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
 
		$achievement = $DB->get_record('achievements', array('id' => $this->achievementid));

		// Conditions
		$mform->addElement('header', 'availabilityconditionsheader', get_string('restrictaccess', 'availability'));
		$mform->addElement('textarea', 'availabilityconditionsjson', get_string('accessrestrictions', 'availability'));
		$mform->setDefault('availabilityconditionsjson', $achievement->conditions);
		\core_availability\frontend::include_all_javascript($COURSE, null);

		$mform->addElement('html', '<hr></hr>');

		$connective = $DB->get_field('achievements', 'connective', array('id' => $this->achievementid));
		$connectives_array = array(AND_CONNECTIVE => 'E', OR_CONNECTIVE => 'Ou');
		$select = $mform->addElement('select', 'connective', 'Conectivo', $connectives_array);
		$mform->addRule('connective', null, 'required', null, 'client');
		$select->setSelected($connective);
 
		$html = '<table>
					<tr>
						<th>Descrição</th>
						<th>Remover</th>
					</tr>';
		$conditions = $DB->get_records('achievements_condition', array('achievementid' => $this->achievementid));
		foreach($conditions as $condition)
		{
			if($condition->type == 0) // Restrição por pontos
			{
				$block_info = null;
				if(isset($condition->prpointsystemid))
				{
					$block_id = $DB->get_field('points_system', 'blockinstanceid', array('id' => $condition->prpointsystemid));
					$block_info = $DB->get_record('block_instances', array('id' => $block_id));

					$points_system_name = $DB->get_field('points_system', 'name', array('id' => $condition->prpointsystemid));
				}
				else
				{
					$block_info = $DB->get_record('block_instances', array('id' => $condition->prblockid));
				}
				
				$instance = block_instance('game_points', $block_info);
				
				$url = new moodle_url('/blocks/game_achievements/conditiondelete.php', array('conditionid' => $condition->id, 'courseid' => $COURSE->id));
				$html .= '<tr><td>Os pontos ' . ($condition->prgrupal ? 'grupais' : 'individuais') . ' do aluno no' . (isset($condition->prblockid) ? ' bloco ' . $instance->title  : ' sistema de pontos ' . (empty($points_system_name) ? $condition->prpointsystemid : $points_system_name . ' (' . $condition->prpointsystemid . ')') . ' (bloco ' . $instance->title . ')' ) . ' devem ser maiores ou iguais a ' . $condition->prpoints . ' pontos' . '</td><td>' . html_writer::link($url, 'Remover') . '</td></tr>';
			}
			else if($condition->type == 1) // Restrição por conteúdo desbloqueado
			{
				$unlock_system = $DB->get_record('content_unlock_system', array('id' => $condition->urunlocksystemid));
				
				$course = $DB->get_record('course', array('id' => $COURSE->id));
				$info = get_fast_modinfo($course);
				$cm = $info->get_cm($unlock_system->coursemoduleid);
				
				$block_info = $DB->get_record('block_instances', array('id' => $unlock_system->blockinstanceid));
				$instance = block_instance('game_content_unlock', $block_info);
				
				$url = new moodle_url('/blocks/game_achievements/conditiondelete.php', array('conditionid' => $condition->id, 'courseid' => $COURSE->id));
				$html .= '<tr><td>O aluno ' . ($condition->urmust ? 'deve' : 'não deve') . ' ter ' . ($unlock_system->coursemodulevisibility ? 'desbloqueado' : 'bloqueado') . ' o recurso/atividade ' . $cm->name . ' (bloco ' . $instance->title . ')' . '</td><td>' . html_writer::link($url, 'Remover') . '</td></tr>';
			}
			else // Restrição por conquista atingida
			{
				$achievement = $DB->get_record('achievements', array('id' => $condition->arachievementid));
				
				$block_info = $DB->get_record('block_instances', array('id' => $achievement->blockinstanceid));
				$instance = block_instance('game_achievements', $block_info);
				
				$url = new moodle_url('/blocks/game_achievements/conditiondelete.php', array('conditionid' => $condition->id, 'courseid' => $COURSE->id));
				$html .= '<tr><td>O aluno deve ter atingido a conquista ' . (isset($achievement->name) ? $achievement->name . ' (' . $achievement->id . ')' : $achievement->id)  . ' (bloco ' . $instance->title . ')</td><td>' . html_writer::link($url, 'Remover') . '</td></tr>';
			}
		}
		$url = new moodle_url('/blocks/game_achievements/conditionadd.php', array('achievementid' => $this->achievementid, 'courseid' => $COURSE->id));
		$html .= '</table>' . html_writer::link($url, 'Adicionar restrição');
		
		$mform->addElement('html', $html);

		// Advanced conditions
		$mform->addElement('html', '<hr></hr>');

		$advconnective = $DB->get_field('achievements', 'advconnective', array('id' => $this->achievementid));
		$select = $mform->addElement('select', 'advconnective', 'Conectivo de restrições avançadas', $connectives_array);
		$mform->addRule('advconnective', null, 'required', null, 'client');
		$select->setSelected($advconnective);
 
		$html = '<table>
					<tr>
						<th>' . get_string('conditionmanagesql', 'block_game_achievements') . '</th>
						<th>' . get_string('conditionmanagetrueif', 'block_game_achievements') . '</th>
						<th>' . get_string('conditionmanagedelete', 'block_game_achievements') . '</th>
					</tr>';
		$conditions = $DB->get_records('achievements_advcondition', array('achievementid' => $this->achievementid));
		foreach($conditions as $condition)
		{
			$url = new moodle_url('/blocks/game_achievements/advancedconditiondelete.php', array('conditionid' => $condition->id, 'courseid' => $COURSE->id));
			
			if($condition->trueif == 0)
			{
				$trueif = get_string('advancedconditionaddtrueifzero', 'block_game_achievements');
			}
			else if($condition->trueif == 1)
			{
				$trueif = get_string('advancedconditionaddtrueifnotzero', 'block_game_achievements');
			}
			else
			{
				$trueif = get_string('advancedconditionaddtrueifegthan', 'block_game_achievements') . ' ' . $condition->count;
			}
			
			$html .= '<tr>
					 	<td>' . get_string('advancedconditionaddselect', 'block_game_achievements') . ' ' . $condition->whereclause . '</td>
						 <td>' . $trueif . '</td>
						<td>' . html_writer::link($url, get_string('conditionmanagedelete', 'block_game_achievements')) . '</td>
					 </tr>';
		}
		$url = new moodle_url('/blocks/game_achievements/advancedconditionadd.php', array('achievementid' => $this->achievementid, 'courseid' => $COURSE->id));
		$html .= '</table>' . html_writer::link($url, get_string('conditionmanageadd', 'block_game_achievements'));

		$mform->addElement('html', $html);

        $mform->addElement('hidden', 'achievementid');
		$mform->addElement('hidden', 'courseid');
		
		$this->add_action_buttons();
    }
}

?>