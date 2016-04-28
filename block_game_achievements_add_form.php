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
 * Achievements system add form definition.
 *
 * @package    block_game_achievements
 * @copyright  20016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");
require_once('lib.php');
 
class block_game_achievements_add_form extends moodleform {
 
    function definition()
	{
		global $COURSE, $CFG, $DB;
 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('achievementadd_header', 'block_game_achievements'));

		$eventsarray = generate_events_list(true);
		$mform->addElement('select', 'event', get_string('achievementadd_eventtext', 'block_game_achievements'), $eventsarray, null);
		$mform->addRule('event', null, 'required', null, 'client');
		
		$mform->addElement('text', 'times', get_string('achievementadd_timestext', 'block_game_achievements'));
		$mform->addRule('times', null, 'required', null, 'client');
		$mform->setType('times', PARAM_INT);

		$mform->addElement('text', 'description', get_string('achievementadd_descriptiontext', 'block_game_achievements'));
		$mform->setType('description', PARAM_TEXT);
		
		// Hidden elements
		$mform->addElement('hidden', 'blockinstanceid');
		$mform->addElement('hidden', 'courseid');
		
		$this->add_action_buttons(true, get_string('achievementadd_submit', 'block_game_achievements'));
    }
}

?>