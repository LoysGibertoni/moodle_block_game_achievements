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
 * Achievements system delete form definition.
 *
 * @package    block_game_achievements
 * @copyright  20016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");
 
class block_game_achievements_delete_form extends moodleform
{
    function definition()
	{
		$mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('achievementdelete_header', 'block_game_achievements'));
		
		$mform->addElement('html', get_string('achievementdelete_message', 'block_game_achievements'));
		
		$mform->addElement('hidden', 'courseid');
		$mform->addElement('hidden', 'achievementid');
		
		$this->add_action_buttons(true, get_string('achievementdelete_submit', 'block_game_achievements'));
    }	
}

?>