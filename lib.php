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
 * Achievements block functions definitions.
 *
 * @package    block_game_achievements
 * @copyright  20016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('lib.php');

function generate_events_list($showeventname = false)
{
	$eventsarray = array();

	$eventslist = report_eventlist_list_generator::get_non_core_event_list();
	foreach($eventslist as $value)
	{
		$description = explode("\\", explode(".", strip_tags($value['fulleventname']))[0]);
		$eventsarray[$value['eventname']] = ($showeventname === true ? ($description[0] . " (" . $value['eventname'] . ")") : $description[0]);
	}
	
	return $eventsarray;
}

function satisfies_conditions($conditions, $courseid, $userid)
{
	global $DB;
	
	if(isset($conditions))
	{
		$tree = new \core_availability\tree(json_decode($conditions));
		$course = $DB->get_record('course', array('id' => $courseid));
		$info = new conditions_info($course);
		$result = $tree->check_available(false, $info, true, $userid);
		return $result->is_available();
	}
	
	return true;
}

function is_student($userid)
{
	return user_has_role_assignment($userid, 5);
}

class conditions_info extends \core_availability\info
{
    public function __construct($course = null)
	{
        global $SITE;
        if (!$course) {
            $course = $SITE;
        }
        parent::__construct($course, true, null);
    }

    protected function get_thing_name()
	{
        return 'Conditions';
    }

    public function get_context()
	{
        return \context_course::instance($this->get_course()->id);
    }

    protected function get_view_hidden_capability()
	{
        return 'moodle/course:viewhiddensections';
    }

    protected function set_in_database($availability)
	{
    }
}
