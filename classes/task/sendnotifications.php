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
 * Ad hoc task to send notifications.
 *
 * @package    block_advnotifications
 * @copyright  2021 Daniel Neis Araujo <daniel@adapta.online>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_advnotifications\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Ad hoc task to send notifications class.
 *
 * @package    block_advnotifications
 * @copyright  2021 Daniel Neis Araujo <daniel@adapta.online>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sendnotifications extends \core\task\adhoc_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendnotificationstask', 'block-advnotifications');
    }

    public function execute() {
        global $DB;

        if (!get_config('block_advnotifications', 'enable')) {
            return;
        }

        $from = \core_user::get_noreply_user();

        $notification = $DB->get_record('block_advnotifications', ['id' => $this->get_custom_data()->notificationid]);

        if (!$notification->enabled) {
            return;
        }

        $users = \block_advnotifications\audience::get_users_for_notification($notification);

        foreach ($users as $u) {
            $eventdata = new \core\message\message();
            $eventdata->component         = 'block_advnotifications';
            $eventdata->name              = 'sendadvnotifications';
            $eventdata->userfrom          = $from;
            $eventdata->userto            = $u->id;
            $eventdata->subject           = $notification->title;
            $eventdata->fullmessage       = format_text($notification->message, FORMAT_MOODLE);
            $eventdata->fullmessageformat = FORMAT_MOODLE;
            $eventdata->fullmessagehtml   = format_text($notification->message, FORMAT_MOODLE);
            $eventdata->smallmessage      = '';
            $eventdata->notification      = true;
            message_send($eventdata);
        }
    }
}
