<?php
/**
 * Created by LearningWorks Ltd
 * Date: 5/07/16
 * Time: 2:49 PM
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    /**
     * ENABLE TOGGLE
     */
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advanced_notifications/enable',									// NAME
            get_string('setting/enable', 'block_advanced_notifications'), 			            // TITLE
            get_string('setting/enable_desc', 'block_advanced_notifications'),		            // DESCRIPTION
            get_string('setting/enable_default', 'block_advanced_notifications')	            // DEFAULT
        )
    );

    /**
     * AUTO-PERMADELETE OLD DELETED NOTIFICATIONS
     */
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advanced_notifications/auto_perma_delete',									// NAME
            get_string('setting/auto_perma_delete', 'block_advanced_notifications'), 			// TITLE
            get_string('setting/auto_perma_delete_desc', 'block_advanced_notifications'),		// DESCRIPTION
            get_string('setting/auto_perma_delete_default', 'block_advanced_notifications')	// DEFAULT
        )
    );

    /**
     * AUTO-DELETE TOGGLE
     */
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advanced_notifications/auto_delete',									// NAME
            get_string('setting/auto_delete', 'block_advanced_notifications'), 			// TITLE
            get_string('setting/auto_delete_desc', 'block_advanced_notifications'),		// DESCRIPTION
            get_string('setting/auto_delete_default', 'block_advanced_notifications')	// DEFAULT
        )
    );

    /**
     * AUTO-DELETE USER DATA TOGGLE
     */
    $settings->add(
        new admin_setting_configcheckbox(
            'block_advanced_notifications/auto_delete_user_data',									// NAME
            get_string('setting/auto_delete_user_data', 'block_advanced_notifications'), 			// TITLE
            get_string('setting/auto_delete_user_data_desc', 'block_advanced_notifications'),		// DESCRIPTION
            get_string('setting/auto_delete_user_data_default', 'block_advanced_notifications')	// DEFAULT
        )
    );

}