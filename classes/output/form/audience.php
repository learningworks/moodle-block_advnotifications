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
 * Reports form.
 *
 * @package   block_advnotifications
 * @copyright 2020 Daniel Neis Araujo <daniel@adapta.online>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_advnotifications\output\form;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use moodleform;

/**
 * Audience form class.
 *
 * @package   block_advnotifications
 * @copyright 2020 Daniel Neis Araujo <daniel@adapta.online>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class audience extends moodleform {

    public function definition() {

        $mform = $this->_form;

        $notificationid = $this->_customdata['notificationid'];

        list($options, $values) = \block_advnotifications\audience::get_cohorts_for_autocomplete($notificationid);

        $autocomplete = $mform->addElement(
            'autocomplete',
            'cohorts',
            get_string('cohorts', 'cohort' ),
            $options,
            ['multiple' => true]
        );
        $autocomplete->setSelected($values);

        list($options, $values) = \block_advnotifications\audience::get_roles_for_autocomplete($notificationid);
        $autocomplete = $mform->addElement(
            'autocomplete',
            'roles',
            get_string('roles'),
            $options,
            ['multiple' => true]
        );
        $autocomplete->setSelected($values);

        $elements = [
            $mform->createElement('select', 'userfield', '', $this->filter_options()),
            $mform->createElement('select', 'operator', '', $this->operator_options()),
            $mform->createElement('text', 'fieldvalue', '', ['size' => 12]),
        ];
        $filters = $mform->createElement('group', 'userfieldfilters', get_string('filter_userfield', 'block_advnotifications'), $elements);
        $deletebutton = $mform->createElement('submit', 'deletefieldrule', 'X', [], false);

        $rules = [
            'userfieldfilters[userfield]' => ['type' => PARAM_TEXT],
            'userfieldfilters[operator]' => ['type' => PARAM_TEXT],
            'userfieldfilters[fieldvalue]' => ['type' => PARAM_TEXT]
        ];

        $this->repeat_elements([$filters, $deletebutton], $this->_customdata['filterscount'], $rules,
            'filterscount', 'adduserfieldfilter', 1, get_string('adduserfieldfilter', 'block_advnotifications'),
            true, 'deletefieldrule');

        $this->add_action_buttons();
    }

    protected function filter_options() {
        global $DB;

        $filters = [
            ''            => get_string('choosedots'),
            'id'          => 'id',
            'username'    => get_string('username'),
            'idnumber'    => get_string('idnumber'),
            'firstname'   => get_string('firstname'),
            'lastname'    => get_string('lastname'),
            'fullname'    => get_string('fullnameuser'),
            'email'       => get_string('email'),
            'phone1'      => get_string('phone1'),
            'phone2'      => get_string('phone2'),
            'institution' => get_string('institution'),
            'department'  => get_string('department'),
            'address'     => get_string('address'),
            'city'        => get_string('city'),
            'timezone'    => get_string('timezone'),
            'url'         => get_string('webpage'),
        ];

        if ($profilefields = $DB->get_records('user_info_field', [], 'sortorder ASC')) {
            foreach ($profilefields as $f) {
                $filters['profile_field_' . $f->shortname] = format_string($f->name);
            }
        }

        return $filters;
    }

    protected function operator_options() {
        return [
            '' => get_string('choosedots'),
            'beginwith' => get_string('operator_beginwith', 'block_advnotifications'),
            'contains' => get_string('operator_contains', 'block_advnotifications'),
            'equals' => get_string('operator_equals', 'block_advnotifications')
        ];
    }
}
