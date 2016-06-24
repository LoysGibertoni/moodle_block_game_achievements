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
		
		// Group settings
		$mform->addElement('header', 'groupsettingsheader', get_string('achievementadd_groupsettingsheader', 'block_game_achievements'));
		
		$mform->addElement('advcheckbox', 'groupmode', get_string('achievementadd_groupmodetext', 'block_game_achievements'), null, null, array(0, 1));
		$mform->setType('groupmode', PARAM_INT);
		$mform->addRule('groupmode', null, 'required', null, 'client');
		
		$mform->addElement('advcheckbox', 'allmembers', get_string('achievementadd_allmemberstext', 'block_game_achievements'), null, null, array(0, 1));
		$mform->setType('allmembers', PARAM_INT);
		$mform->disabledIf('allmembers', 'groupmode', 'eq', 0);

		$options = array(SEPARATEGROUPS => get_string('groupsseparate'),
						 VISIBLEGROUPS  => get_string('groupsvisible'));
		$mform->addElement('select', 'groupvisibility', get_string('groupmode', 'group'), $options, SEPARATEGROUPS);
		//$mform->addHelpButton('groupvisibility', 'groupmode', 'group');
		$mform->disabledIf('groupvisibility', 'groupmode', 'eq', 0);
		
		$options = array();
		if ($groupings = $DB->get_records('groupings', array('courseid'=>$COURSE->id))) {
			foreach ($groupings as $grouping) {
				$options[$grouping->id] = format_string($grouping->name);
			}
		}
		core_collator::asort($options);
		$options = array(0 => get_string('none')) + $options;
		$mform->addElement('select', 'groupingid', get_string('grouping', 'group'), $options);
		//$mform->addHelpButton('groupingid', 'grouping', 'group');
		$mform->disabledIf('groupingid', 'groupmode', 'eq', 0);

		// Hidden elements
		$mform->addElement('hidden', 'blockinstanceid');
		$mform->setType('blockinstanceid', PARAM_INT);
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		
		$this->add_action_buttons(true, get_string('achievementadd_submit', 'block_game_achievements'));
    }
}

?>