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
 * Created by LearningWorks Ltd
 * Date: 5/07/16
 * Time: 2:49 PM
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;

if ($ADMIN->fulltree) {

    // SETTINGS' NAVIGATIONAL LINKS HEADING & LINKS.
    $settings->add(
        new admin_setting_heading(
            'block_advanced_notifications/navigation',                                              // NAME.
            get_string('setting/navigation', 'block_advanced_notifications'),                       // TITLE.
            get_string('setting/navigation_desc',
                        'block_advanced_notifications',
                        array('manage' => '<a class="btn instance" href="' . $CFG->wwwroot .
                            '/blocks/advanced_notifications/pages/notifications.php">Manage</a>',
                            'restore' => '<a class="btn instance" href="' . $CFG->wwwroot .
                                '/blocks/advanced_notifications/pages/restore.php">Restore</a>'))   // DESCRIPTION.
        )
    );

    // SETTINGS HEADING.
    $settings->add(
        new admin_setting_heading(
            'block_advanced_notifications/settings',                                                // NAME.
            get_string('setting/settings', 'block_advanced_notifications'),                         // TITLE.
            null
        )
    );

    // ENABLE TOGGLE.
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advanced_notifications/enable',                                                  // NAME.
            get_string('setting/enable', 'block_advanced_notifications'),                           // TITLE.
            get_string('setting/enable_desc', 'block_advanced_notifications'),                      // DESCRIPTION.
            get_string('setting/enable_default', 'block_advanced_notifications')                    // DEFAULT.
        )
    );

    // AUTO-PERMADELETE OLD DELETED NOTIFICATIONS.
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advanced_notifications/auto_perma_delete',                                       // NAME.
            get_string('setting/auto_perma_delete', 'block_advanced_notifications'),                // TITLE.
            get_string('setting/auto_perma_delete_desc', 'block_advanced_notifications'),           // DESCRIPTION.
            get_string('setting/auto_perma_delete_default', 'block_advanced_notifications')         // DEFAULT.
        )
    );

    // AUTO-DELETE TOGGLE.
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advanced_notifications/auto_delete',                                             // NAME.
            get_string('setting/auto_delete', 'block_advanced_notifications'),                      // TITLE.
            get_string('setting/auto_delete_desc', 'block_advanced_notifications'),                 // DESCRIPTION.
            get_string('setting/auto_delete_default', 'block_advanced_notifications')               // DEFAULT.
        )
    );

    // AUTO-DELETE USER DATA TOGGLE.
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advanced_notifications/auto_delete_user_data',                                   // NAME.
            get_string('setting/auto_delete_user_data', 'block_advanced_notifications'),            // TITLE.
            get_string('setting/auto_delete_user_data_desc', 'block_advanced_notifications'),       // DESCRIPTION.
            get_string('setting/auto_delete_user_data_default', 'block_advanced_notifications')     // DEFAULT.
        )
    );

}