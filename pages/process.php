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
 * Created by LearningWorks Ltd
 * Date: 4/07/16
 * Time: 3:02 PM
 */
define('AJAX_SCRIPT', true);

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

$context = context_system::instance();

if ( !has_capability('block/advnotifications:managenotifications', $context) ) {
    require_capability('block/advnotifications:managenotifications', $context);
}

header('HTTP/1.0 200 OK');

global $DB, $USER;

// TODO - Check if insertions/updates/deletions were successful, and return appropriate message.

// GET PARAMETERS.
// Check if ajax or other type of call.
$calltype = optional_param('call', null, PARAM_TEXT);

// Notification details.
$enable = optional_param('enable', null, PARAM_TEXT);
$title = optional_param('title', null, PARAM_TEXT);
$message = optional_param('message', null, PARAM_TEXT);
$type = optional_param('type', null, PARAM_TEXT);
$times = optional_param('times', null, PARAM_TEXT);
$icon = optional_param('icon', null, PARAM_TEXT);
$dismissible = optional_param('dismissible', null, PARAM_TEXT);
$datefrom = optional_param('date_from', null, PARAM_TEXT);
$dateto = optional_param('date_to', null, PARAM_TEXT);
$global = optional_param('global', null, PARAM_TEXT);

// Param that will be required later, depending on $global's value.
$blockinstance = '';

// Notification management actions.
$delete = optional_param('delete', null, PARAM_INT);
$edit = optional_param('edit', null, PARAM_INT);

// Handle Delete/Edit first as it requires few resources, and then we can quickly exit() - this is now a non-JS fallback.
// DELETE.
if (isset($delete) && $delete != "") {

    $drow = new stdClass();

    $drow->id = $delete;
    $drow->deleted = 1;

    $deletedrowid = $DB->update_record('block_advnotifications', $drow);

    echo json_encode("D: Successful");
    exit();
}

// EDIT.
if (isset($edit) && $edit != "") {

    $erow = new stdClass();

    $erow->id = $edit;

    $editedrowid = $DB->get_record('block_advnotifications', $erow);

    echo json_encode("E: Successful");
    exit();
}

// GLOBAL.
// Sort out whether global or instance-based - if the global variable contains anything it is assumed to be global.
if (isset($global) && $global != "") {
    $global = 1;
    $blockinstance = 1;
} else {
    $global = 0;
    $blockinstance = optional_param('blockid', null, PARAM_INT);
}

// Check if notifications are enabled 'globally'.
if (get_config('block_advnotifications', 'enable') == 1) {

    // NEW NOTIFICATION.
    // Change to checkbox values to integers for DB - another level of security.
    if ($enable == 'on' || $enable == '1') {
        $enable = 1;
    } else {
        $enable = 0;
    }
    if ($icon == 'on' || $icon == '1') {
        $icon = 1;
    } else {
        $icon = 0;
    }
    if ($dismissible == 'on' || $dismissible == '1') {
        $dismissible = 1;
    } else {
        $dismissible = 0;
    }

    // TODO How to check if successful?
    // Convert dates to epoch for DB.
    $datefrom = strtotime($datefrom);

    $dateto = strtotime($dateto);

    if ($calltype == 'ajax' && isloggedin()) {
        $dismiss = optional_param('dismiss', null, PARAM_TEXT);
        $purpose = optional_param('purpose', null, PARAM_TEXT);
        $tableaction = optional_param('tableaction', null, PARAM_TEXT);

        if (isset($dismiss) && $dismiss != '') {
            $notification = $DB->get_record('block_advnotifications',
                                            array('id' => $dismiss)
            );
            $userdissed = $DB->get_record('block_advnotifications_dismissed',
                                            array('user_id' => $USER->id, 'not_id' => $dismiss)
            );

            // Update if the user has dismissed the notification.
            // TODO Is the first if statement even necessary? Or is that logic already handled by 'seen' in renderer.php?
            if ($userdissed === false) {
                $seenrecord = new stdClass();
                $seenrecord->user_id = $USER->id;
                $seenrecord->not_id = $dismiss;
                $seenrecord->dismissed = 1;
                $seenrecord->seen = 1;

                $DB->insert_record('block_advnotifications_dismissed', $seenrecord);
            } else {
                $upseenrecord = new stdClass();
                $upseenrecord->id = $userdissed->id;
                $upseenrecord->dismissed = 1;

                $DB->update_record('block_advnotifications_dismissed', $upseenrecord);
            }

            echo json_encode("Di: Successful");
            exit();
        }

        // Handle Delete/Edit early as it requires few resources, and then we can quickly exit(),
        // this is the new AJAX/JS deletion/editing method.
        if (isset($tableaction) && $tableaction != '') {
            if ($purpose == 'edit') {
                $enotification = $DB->get_record('block_advnotifications', array('id' => $tableaction));

                $enotification->date_from = date('Y-m-d', $enotification->date_from);
                $enotification->date_to = date('Y-m-d', $enotification->date_to);

                echo json_encode(array("edit" => $enotification));
                exit();
            } else if ($purpose == 'delete') {
                $dnotification = new stdClass();
                $dnotification->id = $tableaction;
                $dnotification->deleted = 1;
                $dnotification->deleted_at = time();

                $DB->update_record('block_advnotifications', $dnotification);

                echo json_encode(array("done" => $tableaction));
                exit();
            } else if ($purpose == 'restore') {
                $rnotification = new stdClass();
                $rnotification->id = $tableaction;
                $rnotification->deleted = 0;
                $rnotification->deleted_at = 0;

                $DB->update_record('block_advnotifications', $rnotification);

                echo json_encode(array("done" => $tableaction));
                exit();
            } else if ($purpose == 'permdelete') {
                $DB->delete_records('block_advnotifications', array('id' => $tableaction));

                echo json_encode(array("done" => $tableaction));
                exit();
            }
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
            $urow->icon = $icon;
            $urow->enabled = $enable;
            $urow->global = $global;
            $urow->blockid = $blockinstance;
            $urow->dismissible = $dismissible;
            $urow->date_from = $datefrom;
            $urow->date_to = $dateto;
            $urow->times = $times;

            $DB->update_record('block_advnotifications', $urow);

            echo json_encode(array("updated" => $title));
            exit();
        }

        echo json_encode("Aj: Successful");
        exit();


    } else if (isloggedin()) {
        // Create a new notification - Used for both Ajax Calls & NON-JS method atm.
        $row = new stdClass();

        $row->title = $title;
        $row->message = $message;
        $row->type = $type;
        $row->icon = $icon;
        $row->enabled = $enable;
        $row->global = $global;
        $row->blockid = $blockinstance;
        $row->dismissible = $dismissible;
        $row->date_from = $datefrom;
        $row->date_to = $dateto;
        $row->times = $times;
        $row->deleted = 0;
        $row->deleted_at = 0;

        $DB->insert_record('block_advnotifications', $row);

        // Return Successful.
        echo json_encode("I: Successful");
        exit();
    }
}