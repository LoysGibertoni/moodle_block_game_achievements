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
 
		$connective = $DB->get_field('achievements', 'connective', array('id' => $this->achievementid));
		$connectives_array = array(AND_CONNECTIVE => 'E', OR_CONNECTIVE => 'Ou');
		$select = $mform->addElement('select', 'connective', 'Conectivo', $connectives_array);
		$mform->addRule('connective', null, 'required', null, 'client');
		$select->setSelected($connective);
 
		$operators_array = array(EQUAL => 'iguais a', GREATER => 'maiores que', LESS => 'menores que', EQUALORGREATER => 'maiores ou iguais a', EQUALORLESS => 'menores ou iguais a', BETWEEN => 'entre');
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
				$block_id = null;
				if(isset($condition->prpointsystemid))
				{
					$block_id = $DB->get_field('points_system', 'blockinstanceid', array('id' => $condition->prpointsystemid));
					$block_info = $DB->get_record('block_instances', array('id' => $block_id));
				}
				else
				{
					$block_info = $DB->get_record('block_instances', array('id' => $condition->prblockid));
				}
				$instance = block_instance('game_points', $block_info);
				
				
				$url = new moodle_url('/blocks/game_achievements/conditiondelete.php', array('conditionid' => $condition->id, 'courseid' => $COURSE->id));
				$html .= '<tr><td>Os pontos do aluno no' . (isset($condition->prblockid) ? ' bloco ' . $instance->title  : ' sistema de pontos ' . $condition->prpointsystemid . ' (bloco ' . $instance->title . ')' ) . ' devem ser ' . $operators_array[$condition->properator] . ' ' . $condition->prpoints . ($condition->properator == BETWEEN ? (' e ' . $condition->prpointsbetween) : '') . ' pontos' . '</td><td>' . html_writer::link($url, 'Remover') . '</td></tr>';
			}
			else // Restrição por conteúdo desbloqueado
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
		}
		$url = new moodle_url('/blocks/game_achievements/conditionadd.php', array('achievementid' => $this->achievementid, 'courseid' => $COURSE->id));
		$html .= '</table>' . html_writer::link($url, 'Adicionar restrição');
		
		$mform->addElement('html', $html);
 
        $mform->addElement('hidden', 'achievementid');
		$mform->addElement('hidden', 'courseid');
		
		$this->add_action_buttons();
    }
}

?>