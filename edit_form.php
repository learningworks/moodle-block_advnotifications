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

class block_advanced_notifications_edit_form extends block_edit_form {

    protected function specific_definition($mform) {

        global $CFG;

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('html',
                            '<div id="advanced_notifications_manage" class="manage_notifications"><h3>Notifications:</h3>
                                <a class="btn instance" href="' . $CFG->wwwroot .
                                    '/blocks/advanced_notifications/pages/notifications.php">Manage</a>&nbsp;&nbsp;
                                <a class="btn" href="' . $CFG->wwwroot .
                                    '/blocks/advanced_notifications/pages/restore.php">Restore</a>&nbsp;&nbsp;
                                <a class="btn" href="' . $CFG->wwwroot .
                                    '/admin/settings.php?section=blocksettingadvanced_notifications">Settings</a><br><br>
                            </div>'
        );

        // Allows a custom class to be added to the block for styling purposes.
        $mform->addElement('text', 'config_class', get_string('advanced_notifications_class', 'block_advanced_notifications'));
        $mform->setDefault('config_class', '');
        $mform->setType('config_class', PARAM_TEXT);
    }
}