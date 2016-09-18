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
 * Achievements delete advanced condition form definition.
 *
 * @package    block_game_achievements
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");
 
class block_game_achievements_advancedconditiondelete_form extends moodleform
{
 
    function definition()
	{
		$mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('advancedconditiondeleteheading', 'block_game_achievements'));
		
		$mform->addElement('html', get_string('advancedconditiondeletemessage', 'block_game_achievements'));
		
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		$mform->addElement('hidden', 'conditionid');
		$mform->setType('conditionid', PARAM_INT);
		
		$this->add_action_buttons(true, get_string('advancedconditiondeletebutton', 'block_game_achievements'));
    }
	
}

?>