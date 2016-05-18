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
 * Achievements block link add form definition.
 *
 * @package    block_game_achievements
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");
 
class block_game_achievements_linkadd_form extends moodleform
{
 
	function __construct($blockid)
	{
		$this->blockid = $blockid;
		parent::__construct();
	}
 
    function definition()
	{
		global $DB;
 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('linkadd_header', 'block_game_achievements'));

		$block_instances = array();
		$blocks_info = $DB->get_records('block_instances', array('blockname' => 'game_achievements'));
		$block_context_level = context::instance_by_id($blocks_info[$this->blockid]->parentcontextid)->contextlevel;
		foreach($blocks_info as $info)
		{
			$instance = block_instance('game_achievements', $info);
			
			$instance_context_level = context::instance_by_id($instance->instance->parentcontextid)->contextlevel;
			if($block_context_level > $instance_context_level || $instance->instance->id == $this->blockid)
			{
				continue;
			}
			if($DB->count_records('achievements_link', array('blockinstanceid' => $this->blockid, 'targetblockinstanceid' => $instance->instance->id)) > 0)
			{
				continue;
			}
			
			$block_instances[$instance->instance->id] = $instance->title;
		}
		$mform->addElement('select', 'targetblockinstanceid',  get_string('linkadd_targettext', 'block_game_achievements'), $block_instances, null);
		$mform->addRule('targetblockinstanceid', null, 'required', null, 'client');
		
		$mform->addElement('hidden', 'blockinstanceid');
		$mform->setType('blockinstanceid', PARAM_INT);
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		
		$this->add_action_buttons(true, get_string('linkadd_submit', 'block_game_achievements'));
    }
}

?>