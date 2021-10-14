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
 * All the configurable strings used throughout the plugin.
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

$string['pluginname'] = 'Advanced notifications';

// Capabilities.
$string['advnotifications:addinstance'] = 'Add a new Advanced notifications block';
$string['advnotifications:myaddinstance'] = 'Add a new Advanced notifications block to the my Moodle page';
$string['advnotifications:managenotifications'] = 'Manage notifications and the relative settings';
$string['advnotifications:manageownnotifications'] = 'Manage own notifications and the relative settings';

// Block Configuration.
$string['advnotifications'] = 'Advanced notifications';
$string['advnotifications_class'] = 'Block class:';

// Notifications Table Column Names & Table Related Lang Strings.
$string['advnotifications_field_id'] = 'ID';
$string['advnotifications_field_title'] = 'Title';
$string['advnotifications_field_type'] = 'Type';
$string['advnotifications_field_enabled'] = 'Enabled';
$string['advnotifications_field_global'] = 'Global';
$string['advnotifications_field_aicon'] = 'Icon';
$string['advnotifications_field_dismissible'] = 'Dismissible';
$string['advnotifications_field_sendnotifications'] = 'Send notifications';
$string['advnotifications_field_times'] = 'View Times';
$string['advnotifications_field_date_from'] = 'From';
$string['advnotifications_field_date_to'] = 'To';
$string['advnotifications_field_actions'] = 'Actions';
$string['advnotifications_edit_label'] = 'Edit';
$string['advnotifications_delete_label'] = 'Delete';
$string['advnotifications_restore_label'] = 'Restore';
$string['advnotifications_table_empty'] = 'No notifications to show!';
$string['advnotifications_cell_yes'] = 'Yes';
$string['advnotifications_cell_no'] = 'No';

$string['advnotifications_restore_table_warning'] = '<strong>Warning!</strong> Deleting notifications from this table will permanently delete it from the database. It is recommended to use the auto-delete features of the plugin...';

// Manage Advanced Notification Lang Strings.
$string['advnotifications_table_title'] = 'Manage notifications';
$string['advnotifications_table_title_short'] = 'Manage';
$string['advnotifications_table_heading'] = 'Advanced notifications';

$string['advnotifications_restore_table_title'] = 'Restore notifications';
$string['advnotifications_restore_table_title_short'] = 'Restore notifications';
$string['advnotifications_restore_table_heading'] = 'Advanced notifications restore';

// New Notification Lang Strings.
$string['advnotifications_enabled'] = 'Enabled?';
$string['advnotifications_global'] = 'Global notification?';
$string['add_notification_global_notice'] = 'This notification will be displayed globally/site-wide!';
$string['add_notif_local_notice'] = 'This notification will <em>only</em> be displayed on the page you\'re managing this block from!';
$string['advnotifications_title'] = 'Title';
$string['advnotifications_message'] = 'Message';
$string['advnotifications_type'] = 'Type';
$string['advnotifications_times'] = '# of times';
$string['advnotifications_times_label'] = 'Number of times to display the notification to a user (0 = forever)';
$string['advnotifications_aicon'] = 'Icon?';
$string['advnotifications_dismissible'] = 'Dismissible?';
$string['advnotifications_date_from'] = 'From:';
$string['advnotifications_date_to'] = 'To:';
$string['advnotifications_date_info'] = 'Same date = forever';
$string['advnotifications_save'] = 'Save';
$string['advnotifications_update'] = 'Update';
$string['advnotifications_cancel'] = 'Cancel';
$string['advnotifications_req'] = 'Required...';
$string['advnotifications_preview'] = 'Preview';
$string['advnotifications_sendnotifications'] = 'Send notitications via site notification system (usually will appear on the bell icon on top)';

// Renderer.
$string['advnotifications_add_heading'] = 'New notification';
$string['advnotifications_add_option_info'] = 'Information';
$string['advnotifications_add_option_success'] = 'Success';
$string['advnotifications_add_option_warning'] = 'Warning';
$string['advnotifications_add_option_danger'] = 'Danger';
$string['advnotifications_add_option_announcement'] = 'Announcement';
$string['advnotifications_add_saving'] = 'Saving...';
$string['advnotifications_add_done'] = 'Done!';

// Admin Settings.
$string['setting/navigation'] = 'Navigation:';
$string['setting/navigation_desc'] = '{$a->left}{$a->right}';

$string['setting/settings'] = 'Settings:';

$string['setting/enable'] = 'Enable:';
$string['setting/enable_desc'] = 'Toggles whether all notifications are enabled/disabled<hr>';
$string['setting/enable_default'] = '';

$string['setting/html'] = 'Allow HTML:';
$string['setting/html_desc'] = 'Toggles whether basic HTML is allowed in notifications\' titles/messages';
$string['setting/html_default'] = '';

$string['setting/multilang'] = 'Multi-lang/Filter support:';
$string['setting/multilang_desc'] = 'Toggles whether the multilang filter (and others) are supported in notifications\' titles/messages.<br>Note - This is for more advanced users & HTML needs to be enabled (above).<hr>';
$string['setting/multilang_default'] = '';

$string['setting/dateformat'] = 'Date format:';
$string['setting/dateformat_desc'] = 'Dates will be shown in the chosen format.<hr>';

$string['setting/auto_delete'] = 'Auto delete:';
$string['setting/auto_delete_desc'] = 'Toggles whether a notification that go past the set end-date is automatically deleted - but can be restored again.<br>(Helps with housekeeping/management)';
$string['setting/auto_delete_default'] = '';

$string['setting/auto_perma_delete'] = 'Auto permanent delete:';
$string['setting/auto_perma_delete_desc'] = 'Toggles whether notifications that have been deleted for more than 30 days are automatically permanently deleted from the database.<br>(Helps with housekeeping/management)';
$string['setting/auto_perma_delete_default'] = '';

$string['setting/auto_delete_user_data'] = 'Auto delete user data:';
$string['setting/auto_delete_user_data_desc'] = 'Toggles whether user data (such as whether the user has seen/dismissed notifications that don\'t exist anymore, etc) related to advanced notifications is automatically deleted.<br>(Helps with housekeeping/management)<hr>';
$string['setting/auto_delete_user_data_default'] = '';

// Navigation Links.
$string['advnotifications_nav_heading'] = 'Notifications:';
$string['advnotifications_nav_manage'] = 'Manage';
$string['advnotifications_nav_restore'] = 'Restore';
$string['advnotifications_nav_settings'] = 'Settings';

// Error Messages.
$string['advnotifications_err_forbidden'] = 'Forbidden, please login again...';
$string['advnotifications_err_nojsedit'] = 'Editing only supported with JavaScript enabled. Re-create the desired notification or enable JavaScript and try again.';
$string['advnotifications_err_req'] = 'The following fields are required: {$a}';
$string['advnotifications_err_nocapability'] = 'You don\'t have permission to do that...';

// Cron Messages.
$string['advnotifications_task_name'] = 'Advanced notifications';
$string['advnotifications_cron_heading'] = 'Cleaning advanced notifications';
$string['advnotifications_cron_auto_perma_delete'] = 'Permanently delete notifications that\'s had the deleted flag for more than 30 days...';
$string['advnotifications_cron_auto_delete'] = 'Add deleted flag to notifications that\'s passed their end-date...';
$string['advnotifications_cron_auto_delete_udata'] = 'Remove user records that relates to notifications that don\'t exist anymore...';

// Misc.
$string['advnotifications_join'] = ' & ';
$string['adduserfieldfilter'] = 'Add another user field filter';
$string['audiencesaved'] = 'Audience save successfully.';
$string['editing_audiences'] = 'Editing audiences';
$string['event_notification_created'] = 'Advanced notification created';
$string['event_notification_deleted'] = 'Advanced notification deleted';
$string['event_notification_updated'] = 'Advanced notification updated';
$string['filter_userfield'] = 'User field';
$string['operator_beginwith'] = 'Begin with';
$string['operator_contains'] = 'Contains';
$string['operator_equals'] = 'Equals';

$string['messageprovider:sendadvnotifications'] = 'Send advanced notifications to users';

// Privacy API.
$string['privacy:metadata:block_advnotifications'] = 'Information about notifications the user has been exposed to and recorded interactions.';
$string['privacy:metadata:block_advnotifications:title'] = 'The title of the notification.';
$string['privacy:metadata:block_advnotifications:message'] = 'The body/message of the notification.';
$string['privacy:metadata:block_advnotifications:blockid'] = 'The ID of the block from which the notification was created (if any).';
$string['privacy:metadata:block_advnotifications:deleted'] = 'Whether the notification has been deleted from the site (1 = deleted).';
$string['privacy:metadata:block_advnotifications:deleted_by'] = 'The ID of the user that deleted the notification (if any).';
$string['privacy:metadata:block_advnotifications:created_by'] = 'The ID of the user that created the notifications (if any).';
$string['privacy:metadata:block_advnotificationsdissed'] = 'Information about the user (as consumer)/notification relationship.';
$string['privacy:metadata:block_advnotificationsdissed:user_id'] = 'The ID of the user that has seen/dismissed the notification.';
$string['privacy:metadata:block_advnotificationsdissed:not_id'] = 'The associated notification ID.';
$string['privacy:metadata:block_advnotificationsdissed:dismissed'] = 'Flag of whether the notification has been dismissed by the user or not (1 = dismissed).';
$string['privacy:metadata:block_advnotificationsdissed:seen'] = 'A count of how many times the user has seen/been shown the notification.';
