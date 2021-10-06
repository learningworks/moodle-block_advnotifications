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

    public static function get_users_for_notification($notification) {
        global $DB;

        $joins = [];
        $wheres = [];
        $params = [];

        if (!empty($notification->blockid) && $notification->blockid > 0) {
            $bcontext = \context_block::instance($notification->blockid);
            $coursecontext = $bcontext->get_course_context(false);
        } else {
            $coursecontext = \context_system::instance();
        }

        if ($DB->record_exists('block_advnotifications_coh', ['notificationid' => $notification->id])) {
           $joins[] =
            ' JOIN {cohort_members} cm
                ON cm.userid = u.id
              JOIN {block_advnotifications_coh} anc
                ON anc.cohortid = cm.cohortid';
           $wheres[] = 'anc.notificationid = :notificationid';
           $params['notificationid'] = $notification->id;
        }

        if ($roles = $DB->get_records_menu('block_advnotifications_roles', ['notificationid' => $notification->id], '', 'roleid')) {

            // TODO: role assignments in sub-contexts.
            $roles = array_keys($roles);
            list($rolesql, $roleparams) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'roleid');
            $joins[] =
             ' JOIN {role_assignments} ra
                 ON ra.userid = u.id';
            $wheres[] = ' ra.contextid = :contextid ';
            $wheres[] = ' ra.roleid ' . $rolesql;
            $params['contextid'] = $coursecontext->id;
            $params = array_merge($params, $roleparams);
        }

        if ($fields = $DB->get_records('block_advnotifications_field', ['notificationid' => $notification->id])) {
            $customfields = [];
            $fieldsapi = \core_user\fields::for_name();
            foreach ($fields as $key => $f) {
                if (strpos($f->userfield, 'profile_field_') === false) {
                    switch ($f->operator) {
                        case 'equals':
                            $wheres[] = " u.{$f->userfield} = :field{$key}";
                            $params['field'.$key] = $f->fieldvalue;
                            break;
                        case 'beginwith':
                            $wheres[] = $DB->sql_like("u.{$f->userfield}", ":field{$key}", false);
                            $params['field'.$key] = $f->fieldvalue . '%';
                            break;
                        case 'contains':
                            $wheres[] = $DB->sql_like("u.{$f->userfield}", ":field{$key}", false);
                            $params['field'.$key] = '%' . $f->fieldvalue . '%';
                            break;
                    }
                } else {
                    $fieldsapi->including($f->userfield);
                    $customfields[] = $f;
                }
            }
            $fieldsall = $fieldsapi->get_sql('u', true);
            $joins[] = $fieldsall->joins;
            $params = array_merge($params, $fieldsall->params);
            foreach ($customfields as $key => $f) {
                switch ($f->operator) {
                    case 'equals':
                        $wheres[] = " {$fieldsall->mappings[$f->userfield]} = :field{$key}";
                        $params['field'.$key] = $f->fieldvalue;
                        break;
                    case 'beginwith':
                        $wheres[] = $DB->sql_like($fieldsall->mappings[$f->userfield], ":field{$key}", false);
                        $params['field'.$key] = $f->fieldvalue . '%';
                        break;
                    case 'contains':
                        $wheres[] = $DB->sql_like($fieldsall->mappings[$f->userfield], ":field{$key}", false);
                        $params['field'.$key] = '%' . $f->fieldvalue . '%';
                        break;
                }
            }
        }

        if (isset($coursecontext)) {
            // TODO: enrolment in sub contexts.
            $enrolledjoin = get_enrolled_join($coursecontext, 'u.id', true);
            $joins[] = $enrolledjoin->joins;
            $wheres[] = $enrolledjoin->wheres;
            $params = array_merge($params, $enrolledjoin->params);
        }

        $sql = "SELECT u.id
                  FROM {user} u
                   " . implode("\n", $joins) . "
                 WHERE u.deleted = 0
                   AND " . implode("\n AND ", $wheres);

        return $DB->get_records_sql($sql, $params);
    }
}
