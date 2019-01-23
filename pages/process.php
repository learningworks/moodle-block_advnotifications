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
 * Used to process user actions.
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

// Load in Moodle config.
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

try {
    require_sesskey();
} catch (EXCEPTION $e) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(array("result" => "Failed",
        "Notification" => get_string('advnotifications_err_forbidden', 'block_advnotifications')));
    exit();
}

require_login();

global $USER;

$context = context_system::instance();

header('HTTP/1.0 200 OK');

// TODO - Check if insertions/updates/deletions were successful, and return appropriate message.

// GET PARAMETERS.
// Check if ajax or other type of call.
$calltype = optional_param('call', null, PARAM_TEXT);

// Notification details.
$enabled = optional_param('enabled', null, PARAM_TEXT);
$global = optional_param('global', null, PARAM_TEXT);
$blockinstance = optional_param('blockid', -1, PARAM_INT);
if (get_config('block_advnotifications', 'html')) {
    $title = optional_param('title', null, PARAM_CLEANHTML);
    $message = optional_param('message', null, PARAM_CLEANHTML);
} else {
    $title = optional_param('title', null, PARAM_TEXT);
    $message = optional_param('message', null, PARAM_TEXT);
}
$type = optional_param('type', null, PARAM_TEXT);
$times = optional_param('times', null, PARAM_INT);
$aicon = optional_param('aicon', null, PARAM_TEXT);
$dismissible = optional_param('dismissible', null, PARAM_TEXT);
$datefrom = optional_param('date_from', null, PARAM_TEXT);
$dateto = optional_param('date_to', null, PARAM_TEXT);

$dismiss = optional_param('dismiss', null, PARAM_TEXT);                 // User dismissed notification.
$purpose = optional_param('purpose', null, PARAM_TEXT);                 // Purpose of request.
$tableaction = optional_param('tableaction', null, PARAM_TEXT);         // ID of item to action.

// Check if ajax call or not (Progressive Enhancement - yay!).
$ajax = false;

if ($calltype === 'ajax') {
    $ajax = true;
}

// GLOBAL.
// Sort out whether global or instance-based - if the global variable contains anything it is assumed to be global.
if (isset($global) && $global != "") {
    $global = 1;
} else {
    $global = 0;
}

// NEW NOTIFICATION.
// Change the checkbox values to integers for DB - another level of security.
if ($enabled == 'on' || $enabled == '1') {
    $enabled = 1;
} else {
    $enabled = 0;
}
if ($aicon == 'on' || $aicon == '1') {
    $aicon = 1;
} else {
    $aicon = 0;
}
if ($dismissible == 'on' || $dismissible == '1') {
    $dismissible = 1;
} else {
    $dismissible = 0;
}

// TODO: Check if successful?
// Convert dates to epoch for DB. If empty, set to 0 (forever) by default.
$datefrom == "" ? $datefrom = 0 : $datefrom = strtotime($datefrom);
$dateto == "" ? $dateto = 0 : $dateto = strtotime($dateto);

if (isset($dismiss) && $dismiss != '') {
    $notification = $DB->get_record('block_advnotifications',
        array('id' => $dismiss)
    );
    $userdissed = $DB->get_record('block_advnotificationsdissed',
        array('user_id' => $USER->id, 'not_id' => $dismiss)
    );

    // Update if the user has dismissed the notification.
    if ($userdissed) {
        $DB->set_field('block_advnotificationsdissed', 'dismissed', 1, array('id' => $userdissed->id));
    }

    if ($ajax) {
        echo json_encode("Di: Successful");
        exit();
    } else {
        exit();
    }
}

// Any logged-in user can dismiss notification, but any other actions require manage capabilities.
require_capability('block/advnotifications:managenotifications', $context);

// Handle Delete/Edit early as it requires few resources, and then we can quickly exit(),
// this is the new AJAX/JS deletion/editing method.
if (isset($tableaction) && $tableaction != '') {
    if ($purpose == 'edit') {
        $enotification = $DB->get_record('block_advnotifications', array('id' => $tableaction));

        $enotification->date_from = date('Y-m-d', $enotification->date_from);
        $enotification->date_to = date('Y-m-d', $enotification->date_to);

        if ($ajax) {
            echo json_encode($enotification);
            exit();
        } else {
            redirect(new moodle_url('/blocks/advnotifications/pages/notifications.php'),
                get_string('advnotifications_err_nojsedit', 'block_advnotifications'));
        }
    } else if ($purpose == 'delete') {
        $dnotification = new stdClass();
        $dnotification->id = $tableaction;
        $dnotification->deleted = 1;
        $dnotification->deleted_at = time();
        $dnotification->deleted_by = $USER->id;

        $DB->update_record('block_advnotifications', $dnotification);

        if ($ajax) {
            echo json_encode(array("done" => $tableaction));
            exit();
        } else {
            redirect(new moodle_url('/blocks/advnotifications/pages/notifications.php'));
        }
    } else if ($purpose == 'restore') {
        $rnotification = new stdClass();
        $rnotification->id = $tableaction;
        $rnotification->deleted = 0;
        $rnotification->deleted_at = 0;
        $rnotification->deleted_by = -1;

        $DB->update_record('block_advnotifications', $rnotification);

        if ($ajax) {
            echo json_encode(array("done" => $tableaction));
            exit();
        } else {
            redirect(new moodle_url('/blocks/advnotifications/pages/restore.php'));
        }
    } else if ($purpose == 'permdelete') {
        $DB->delete_records('block_advnotifications', array('id' => $tableaction));

        if ($ajax) {
            echo json_encode(array('done' => $tableaction));
            exit();
        } else {
            redirect(new moodle_url('/blocks/advnotifications/pages/restore.php'));
        }
    }
}

// Get plugin strings so JS can use appropriate locale strings.
if ($purpose == 'strings') {
    if ($ajax) {
        $strings = new stdClass();

        $strings->save = get_string('advnotifications_save', 'block_advnotifications');
        $strings->update = get_string('advnotifications_update', 'block_advnotifications');
        $strings->req = get_string('advnotifications_req', 'block_advnotifications');
        $strings->preview = get_string('advnotifications_preview', 'block_advnotifications');
        $strings->title = get_string('advnotifications_title', 'block_advnotifications');
        $strings->message = get_string('advnotifications_message', 'block_advnotifications');

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($strings);
        exit();
    }
    // Else do nothing... No JS, no JS strings needed...
}

// Update existing notification, instead of inserting a new one.
if ($purpose == 'update') {
    // Only check for id parameter when updating.
    $id = optional_param('id', null, PARAM_INT);

    // Update an existing notification.
    $urow = new stdClass();

    $urow->id = $id;
    $urow->title = $title;
    $urow->message = $message;
    $urow->type = $type;
    $urow->aicon = $aicon;
    $urow->enabled = $enabled;
    $urow->global = $global;
    $urow->blockid = $blockinstance;
    $urow->dismissible = $dismissible;
    $urow->date_from = $datefrom;
    $urow->date_to = $dateto;
    $urow->times = $times;

    $DB->update_record('block_advnotifications', $urow);

    if ($ajax) {
        echo json_encode(array("updated" => $title));
        exit();
    } else {
        redirect(new moodle_url('/blocks/advnotifications/pages/notifications.php'),
            get_string('advnotifications_err_nojsedit', 'block_advnotifications'));
    }
}

if ($purpose == "add") {
    // Check for required fields.
    $error = '';
    $fields = [];

    if (!isset($type)) {
        $fields[] = 'type';
        $error .= '"' . get_string('advnotifications_type', 'block_advnotifications') . '"';
    }
    if (!isset($times)) {
        $fields[] = 'times';

        if ($error !== '') {
            $error .= get_string('advnotifications_join', 'block_advnotifications');
        }

        $error .= '"' . get_string('advnotifications_times', 'block_advnotifications') . '"';
    }
    if ($error !== '') {
        if ($ajax) {
            // Return Error.
            // Technically we should never reach this if JS is enabled client-side,
            // but leaving it in case validation slipped past JS.
            header('HTTP/1.1 400 Bad Request Invalid Input');
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array('error' => $fields));
            exit();
        } else {
            // Redirect with Error.
            redirect(new moodle_url('/blocks/advnotifications/pages/notifications.php'),
                get_string('advnotifications_err_req', 'block_advnotifications', $error));
        }
    }


    // Create a new notification - Used for both Ajax Calls & NON-JS calls.
    $row = new stdClass();

    $row->title = $title;
    $row->message = $message;
    $row->type = $type;
    $row->aicon = $aicon;
    $row->enabled = $enabled;
    $row->global = $global;
    $row->blockid = $blockinstance;
    $row->dismissible = $dismissible;
    $row->date_from = $datefrom;
    $row->date_to = $dateto;
    $row->times = $times;
    $row->deleted = 0;
    $row->deleted_at = 0;
    $row->deleted_by = -1;
    $row->created_by = $USER->id;

    $DB->insert_record('block_advnotifications', $row);

    // Send JSON response if AJAX call was made, otherwise simply redirect to origin page.
    if ($ajax) {
        // Return Successful.
        echo json_encode("I: Successful");
        exit();
    } else {
        redirect(new moodle_url('/blocks/advnotifications/pages/notifications.php'));
    }
}