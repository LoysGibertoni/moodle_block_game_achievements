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
 * Achievements system edit form definition.
 *
 * @package    block_game_achievements
 * @copyright  20016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");
require_once('lib.php');

class block_game_achievements_edit_form extends moodleform {
 
	function __construct($id, $courseid)
	{
		$this->id = $id;
		$this->courseid = $courseid;
		parent::__construct();
	}
 
    function definition()
	{
		global $COURSE, $CFG, $DB;
 
		$achievement = $DB->get_record('achievements', array('id' => $this->id));
 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('achievementedit_header', 'block_game_achievements'));

		$eventsarray = generate_events_list(true);
		$select = $mform->addElement('select', 'event', get_string('achievementedit_eventtext', 'block_game_achievements'), $eventsarray, null);
		$mform->addRule('event', null, 'required', null, 'client');
		$select->setSelected($achievement->event);
		
		$mform->addElement('text', 'times', get_string('achievementedit_timestext', 'block_game_achievements'));
		$mform->addRule('times', null, 'required', null, 'client');
		$mform->setType('times', PARAM_INT);
		$mform->setDefault('times', $achievement->times);

		$mform->addElement('text', 'description', get_string('achievementedit_descriptiontext', 'block_game_achievements'));
		$mform->setType('description', PARAM_TEXT);
		$mform->setDefault('description', $achievement->description);
		
		// Group settings
		$mform->addElement('header', 'groupsettingsheader', get_string('achievementedit_groupsettingsheader', 'block_game_achievements'));
		
		$mform->addElement('advcheckbox', 'groupmode', get_string('achievementadd_groupmodetext', 'block_game_achievements'), null, null, array(0, 1));
		$mform->setType('groupmode', PARAM_INT);
		$mform->addRule('groupmode', null, 'required', null, 'client');
		$mform->setDefault('groupmode', $achievement->groupmode);
		
		$mform->addElement('advcheckbox', 'allmembers', get_string('achievementadd_allmemberstext', 'block_game_achievements'), null, null, array(0, 1));
		$mform->setType('allmembers', PARAM_INT);
		$mform->setDefault('allmembers', $achievement->allmembers);
		$mform->disabledIf('allmembers', 'groupmode', 'eq', 0);
		
		// Hidden elements
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		$mform->addElement('hidden', 'achievementid');
		$mform->setType('achievementid', PARAM_INT);
		
		$this->add_action_buttons(true, get_string('achievementedit_submit', 'block_game_achievements'));
    }
	
}

?>