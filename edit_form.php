<?php

    class block_advanced_notifications_edit_form extends block_edit_form {

        protected function specific_definition($mform) {

            global $CFG;

            // Section header title according to language file.
            $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

            $mform->addElement('html', '<h3>Notifications:</h3>
                                        <a class="btn" href="' . $CFG->wwwroot . '/blocks/advanced_notifications/pages/notifications.php">Manage</a>
                                        &nbsp;&nbsp;<a class="btn" href="' . $CFG->wwwroot . '/blocks/advanced_notifications/pages/notifications.php#add_notification_wrapper_id">New</a><br><br>');

            // Allows a custom class to be added to the block for styling purposes
            $mform->addElement('text', 'config_class', get_string('advanced_notifications_class', 'block_advanced_notifications'));
            $mform->setDefault('config_class', '');
            $mform->setType('config_class', PARAM_TEXT);
        }
    }