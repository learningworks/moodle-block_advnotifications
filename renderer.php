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
 * Advanced Notifications renderer - what gets displayed
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

defined('MOODLE_INTERNAL') || die;

// Load in Moodle config.
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_login();

/**
 * Renders notifications.
 *
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_advnotifications_renderer extends plugin_renderer_base
{
    /**
     * Renders notification on page.
     *
     * @param int $instanceid - block instance id.
     * @return string - returns HTML to render notification.
     */
    public function render_notification($instanceid) {
        global $DB, $USER, $CFG;

        // CONDITIONS.
        // Initialise/Declare.
        $conditions = array();

        // No deleted notifications.
        $conditions['deleted'] = 0;

        // No disabled notifications.
        $conditions['enabled'] = 1;

        // Get notifications with conditions from above.
        $allnotifications = $DB->get_records('block_advnotifications', $conditions);

        $html = '';

        foreach ($allnotifications as $notification) {

            // Keep track of number of times the user has seen the notification.
            // Check if a record of the user exists in the dismissed/seen table.

            // DO NOT CHANGE THIS IF YOU DO A RENDER OVERRIDE - START to END.
            // START.
            $userseen = $DB->get_record('block_advnotificationsdissed',
                                        array('user_id' => $USER->id, 'not_id' => $notification->id)
            );

            // Get notification settings to determine whether to render it or not.
            $render = false;

            // Check if in date-range.
            if ($notification->date_from === $notification->date_to) {
                $render = true;
            } else if ($notification->date_from < time() && $notification->date_to > time()) {
                $render = true;
            }

            // Don't render if user has seen it more (or equal) to the times specified.
            if ($userseen !== false) {
                if ($userseen->seen >= $notification->times && $notification->times != 0) {
                    $render = false;
                } else if ($userseen->dismissed > 0) {
                    $render = false;
                }
            }

            // Don't render if notification isn't a global notification and the instanceid's/blockid's don't match.
            if ($notification->blockid != $instanceid && $notification->global == 0) {
                $render = false;
            }

            if ($render) {
                // Update how many times the user has seen the notification.
                if ($userseen === false) {
                    $seenrecord = new stdClass();
                    $seenrecord->user_id = $USER->id;
                    $seenrecord->not_id = $notification->id;
                    $seenrecord->dismissed = 0;
                    $seenrecord->seen = 1;

                    $DB->insert_record('block_advnotificationsdissed', $seenrecord);
                } else {
                    $upseenrecord = new stdClass();
                    $upseenrecord->id = $userseen->id;
                    $upseenrecord->seen = $userseen->seen + 1;

                    $DB->update_record('block_advnotificationsdissed', $upseenrecord);
                }

                // END (close if).

                // Get type to know which (bootstrap) class to apply.
                $alerttype = '';
                $aicon = '';

                // Allows for custom styling and serves as a basic filter if anything unwanted was somehow submitted.
                if (!empty($notification)) {
                    if ($notification->type == "info") {
                        $alerttype = 'info';
                        $aicon = 'info';
                    } else if ($notification->type == "success") {
                        $alerttype = 'success';
                        $aicon = 'success';
                    } else if ($notification->type == "warning") {
                        $alerttype = 'warning';
                        $aicon = 'warning';
                    } else if ($notification->type == "danger") {
                        $alerttype = 'danger';
                        $aicon = 'danger';
                    } else if ($notification->type == "announcement") {
                        $alerttype = 'info announcement';
                        $aicon = 'info';
                    } else {
                        $alerttype = 'info';
                        $aicon = 'info';
                    }
                } else {
                    $alerttype = 'info';
                    $aicon = 'info';
                }

                // Extra classes to add to the notification wrapper - at least having the type of alert.
                $extraclasses = ' ' . $alerttype;
                if ($notification->dismissible == 1) {
                    $extraclasses .= ' dismissible';
                }
                if ($notification->times > 0) {
                    $extraclasses .= ' limitedtimes';
                }
                if ($notification->aicon == 1) {
                    $extraclasses .= ' aicon';
                }

                // Open notification block.
                $html .= '<div class="notification-block-wrapper' . $extraclasses . '" data-dismiss="' . $notification->id . '">
                            <div class="alert alert-' . $alerttype . '">';

                if (!empty($notification->aicon) && $notification->aicon == 1) {
                    $html .= '<img class="notification_aicon" src="' .
                                $CFG->wwwroot . '/blocks/advnotifications/pix/' . $aicon . '.png"/>';
                }
                if (!empty($notification->title)) {
                    $html .= '<strong>' . $notification->title . '</strong> ';
                }
                if (!empty($notification->message)) {
                    $html .= $notification->message;
                }

                // If dismissible, add close button.
                if ($notification->dismissible == 1) {
                    $html .= '<div class="notification-block-close"><strong>&times;</strong></div>';
                }

                // Close notification block.
                $html .= '    </div>
                          </div>';
            }
        }

        return $html;
    }

    /**
     * Render interface to add a notification.
     *
     * @param array $params - passes information such whether notification is new or the block's instance id.
     * @return string - returns HTML to render (add notification form HTML).
     */
    public function add_notification($params) {
        global $CFG;

        $html = '';

        // New Notification Form.
        $html .= '<div id="add_notification_wrapper_id" class="add_notification_wrapper">
                    <div class="add_notification_header"><h2>' .
                        get_string('advnotifications_add_heading', 'block_advnotifications') .
                        '</h2>
                    </div>
                    <div class="add_notification_form_wrapper">
                        <form id="add_notification_form" action="' . $CFG->wwwroot .
                            '/blocks/advnotifications/pages/process.php" method="POST">';

        // Form inputs.
        $html .= '          <input type="checkbox" id="add_notification_enabled" name="enabled"/>
                            <label for="add_notification_enabled">' .
                                get_string('advnotifications_enabled', 'block_advnotifications') .
                            '</label><br>' .
                            ((array_key_exists('blockid', $params)) ? '
                            <input type="checkbox" id="add_notification_global" name="global"/>
                            <label for="add_notification_global">' .
                                    get_string('advnotifications_global', 'block_advnotifications') .
                                '</label><br>
                            <input type="hidden" id="add_notification_blockid" name="blockid" value="' . $params['blockid'] .
                                '"/>' : '
                            <strong><em>' . get_string('add_notification_global_notice', 'block_advnotifications') . '</em></strong>
                            <input type="hidden" id="add_notification_global" name="global" value="1"/><br>') .
                            '<input type="text" id="add_notification_title" name="title" placeholder="' .
                                get_string('advnotifications_title', 'block_advnotifications') . '"/><br>
                            <input type="text" id="add_notification_message" name="message" placeholder="' .
                                get_string('advnotifications_message', 'block_advnotifications') . '"/><br>
                            <select id="add_notification_type" name="type" required>
                                <option selected disabled>' .
                                    get_string('advnotifications_type', 'block_advnotifications') .
                                '</option>
                                <option value="info">' .
                                    get_string('advnotifications_add_option_info', 'block_advnotifications') .
                                '</option>
                                <option value="success">' .
                                    get_string('advnotifications_add_option_success', 'block_advnotifications') .
                                '</option>
                                <option value="warning">' .
                                    get_string('advnotifications_add_option_warning', 'block_advnotifications') .
                                '</option>
                                <option value="danger">' .
                                    get_string('advnotifications_add_option_danger', 'block_advnotifications') .
                                '</option>
                                <option value="announcement">' .
                                    get_string('advnotifications_add_option_announcement', 'block_advnotifications') .
                                '</option>
                            </select><strong class="required">*</strong><br>
                            <select id="add_notification_times" name="times" required>
                                <option selected disabled>' .
                                    get_string('advnotifications_times', 'block_advnotifications') . '</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select><strong class="required">*</strong><label for="add_notification_times">' .
                                get_string('advnotifications_times_label', 'block_advnotifications') . '</label><br>
                            <input type="checkbox" id="add_notification_aicon" name="aicon"/><label for="add_notification_aicon">' .
                                get_string('advnotifications_aicon', 'block_advnotifications') . '</label><br>
                            <input type="checkbox" id="add_notification_dismissible" name="dismissible"/>
                            <label for="add_notification_dismissible">' .
                                get_string('advnotifications_dismissible', 'block_advnotifications') . '</label><br>
                            <label for="add_notification_date_from">' .
                                get_string('advnotifications_date_from', 'block_advnotifications') . '</label>
                            <input type="date" id="add_notification_date_from" name="date_from" placeholder="dd/mm/yyyy"/>
                            &nbsp;&nbsp;&nbsp;&nbsp;<label for="add_notification_to">' .
                                get_string('advnotifications_date_to', 'block_advnotifications') . '</label>
                            <input type="date" id="add_notification_date_to" name="date_to" placeholder="dd/mm/yyyy"/>
                            <label>' . get_string('advnotifications_date_info', 'block_advnotifications') . '</label><br>
                            <input type="hidden" id="add_notification_sesskey" name="sesskey" value="' . sesskey() . '"/>
                            <input type="hidden" id="add_notification_purpose" name="purpose" value="add"/>
                            <input type="submit" id="add_notification_save" class="btn" role="button" name="save" value="' .
                                get_string('advnotifications_save', 'block_advnotifications') . '"/>
                            &nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $CFG->wwwroot . '/blocks/advnotifications/pages/notifications.php"
                                    id="add_notification_cancel" class="btn" name="cancel">' .
                                get_string('advnotifications_cancel', 'block_advnotifications') . '</a><br>
                            <div id="add_notification_status">
                                <div class="signal"></div>
                                <div class="saving">' .
                                    get_string('advnotifications_add_saving', 'block_advnotifications') .
                                '</div>
                                <div class="done">' .
                                    get_string('advnotifications_add_done', 'block_advnotifications') .
                                '</div>
                            </div>';

        // Close Form.
        $html .= '      </form>
                    </div>
                </div>';

        return $html;
    }
}