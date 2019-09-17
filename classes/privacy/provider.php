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
 * Privacy API provider.
 *
 * @package    block_advnotifications
 * @copyright  2018 LearningWorks Ltd - learningworks.co.nz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_advnotifications\privacy;

use context_block;
use context_system;
use core_privacy\local\metadata\collection;
use \core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\plugin\provider as plugin_provider;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/** @var string Flag used to determine if notification is block-based or global */
const SITE_NOTIFICATION = "-1";

/**
 * Class provider - extends core to leverage the Privacy API.
 *
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        // This plugin stores personal user data.
        metadata_provider,

        // Data is provided directly to core.
        plugin_provider,

        // This plugin is can determine which users' data it's captured.
        core_userlist_provider {

    /**
     * Get metadata about a user used by the plugin.
     *
     * @param   collection $collection The collection of metadata.
     * @return  collection  $collection The collection returned as a whole.
     */
    public static function get_metadata(collection $collection) : collection {
        mtrace("\tGetting metadata...");

        // Add items to collection.
        $collection->add_database_table(
            'advnotifications',
            [
                'title' => 'privacy:metadata:advnotifications:title',
                'message' => 'privacy:metadata:advnotifications:message',
                'blockid' => 'privacy:metadata:advnotifications:blockid',
                'deleted' => 'privacy:metadata:advnotifications:deleted',
                'deleted_by' => 'privacy:metadata:advnotifications:deleted_by',
                'created_by' => 'privacy:metadata:advnotifications:created_by'
            ],
            'privacy:metadata:advnotifications'
        );

        $collection->add_database_table(
            'advnotificationsdissed',
            [
                'user_id' => 'privacy:metadata:advnotificationsdissed:user_id',
                'not_id' => 'privacy:metadata:advnotificationsdissed:not_id',
                'dismissed' => 'privacy:metadata:advnotificationsdissed:dismissed',
                'seen' => 'privacy:metadata:advnotificationsdissed:seen'
            ],
            'privacy:metadata:advnotificationsdissed'
        );

        mtrace("\tDone 'Getting metadata...'");

        return $collection;
    }

    /**
     * Get list of contexts containing user info for the given user.
     *
     * @param int $userid User ID to find contexts for.
     * @return contextlist  $contextlist    List of contexts used by the plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        mtrace("\tGetting contexts for userid...");

        // User data only on system/block context.
        global $DB;
        $contextlist = new \core_privacy\local\request\contextlist();

        // Get context IDs from block instance IDs of notifications user has seen.
        $contextlist->add_from_sql(
            "SELECT c.id
               FROM {context} c
               JOIN {block_advnotifications} adv ON adv.blockid = c.instanceid
               JOIN {block_advnotificationsdissed} advdis ON advdis.user_id = :userid
              WHERE c.contextlevel = :contextblock",
            array('userid' => $userid, 'contextblock' => CONTEXT_BLOCK)
        );

        // TODO: Check if needed...
        // Check if system context should be added for user.
        $blockids = $DB->get_records_sql(
            "SELECT DISTINCT adv.blockid
               FROM {block_advnotifications} adv
               JOIN {block_advnotificationsdissed} advdis ON advdis.not_id = adv.id
              WHERE advdis.user_id = :userid",
            array('userid' => $userid)
        );

        // Check if block ID is not empy/null.
        if (isset($blockids) && !empty($blockids)) {

            // If notification set to display globally, system context is added.
            if (array_key_exists(SITE_NOTIFICATION, $blockids)) {
                $contextlist->add_system_context();
            }
        }

        mtrace("\tDone 'Getting contexts for userid...'");

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        mtrace("\tGetting users in context...");

        $context = $userlist->get_context();

        // Check if contexts are one block or system-level (others not allowed).
        if (!($context instanceof context_block || $context instanceof context_system)) {
            return;
        }

        // Different sql for system/block contexts.
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            // Get and add user IDs in system context.
            $userlist->add_from_sql('user_id',
                "SELECT advdis.user_id
                   FROM {block_advnotificationsdissed} advdis
                   JOIN {block_advnotifications} adv ON adv.id = advdis.not_id
                  WHERE adv.blockid = :blockid",
                array('blockid' => SITE_NOTIFICATION)
            );
        } else if ($context->contextlevel == CONTEXT_BLOCK) {
            // Get and add user IDs in block context.
            $userlist->add_from_sql('user_id',
                "SELECT advdis.user_id
                   FROM {block_advnotificationsdissed} advdis
                   JOIN {block_advnotifications} adv ON adv.id = advdis.not_id
                   JOIN {context} c ON c.instanceid = adv.blockid
                  WHERE c.contextlevel = :contextlevel",
                array('contextlevel' => CONTEXT_BLOCK)
            );
        }

        mtrace("\tDone 'Getting users in context...'");
    }

    /**
     * Export all of user's data in the given context(s).
     *
     * @param   approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        mtrace("\tExporting user data...");

        global $DB;

        $blockdata = [];
        $sitedata = [];

        $userid = $contextlist->get_user()->id;

        /* TODO: Edge case if user has not seen the notification... What about deleted_by or created_by?
           MAYBE ADD: OR adv.deleted_by = :userid
                      OR adv.created_by = :userid */
        $alluserdata = $DB->get_records_sql(
            "SELECT adv.title, adv.message, adv.blockid, adv.deleted, adv.deleted_by, adv.created_by,
                    advdis.user_id, advdis.dismissed, advdis.seen
               FROM {block_advnotifications} adv
               JOIN {block_advnotificationsdissed} advdis ON advdis.not_id = adv.id
              WHERE advdis.user_id = :userid",
            array('userid' => $userid)
        );

        // Get and export user data.
        foreach ($alluserdata as $userdata) {

            if ($userdata->blockid !== SITE_NOTIFICATION) {
                $blockdata[] = (object)[
                    'title' => $userdata->title,
                    'message' => $userdata->message,
                    'blockid' => $userdata->blockid,
                    'deleted' => transform::yesno($userdata->deleted),
                    'deleted_by' => $userdata->deleted_by,
                    'created_by' => $userdata->created_by,
                    'user_id' => $userdata->user_id,
                    'dismissed' => transform::yesno($userdata->dismissed),
                    'seen' => $userdata->seen
                ];
            } else if ($userdata->blockid === SITE_NOTIFICATION) {
                $sitedata[] = (object)[
                    'title' => $userdata->title,
                    'message' => $userdata->message,
                    'blockid' => $userdata->blockid,
                    'deleted' => transform::yesno($userdata->deleted),
                    'deleted_by' => $userdata->deleted_by,
                    'created_by' => $userdata->created_by,
                    'user_id' => $userdata->user_id,
                    'dismissed' => transform::yesno($userdata->dismissed),
                    'seen' => $userdata->seen
                ];
            }
        }

        // Export depending on context.
        if (isset($blockdata) && !empty($blockdata)) {
            $data = (object)[
                'advnotifications' => $blockdata,
            ];

            // Using user context for better export presentation.
            writer::with_context(\context_user::instance($userid))->export_data([
                get_string('pluginname', 'block_advnotifications')
            ], $data);
        }
        if (isset($sitedata) && !empty($sitedata)) {
            $data = (object)[
                'advnotifications' => $sitedata,
            ];
            writer::with_context(\context_system::instance())->export_data([
                get_string('pluginname', 'block_advnotifications')
            ], $data);
        }

        mtrace("\tDone 'Exporting user data...'");
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        mtrace("\tDeleting data for all users in context...");

        global $DB;

        // Check if approved context.
        if (!($context instanceof context_block || $context instanceof context_system)) {
            return;
        }

        // Handle system context first.
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $delrecords = $DB->get_records('block_advnotifications',
                array('blockid' => SITE_NOTIFICATION),
                null,
                'id');

            foreach ($delrecords as $delrecord) {
                static::adv_delete_record_data($delrecord->id);
            }
        } else if ($context->contextlevel == CONTEXT_BLOCK) { // Handle block context next.
            // Get id of notification data to delete based on provided context instance id.
            // And block instance id is saved in DB already, so just check against that.
            $delrecords = $DB->get_records('block_advnotifications',
                array('blockid' => $context->instanceid),
                null,
                'id');

            foreach ($delrecords as $delrecord) {
                static::adv_delete_record_data($delrecord->id);
            }
        }

        mtrace("\tDone 'Deleting data for all users in context...'");
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        mtrace("\tDeleting data for user...");

        // Get user id.
        $userid = $contextlist->get_user()->id;

        // Delete their data.
        static::adv_delete_user_data($userid);

        mtrace("\tDone 'Deleting data for user...'");
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        mtrace("\tDeleting data for users...");

        // For each user, delete the user data.
        foreach ($userlist->get_userids() as $userid) {
            static::adv_delete_user_data($userid);
        }

        mtrace("\tDone 'Deleting data for users...'");
    }

    /**
     * Delete user data generated/managed by the block - based on user passed.
     *
     * @param   int $userid The userid of the user which data need to be deleted.
     */
    public static function adv_delete_user_data($userid) {
        global $DB;
        // We won't delete notification due to user data being deleted - just 'clear' user id.

        // If user created notification.
        $DB->set_field('block_advnotifications', 'created_by', -1, array('created_by' => $userid));

        // If user deleted notification.
        $DB->set_field('block_advnotifications', 'deleted_by', -1, array('deleted_by' => $userid));

        // If user viewed/dismsissed notification.
        $DB->delete_records('block_advnotificationsdissed', array('user_id' => $userid));
    }

    /**
     * Delete user data generated/managed by the block - based on record passed.
     *
     * @param   int $recordid The recordid of the notification which contains user data to be deleted.
     */
    public static function adv_delete_record_data($recordid) {
        global $DB;
        // We won't delete notification due to user data being deleted - just 'clear' user id.

        // If user created notification.
        $DB->set_field('block_advnotifications', 'created_by', -1, array('id' => $recordid));

        // If user deleted notification.
        $DB->set_field('block_advnotifications', 'deleted_by', -1, array('id' => $recordid));

        // If user viewed/dismsissed notification.
        $DB->delete_records('block_advnotificationsdissed', array('not_id' => $recordid));
    }
}

