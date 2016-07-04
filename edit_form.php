<?php

    class block_advanced_notifications_edit_form extends block_edit_form {

        protected function specific_definition($mform) {

            global $CFG;

            // Section header title according to language file.
            $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

            // Make the notification dismissible/non-dismissible
            $mform->addElement('advcheckbox', 'config_enable', get_string('advanced_notifications_enable', 'block_advanced_notifications'), 'Enable/Disable notification?');
            $mform->setDefault('config_enable', 1);
            $mform->setType('config_enable', PARAM_BOOL);

            // Make the notification dismissible/non-dismissible
            $mform->addElement('advcheckbox', 'config_auto_delete', get_string('advanced_notifications_auto_delete', 'block_advanced_notifications'), 'Auto-delete notifications after the end date/time specified?');
            $mform->setDefault('config_auto_delete', 1);
            $mform->setType('config_auto_delete', PARAM_BOOL);

            // Allows a custom class to be added to the block for styling purposes
            $mform->addElement('text', 'config_class', get_string('advanced_notifications_class', 'block_advanced_notifications'));
            $mform->setDefault('config_class', '');
            $mform->setType('config_class', PARAM_TEXT);

            $mform->addElement('html', '&nbsp;&nbsp;&nbsp;&nbsp;<strong>Notifications:</strong><br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $CFG->wwwroot . '/blocks/advanced_notifications/pages/notifications.php">Manage</a>
                                        &nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $CFG->wwwroot . '/blocks/advanced_notifications/pages/notifications.php?new=1">New</a>');

            /*// The notification's Title string variable with a default value.
            $mform->addElement('text', 'config_titletext', get_string('advanced_notifications_title', 'block_advanced_notifications'));
            $mform->setDefault('config_titletext', '');
            $mform->setType('config_titletext', PARAM_TEXT);

            // The notification's message string variable with a default value.
            $mform->addElement('textarea', 'config_messagetextarea', get_string('advanced_notifications_message', 'block_advanced_notifications'));
            $mform->setDefault('config_messagetextarea', '');
            $mform->setType('config_messagetextarea', PARAM_TEXT);

            // Selectable notification types
            $mform->addElement('select', 'config_type', get_string('advanced_notifications_type', 'block_advanced_notifications'), array('information'=>'Information', 'success'=>'Success', 'warning'=>'Warning', 'danger'=>'Danger', 'announcement'=>'Announcement'));
            $mform->setDefault('config_type', 'Information');
            $mform->setType('config_type', PARAM_TEXT);

            // Add a small icon in-front of the notification
            $mform->addElement('advcheckbox', 'config_icon', get_string('advanced_notifications_icon', 'block_advanced_notifications'), 'Add icon to notification?');
            $mform->setDefault('config_icon', 0);
            $mform->setType('config_icon', PARAM_BOOL);

            $mform->addElement('html', '<div class="notificationicon">&nbsp;&nbsp;&nbsp;&nbsp;<strong>Notification Icons:</strong></div>
                                        <div class="bootstrapinformation">&nbsp;&nbsp;&nbsp;&nbsp;Information: <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span></div>
                                        <div class="bootstrapsuccess">&nbsp;&nbsp;&nbsp;&nbsp;Success: <span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span></div>
                                        <div class="bootstrapwarning">&nbsp;&nbsp;&nbsp;&nbsp;Warning: <span class="glyphicon glyphicon-alert" aria-hidden="true"></span></div>
                                        <div class="bootstrapdanger">&nbsp;&nbsp;&nbsp;&nbsp;Danger: <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span></div>
                                        <div class="bootstrapannouncement">&nbsp;&nbsp;&nbsp;&nbsp;Announcement: <span class="glyphicon glyphicon-bullhorn" aria-hidden="true"></span></div>');

            // Make the notification dismissible/non-dismissible
            $mform->addElement('advcheckbox', 'config_dismissible', get_string('advanced_notifications_dismissible', 'block_advanced_notifications'), 'Make the notification dismissible?');
            $mform->setDefault('config_dismissible', 0);
            $mform->setType('config_dismissible', PARAM_BOOL);*/
        }
    }