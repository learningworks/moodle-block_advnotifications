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
 * Advanced Notifications block settings
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;

if ($ADMIN->fulltree) {

    // Used for navigation links to keep track of blockid (if any).
    $blockid = optional_param('blockid', '', PARAM_INT);
    $param = '';

    if (isset($blockid) && $blockid !== '') {
        $param = '?blockid=' . $blockid;
    }

    // SETTINGS' NAVIGATIONAL LINKS HEADING & LINKS.
    $settings->add(
        new admin_setting_heading(
            'block_advnotifications/navigation',                                                            // NAME.
            get_string('setting/navigation', 'block_advnotifications'),                                     // TITLE.
                        get_string('setting/navigation_desc', 'block_advnotifications',
                        array('manage' => '<a class="btn btn-secondary instance" href="' . $CFG->wwwroot .
                            '/blocks/advnotifications/pages/notifications.php' . $param . '">Manage</a>',
                            'restore' => '<a class="btn btn-secondary instance" href="' . $CFG->wwwroot .
                                '/blocks/advnotifications/pages/restore.php' . $param . '">Restore</a>'))   // DESCRIPTION.
        )
    );

    // SETTINGS HEADING.
    $settings->add(
        new admin_setting_heading(
            'block_advnotifications/settings',                                                              // NAME.
            get_string('setting/settings', 'block_advnotifications'),                                       // TITLE.
            null
        )
    );

    // ENABLE TOGGLE.
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advnotifications/enable',                                                                // NAME.
            get_string('setting/enable', 'block_advnotifications'),                                         // TITLE.
            get_string('setting/enable_desc', 'block_advnotifications'),                                    // DESCRIPTION.
            get_string('setting/enable_default', 'block_advnotifications')                                  // DEFAULT.
        )
    );

    // ALLOW HTML TOGGLE.
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advnotifications/html',                                                                  // NAME.
            get_string('setting/html', 'block_advnotifications'),                                           // TITLE.
            get_string('setting/html_desc', 'block_advnotifications'),                                      // DESCRIPTION.
            get_string('setting/html_default', 'block_advnotifications')                                    // DEFAULT.
        )
    );

    // MULTILANG FILTER(S) SUPPORT TOGGLE.
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advnotifications/multilang',                                                             // NAME.
            get_string('setting/multilang', 'block_advnotifications'),                                      // TITLE.
            get_string('setting/multilang_desc', 'block_advnotifications'),                                 // DESCRIPTION.
            get_string('setting/multilang_default', 'block_advnotifications')                               // DEFAULT.
        )
    );

    // AUTO-DELETE TOGGLE.
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advnotifications/auto_delete',                                                           // NAME.
            get_string('setting/auto_delete', 'block_advnotifications'),                                    // TITLE.
            get_string('setting/auto_delete_desc', 'block_advnotifications'),                               // DESCRIPTION.
            get_string('setting/auto_delete_default', 'block_advnotifications')                             // DEFAULT.
        )
    );

    // AUTO-PERMADELETE OLD DELETED NOTIFICATIONS.
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advnotifications/auto_perma_delete',                                                     // NAME.
            get_string('setting/auto_perma_delete', 'block_advnotifications'),                              // TITLE.
            get_string('setting/auto_perma_delete_desc', 'block_advnotifications'),                         // DESCRIPTION.
            get_string('setting/auto_perma_delete_default', 'block_advnotifications')                       // DEFAULT.
        )
    );

    // AUTO-DELETE USER DATA TOGGLE.
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advnotifications/auto_delete_user_data',                                                 // NAME.
            get_string('setting/auto_delete_user_data', 'block_advnotifications'),                          // TITLE.
            get_string('setting/auto_delete_user_data_desc', 'block_advnotifications'),                     // DESCRIPTION.
            get_string('setting/auto_delete_user_data_default', 'block_advnotifications')                   // DEFAULT.
        )
    );
}