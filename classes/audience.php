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
 * Class for audience rules.
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

namespace block_advnotifications;

defined('MOODLE_INTERNAL') || die;

class audience {

    public static function get_cohorts_for_autocomplete($notificationid) {
        global $DB;

        $cohortssql =
            'SELECT c.id, c.name, nc.id as inuse
               FROM {cohort} c
          LEFT JOIN {block_advnotifications_coh} nc
                 ON c.id = nc.cohortid AND nc.notificationid = ?';

        $cohorts = $DB->get_records_sql($cohortssql, [$notificationid]);

        $options = [];
        $values = [];

        foreach ($cohorts as $c) {
            $options[$c->id] = $c->name;
            if (!is_null($c->inuse)) {
                $values[] = $c->id;
            }
        }
        return [$options, $values];
    }

    public static function get_roles_for_autocomplete($notificationid) {
        global $DB;

        $roles = role_get_names();
        $selectedroles = $DB->get_fieldset_select(
            'block_advnotifications_roles',
            'roleid',
            'notificationid = ?',
            [$notificationid]
        );
        $options = [];
        $values = [];
        foreach ($roles as $r) {
            $options[$r->id] = $r->localname;
            if (in_array($r->id, $selectedroles)) {
                $values[] = $r->id;
            }
        }
        return [$options, $values];
    }

    public static function meets_profile_requirements($notificationid, $userid) {
        global $DB, $USER;
        if (!$rules = $DB->get_records('block_advnotifications_field', ['notificationid' => $notificationid])) {
            return true; // There is no field restriction.
        }
        foreach ($rules as $r) {
            if (strpos($r->userfield, 'profile_field_') === false) {
                $currentvalue = $USER->{$r->userfield};
            } else {
                $field = substr($r->userfield, 14);
                $currentvalue = $USER->profile[$field];
            }
            switch ($r->operator) {
                case 'equals':
                    if ($currentvalue !== $r->fieldvalue) {
                        return false;
                    }
                    break;
                case 'contains':
                    if (strpos($currentvalue, $r->fieldvalue) === false) {
                        return false;
                    }
                    break;
                case 'beginwith':
                    if (strpos($currentvalue, $r->fieldvalue) !== 0) {
                        return false;
                    }
                    break;
            }
        }
        return true;
    }

    public static function meets_cohorts_requirements($notificationid, $userid) {
        global $DB;
        if (!$DB->record_exists('block_advnotifications_coh', ['notificationid' => $notificationid])) {
            return true; // There is no cohort restriction.
        }
        $sql =
        'SELECT 1
           FROM {block_advnotifications_coh} anc
           JOIN {cohort_members} cm
             ON cm.cohortid = anc.cohortid AND
                anc.notificationid = ? AND
                cm.userid = ?';
        return $DB->record_exists_sql($sql, [$notificationid, $userid]);
    }

    public static function meets_roles_requirements($notificationid, $userid, $blockid) {
        global $DB;
        if (!$roles = $DB->get_records('block_advnotifications_roles', ['notificationid' => $notificationid])) {
            return true; // There is no role restriction.
        }
        if ($blockid) {
            $context = \context_block::instance($blockid);
        } else {
            $context = \context_system::instance();
        }
        foreach ($roles as $r) {
            if (!user_has_role_assignment($userid, $r->roleid, $context->id)) {
                return false;
            }
        }
        return true;
    }
}
