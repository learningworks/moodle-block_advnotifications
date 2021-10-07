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
        if (!$roles = $DB->get_records('block_advnotifications_roles', ['notificationid' => $notificationid], '', 'roleid')) {
            return true; // There is no role restriction.
        }
        $roles = array_keys($roles);

        if ($blockid > 0) {
            $context = \context_block::instance($blockid);
            if ($coursecontext = $context->get_course_context(false)) {
                $context = $coursecontext;
            } else {
                $context = $context->get_parent_context(); // It may be category or user context.
                if ($context instanceof \context_user) {
                    $context = \context_system::instance();
                }
            }
        } else {
            $context = \context_system::instance();
        }
        list($rolessql, $params) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'roleid');
        $sql =
            'SELECT 1
               FROM {role_assignments} ra
               JOIN {context} ctx
                 ON ctx.id = ra.contextid
              WHERE ' . $DB->sql_like('ctx.path', ':ctxpath', false) . '
                AND roleid ' . $rolessql;
        $params['ctxpath'] = $context->path . '%';

        return $DB->record_exists_sql($sql, $params);
    }

    public static function get_users_for_notification($notification) {
        global $DB;

        $joins = [];
        $wheres = [];
        $params = [];

        if ($notification->blockid > 0) {
            $context = \context_block::instance($notification->blockid);
            if ($coursecontext = $context->get_course_context(false)) {
                $context = $coursecontext;
            } else {
                $context = $context->get_parent_context(); // It may be category context.
            }
        } else {
            $context = \context_system::instance();
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

            $roles = array_keys($roles);
            list($rolessql, $rolesparams) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'roleid');
            $joins[] =
             ' JOIN {role_assignments} ra
                 ON ra.userid = u.id
               JOIN {context} ctx
                 ON ctx.id = ra.contextid';
            $wheres[] = $DB->sql_like('ctx.path', ':ctxpath', false);
            $wheres[] = ' ra.roleid ' . $rolessql;
            $params['ctxpath'] = $context->path . '%';
            $params = array_merge($params, $rolesparams);
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

        $sql = "SELECT u.id
                  FROM {user} u
                   " . implode("\n", $joins) . "
                 WHERE u.deleted = 0
                   AND " . implode("\n AND ", $wheres);

        return $DB->get_records_sql($sql, $params);
    }
}
