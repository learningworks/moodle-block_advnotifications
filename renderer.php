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
     * @param array $notifications - attributes about notifications to render.
     * @return string - returns HTML to render notification.
     */
    public function render_notification($notifications) {
        $html = '';

        // Render all the appropriate notifications.
        foreach ($notifications as $notification) {
            // Open notification block.
            $html .= '<div class="notification-block-wrapper' . $notification['extraclasses'] .
                '" data-dismiss="' . $notification['notifid'] .
                '"><div class="alert alert-' . $notification['alerttype'] . '">';

            if (!empty($notification['aiconflag']) && $notification['aiconflag'] == 1) {
                $html .= '<img class="notification_aicon" src="' .
                    $this->image_url($notification['aicon'], 'block_advnotifications') . '"/>';
            }
            if (!empty($notification['title'])) {
                $html .= '<strong>' . $notification['title'] . '</strong> ';
            }
            if (!empty($notification['message'])) {
                $html .= $notification['message'];
            }

            // If dismissible, add close button.
            if ($notification['dismissible'] == 1) {
                $html .= '<div class="notification-block-close"><strong>&times;</strong></div>';
            }

            // Close notification block.
            $html .= '</div></div>';
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
                            <textarea id="add_notification_message" name="message" placeholder="' .
                                get_string('advnotifications_message', 'block_advnotifications') . '"></textarea><br>
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
                            <input type="date" id="add_notification_date_from" name="date_from" placeholder="yyyy-mm-dd"/>
                            &nbsp;&nbsp;&nbsp;&nbsp;<label for="add_notification_to">' .
                                get_string('advnotifications_date_to', 'block_advnotifications') . '</label>
                            <input type="date" id="add_notification_date_to" name="date_to" placeholder="yyyy-mm-dd"/>
                            <label>' . get_string('advnotifications_date_info', 'block_advnotifications') . '</label><br>
                            <input type="hidden" id="add_notification_sesskey" name="sesskey" value="' . sesskey() . '"/>
                            <input type="hidden" id="add_notification_purpose" name="purpose" value="add"/>
                            <input type="submit" id="add_notification_save" class="btn btn-primary" role="button" name="save" value="' .
                                get_string('advnotifications_save', 'block_advnotifications') . '"/>
                            &nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $CFG->wwwroot . '/blocks/advnotifications/pages/notifications.php"
                                    id="add_notification_cancel" class="btn btn-secondary" name="cancel">' .
                                get_string('advnotifications_cancel', 'block_advnotifications') . '</a><br>
                            <div id="add_notification_status">
                                <div class="signal"></div>
                                <div class="saving">' .
                                    get_string('advnotifications_add_saving', 'block_advnotifications') .
                                '</div>
                                <div class="done" style="display: none;">' .
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