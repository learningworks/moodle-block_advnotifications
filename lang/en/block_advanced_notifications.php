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

$string['pluginname'] = 'Advanced Notifications';

// Capabilities.
$string['advanced_notifications:addinstance'] = 'Add a new Advanced Notifications block';
$string['advanced_notifications:myaddinstance'] = 'Add a new Advanced Notifications block to the My Moodle page';
$string['advanced_notifications:managenotifications'] = 'Manage notifications and the relative settings';

// Block Configuration.
$string['advanced_notifications'] = 'Advanced Notifications';
$string['advanced_notifications_enable'] = 'Enable:';
$string['advanced_notifications_auto_delete'] = 'Auto-Delete:';
$string['advanced_notifications_class'] = 'Block Class:';

// Notifications Table Column Names & Table Related Lang Strings.
$string['advanced_notifications_field_id'] = 'ID';
$string['advanced_notifications_field_title'] = 'Title';
$string['advanced_notifications_field_type'] = 'Type';
$string['advanced_notifications_field_enabled'] = 'Enabled';
$string['advanced_notifications_field_global'] = 'Global';
$string['advanced_notifications_field_icon'] = 'Icon';
$string['advanced_notifications_field_dismissible'] = 'Dismissible';
$string['advanced_notifications_field_times'] = 'View Times';
$string['advanced_notifications_field_date_from'] = 'From';
$string['advanced_notifications_field_date_to'] = 'To';
$string['advanced_notifications_field_actions'] = 'Actions';
$string['advanced_notifications_edit_label'] = 'Edit';
$string['advanced_notifications_delete_label'] = 'Delete';
$string['advanced_notifications_restore_label'] = 'Restore';
$string['advanced_notifications_table_empty'] = 'No notifications to show!';
$string['advanced_notifications_cell_yes'] = 'Yes';
$string['advanced_notifications_cell_no'] = 'No';

$string['advanced_notifications_restore_table_warning'] = '<strong>Warning!</strong> Deleting notifications from this table will permanently delete it from the database. We recommend using the auto-delete features of the plugin...';

// Manage Advanced Notification Lang Strings.
$string['advanced_notifications_table_title'] = 'Manage Notifications';
$string['advanced_notifications_table_heading'] = 'Advanced Notifications';

$string['advanced_notifications_restore_table_title'] = 'Restore Notifications';
$string['advanced_notifications_restore_table_heading'] = 'Advanced Notifications Restore';

// New Notification Lang Strings.
$string['advanced_notifications_enable'] = 'Enable/Disable?';
$string['advanced_notifications_global'] = 'Global Notification?';
$string['advanced_notifications_title'] = 'Title';
$string['advanced_notifications_message'] = 'Message';
$string['advanced_notifications_type'] = 'Type';
$string['advanced_notifications_times'] = '# of times';
$string['advanced_notifications_times_label'] = 'Number of times to display the notification to the user (0 = forever)';
$string['advanced_notifications_icon'] = 'Display Icon?';
$string['advanced_notifications_dismissible'] = 'Dismissible?';
$string['advanced_notifications_date_from'] = 'From:';
$string['advanced_notifications_date_to'] = 'To:';
$string['advanced_notifications_save'] = 'Save';
$string['advanced_notifications_cancel'] = 'Cancel';

// Renderer.
$string['advanced_notifications_add_heading'] = 'New Notification';

// Admin Settings.
$string['setting/navigation'] = 'Navigation:';
$string['setting/navigation_desc'] = '<div id="advanced_notifications_manage">{$a->manage}&nbsp;&nbsp;&nbsp;{$a->restore}</div>';

$string['setting/settings'] = 'Settings:';

$string['setting/enable'] = 'Enable:';
$string['setting/enable_desc'] = 'Toggles whether all notifications are enabled/disabled';
$string['setting/enable_default'] = '';

$string['setting/auto_perma_delete'] = 'Auto Permanent Delete:';
$string['setting/auto_perma_delete_desc'] = 'Toggles whether notifications that have been deleted for more than 30 days are automatically permanently deleted from the database.<br>(Helps with housekeeping/management)';
$string['setting/auto_perma_delete_default'] = '';

$string['setting/auto_delete'] = 'Auto Delete:';
$string['setting/auto_delete_desc'] = 'Toggles whether a notification that go past the set end-date is automatically deleted - but can be restored again.<br>(Helps with housekeeping/management)';
$string['setting/auto_delete_default'] = '';

$string['setting/auto_delete_user_data'] = 'Auto Delete User Data:';
$string['setting/auto_delete_user_data_desc'] = 'Toggles whether user data (such as whether the user has seen/dismissed notifications that don\'t exist anymore, etc) related to advanced notifications is automatically deleted.<br>(Helps with housekeeping/management)';
$string['setting/auto_delete_user_data_default'] = '';

// Navigation Links
$string['advanced_notifications_nav_manage'] = 'Manage';
$string['advanced_notifications_nav_restore'] = 'Restore';
$string['advanced_notifications_nav_settings'] = 'Settings';