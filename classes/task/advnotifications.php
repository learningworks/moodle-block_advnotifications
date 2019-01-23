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
 * Setup scheduled tasks for performing routine maintenance.
 *
 * @package    block_advnotifications
 * @copyright  2017 LearningWorks Ltd - learningworks.co.nz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_advnotifications\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Class advnotifications - extends core to leverage the Tasks API.
 *
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class advnotifications extends \core\task\scheduled_task {
    /**
     * Get the name of the task.
     *
     * @return string.
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('advnotifications_task_name', 'block_advnotifications');
    }

    /**
     * Remove old (or deleted) notifications from table block_advnotifications & cleanup table block_advnotificationsdissed.
     */
    public function execute() {
        global $DB;

        // TODO - echo "\n\t" . get_string('advnotifications_cron_heading', 'block_advnotifications') . "\n";.

        // Auto-Permanent Delete Feature.
        if (get_config('block_advnotifications', 'auto_perma_delete')) {
            // TODO - echo "\n\t\t- " . get_string('advnotifications_cron_auto_perma_delete', 'block_advnotifications') . "\n";.

            // Permanently delete notifications that's had the deleted flag for more than 30 days.
            $DB->delete_records_select('block_advnotifications',
                'deleted_at < :limit AND deleted_at <> 0 AND deleted = 1',
                array('limit' => strtotime('-30 days'))
            );
        }

        // Auto Delete Flagging Feature.
        if (get_config('block_advnotifications', 'auto_delete')) {
            // TODO - echo "\t\t- " . get_string('advnotifications_cron_auto_delete', 'block_advnotifications') . "\n";.

            // Add deleted flag to notifications that's passed their end-date.
            $DB->set_field_select('block_advnotifications',
                'deleted',
                '1',
                'date_to < :now AND date_from <> date_to',
                array('now' => time())
            );

            // Record time of setting 'deleted' flag.
            $DB->set_field_select('block_advnotifications',
                'deleted_at',
                time(),
                'deleted = 1'
            );
        }

        // Auto User Data Deletion Feature.
        if (get_config('block_advnotifications', 'auto_delete_user_data')) {
            // TODO - echo "\t\t- " . get_string('advnotifications_cron_auto_delete_udata', 'block_advnotifications') . "\n\n";.

            // Remove user records that relates to notifications that don't exist anymore.
            $todelete = $DB->get_records_sql('SELECT band.id
                                                FROM {block_advnotificationsdissed} band
                                           LEFT JOIN {block_advnotifications} ban ON band.not_id = ban.id
                                               WHERE ban.id IS NULL');

            $DB->delete_records_list('block_advnotificationsdissed',
                'id',
                array_keys((array)$todelete)
            );
        }
    }
}


