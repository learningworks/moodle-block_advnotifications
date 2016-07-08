<?php
/**
 * Created by LearningWorks Ltd
 * Date: 4/07/16
 * Time: 4:44 PM
 */


/**
 * Advanced Notifications Renderer
 *
 * @package    block_advanced_notifications
 * @copyright  LearningWorks Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

// Load in Moodle config
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

class block_advanced_notifications_renderer extends plugin_renderer_base
{

    function render_notification()
    {
        global $DB, $USER;

        // CONDITIONS
        // Initialise/Declare
        $conditions = array();

        // No deleted notifications
        $conditions['deleted'] = 0;

        // No disabled notifications
        $conditions['enabled'] = 1;

        // Get notifications with conditions from above
        $allnotifications = $DB->get_records('block_advanced_notifications', $conditions);

        $html = '';



        foreach ($allnotifications as $notification) {

            // Keep track of number of times the user has seen the notification
            // Check if a record of the user exists in the dismissed/seen table
            // DO NOT CHANGE THIS IF YOU DO A RENDER OVERRIDE - BETWEEN THE ASTERISKS
            // ***********************************************************************
            $userseen = $DB->get_record('block_advanced_notifications_dismissed', array('user_id' => $USER->id, 'not_id' => $notification->id));



            // Get notification settings to determine whether to render it or not
            $render = false;

            // Check if in date-range
            if ($notification->date_from === $notification->date_to) {
                $render = true;
            }
            elseif ($notification->date_from < time() && $notification->date_to > time())
            {
                $render = true;
            }

            // Don't render if user has seen it more (or equal) to the times specified
            if ($userseen !== false)
            {
                if ($userseen->seen >= $notification->times && $notification->times != 0)
                {
                    $render = false;
                }
                elseif ($userseen->dismissed > 0)
                {
                    $render = false;
                }
            }

            if ($render) {

                // Update how many times the user has seen the notification
                if ($userseen === false)
                {
                    $seenrecord = new stdClass();
                    $seenrecord->user_id = $USER->id;
                    $seenrecord->not_id = $notification->id;
                    $seenrecord->dismissed = 0;
                    $seenrecord->seen = 1;

                    $DB->insert_record('block_advanced_notifications_dismissed', $seenrecord);
                }
                else
                {
                    $upseenrecord = new stdClass();
                    $upseenrecord->id = $userseen->id;
                    $upseenrecord->seen = $userseen->seen + 1;

//                    var_dump('seen ' . time());

                    $DB->update_record('block_advanced_notifications_dismissed', $upseenrecord);
                }

            // ***********************************************************************

                // Get type to know which bootstrap class to apply
                $alerttype = '';

                // Allows for custom styling and a basic filter (by specifying specific strings) if anything unwanted was somehow submitted
                if (!empty($notification)) {
                    if ($notification->type == "info") {
                        $alerttype = 'info';
                    } elseif ($notification->type == "success") {
                        $alerttype = 'success';
                    } elseif ($notification->type == "warning") {
                        $alerttype = 'warning';
                    } elseif ($notification->type == "danger") {
                        $alerttype = 'danger';
                    } elseif ($notification->type == "announcement") {
                        $alerttype = 'info announcement';
                    }
                    else
                    {
                        $alerttype = 'info';
                    }
                } else {
                    $alerttype = 'info';
                }

                // Extra classes to add to the notification wrapper - at least having the type of alert
                $extraclasses = ' ' . $alerttype;
                if ($notification->dismissible == 1)
                {
                    $extraclasses .= ' dismissible';
                }
                if ($notification->times > 0)
                {
                    $extraclasses .= ' limitedtimes';
                }
                if ($notification->icon == 1)
                {
                    $extraclasses .= ' icon';
                }

                // Open notification block
                $html .= '<div class="notification-block-wrapper' . $extraclasses . '" data-dismiss="' . $notification->id . '">
                            <div class="alert alert-' . $alerttype . '">';

                if (!empty($notification->title)) {
                    $html .= '<strong>' . $notification->title . '</strong> ';
                }
                if (!empty($notification->message)) {
                    $html .= $notification->message;
                }

                // If dismissible, add close button
                if ($notification->dismissible == 1) {
                    $html .= '<div class="notification-block-close"><strong>&times;</strong></div>';
                }

                // Close notification block
                $html .= '    </div>
                          </div>';
            }
        }

        return $html;
    }

    function add_notification($params)
    {
        global $CFG;

        $extraclasses = '';
        // If user wants to create a new notification
        if ($params['new'] === 1)
        {
            $extraclasses .= ' new';
        }

        $html = '';

        // New Notification Form
        $html .= '<div id="add_notification_wrapper_id" class="add_notification_wrapper' . $extraclasses . '">
                    <div class="add_notification_header"><h2>' . get_string('advanced_notifications_add_heading', 'block_advanced_notifications') . '</h2></div>
                    <div class="add_notification_form_wrapper">
                        <form id="add_notification_form" action="' . $CFG->wwwroot . '/blocks/advanced_notifications/pages/process.php" method="POST">';

        // Form inputs
        $html .= '          <input type="checkbox" id="add_notification_enable" name="enable"/><label for="add_notification_enable">' . get_string('advanced_notifications_enable', 'block_advanced_notifications') . '</label><br>
                            <input type="text" id="add_notification_title" name="title" placeholder="' . get_string('advanced_notifications_title', 'block_advanced_notifications') . '"/><br>
                            <input type="text" id="add_notification_message" name="message" placeholder="' . get_string('advanced_notifications_message', 'block_advanced_notifications') . '"/><br>
                            <select id="add_notification_type" name="type" required>
                                <option selected disabled>' . get_string('advanced_notifications_type', 'block_advanced_notifications') . '</option>
                                <option value="info">Information</option>
                                <option value="success">Success</option>
                                <option value="warning">Warning</option>
                                <option value="danger">Danger</option>
                                <option value="announcement">Announcement</option>
                            </select><br>
                            <select id="add_notification_times" name="times" required>
                                <option selected disabled>' . get_string('advanced_notifications_times', 'block_advanced_notifications') . '</option>
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
                            </select><label for="add_notification_times_label">' . get_string('advanced_notifications_times_label', 'block_advanced_notifications') . '</label><br>
                            <input type="checkbox" id="add_notification_icon" name="icon"/><label for="add_notification_icon">' . get_string('advanced_notifications_icon', 'block_advanced_notifications') . '</label><br>
                            <input type="checkbox" id="add_notification_dismissible" name="dismissible"/><label for="add_notification_dismissible">' . get_string('advanced_notifications_dismissible', 'block_advanced_notifications') . '</label><br>
                            <label for="add_notification_date_from">' . get_string('advanced_notifications_date_from', 'block_advanced_notifications') . '</label><input type="date" id="add_notification_date_from" name="date_from" placeholder="dd/mm/yyyy"/>&nbsp;&nbsp;&nbsp;&nbsp;<label for="add_notification_to">' . get_string('advanced_notifications_date_to', 'block_advanced_notifications') . '</label><input type="date" id="add_notification_date_to" name="date_to" placeholder="dd/mm/yyyy"/><br>
                            <input type="hidden" id="add_notification_sesskey" name="sesskey" value="' . sesskey() . '"/>
                            <input type="submit" id="add_notification_save" name="save" value="' . get_string('advanced_notifications_save', 'block_advanced_notifications') . '"/>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" id="add_notification_cancel" class="btn" name="cancel">' . get_string('advanced_notifications_cancel', 'block_advanced_notifications') . '</a><br>
                            <div id="add_notification_status"><div class="signal"></div><div class="saving">Saving...</div><div class="done">Done</div></div>';

        // Close Form
        $html .= '      </form>
                    </div>
                </div>';

        return $html;
    }
}