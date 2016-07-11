<?php
/**
 * Created by LearningWorks Ltd
 * Date: 4/07/16
 * Time: 3:02 PM
 */
define('AJAX_SCRIPT', true);

// Load in Moodle config
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';

try{
    require_sesskey();
}catch(EXCEPTION $e){
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(array("result"=>"Failed","Notification"=>"Your changes were not saved, please login again..."));
    exit();
}

$context = context_system::instance();

if ( !has_capability('block/advanced_notifications:managenotifications', $context) ) {
    require_capability('block/advanced_notifications:managenotifications', $context);
}

header('HTTP/1.0 200 OK');

global $DB, $USER;

// TODO - Check if insertions/updates/deletions were successful, and return appropriate message

// GET PARAMETERS
// Check if ajax or other type of call
$callType = optional_param('call','',PARAM_TEXT);

// Notification details
$enable = optional_param('enable','',PARAM_TEXT);
$title = optional_param('title','',PARAM_TEXT);
$message = optional_param('message','',PARAM_TEXT);
$type = optional_param('type','',PARAM_TEXT);
$times = optional_param('times','',PARAM_TEXT);
$icon = optional_param('icon','',PARAM_TEXT);
$dismissible = optional_param('dismissible','',PARAM_TEXT);
$date_from = optional_param('date_from','',PARAM_TEXT);
$date_to = optional_param('date_to','',PARAM_TEXT);
$global = optional_param('global','',PARAM_TEXT);

//Param that will be required later, depending on $global's value
$blockinstance = '';

// Notification management actions
$delete = optional_param('delete','',PARAM_INT);
$edit = optional_param('edit','',PARAM_INT);

//echo json_encode($_POST);
//exit();

//Handle Delete/Edit first as it requires few resources, and then we can quickly exit() - this is now a non-JS fallback
//DELETE
if (isset($delete) && $delete != "")
{

    $drow = new stdClass();

    $drow->id = $delete;
    $drow->deleted = 1;

    $deletedrowid = $DB->update_record('block_advanced_notifications', $drow);

    echo json_encode("D: Successful");
    exit();
}

//EDIT
if (isset($edit) && $edit != "")
{

    $erow = new stdClass();

    $erow->id = $edit;

    $editedrowid = $DB->get_record('block_advanced_notifications', $erow);

    echo json_encode("E: Successful");
    exit();
}

//GLOBAL
//Sort out whether global or instance-based
if (isset($global) && $global != "")
{
    $global = 1;
    $blockinstance = 1;
}
else
{
    $global = 0;
    $blockinstance = optional_param('blockid', '', PARAM_INT);
}

// Check if notifications are enabled 'globally'
if (get_config('block_advanced_notifications', 'enable') == 1) {

    //NEW NOTIFICATION
    //Change to checkbox values to integers for DB
    if ($enable == 'on') {
        $enable = 1;
    }
    if ($icon == 'on') {
        $icon = 1;
    }
    if ($dismissible == 'on') {
        $dismissible = 1;
    }

    //TODO How to check if successful?
    //Convert dates to epoch for DB
    $date_from = strtotime($date_from);

    $date_to = strtotime($date_to);

    if ($callType == 'ajax' && isloggedin()) {
        $dismiss = optional_param('dismiss', '', PARAM_TEXT);
        $purpose = optional_param('purpose', '', PARAM_TEXT);
        $tableaction = optional_param('tableaction', '', PARAM_TEXT);

        if (isset($dismiss) && $dismiss != '') {
            $notification = $DB->get_record('block_advanced_notifications', array('id' => $dismiss));
            $userdissed = $DB->get_record('block_advanced_notifications_dismissed', array('user_id' => $USER->id, 'not_id' => $dismiss));

            // Update if the user has dismissed the notification
            //TODO Is the first if statement even necessary? Or is that logic already handled by 'seen' in renderer.php?
            if ($userdissed === false)
            {
                $seenrecord = new stdClass();
                $seenrecord->user_id = $USER->id;
                $seenrecord->not_id = $dismiss;
                $seenrecord->dismissed = 1;
                $seenrecord->seen = 1;

                $DB->insert_record('block_advanced_notifications_dismissed', $seenrecord);
            }
            else
            {
                $upseenrecord = new stdClass();
                $upseenrecord->id = $userdissed->id;
                $upseenrecord->dismissed = 1;

                $DB->update_record('block_advanced_notifications_dismissed', $upseenrecord);
            }
            echo json_encode("Di: Successful");
            exit();
        }

        //Handle Delete/Edit early as it requires few resources, and then we can quickly exit() - this is the new AJAX/JS deletion/editing method
        if (isset($tableaction) && $tableaction != '')
        {
            if ($purpose == 'edit')
            {
                $enotification = $DB->get_record('block_advanced_notifications', array('id'=>$tableaction));

                $enotification->date_from = date('Y-m-d', $enotification->date_from);
                $enotification->date_to = date('Y-m-d', $enotification->date_to);

                echo json_encode(array("edit"=>$enotification));
                exit();
            }
            elseif ($purpose == 'delete')
            {
                $dnotification = new stdClass();
                $dnotification->id = $tableaction;
                $dnotification->deleted = 1;
                $dnotification->deleted_at = time();

                $DB->update_record('block_advanced_notifications', $dnotification);

                echo json_encode(array("done"=>$tableaction));
                exit();
            }
            elseif ($purpose == 'restore')
            {
                $rnotification = new stdClass();
                $rnotification->id = $tableaction;
                $rnotification->deleted = 0;
                $rnotification->deleted_at = 0;

                $DB->update_record('block_advanced_notifications', $rnotification);

                echo json_encode(array("done"=>$tableaction));
                exit();
            }
            elseif ($purpose == 'permdelete')
            {
                $DB->delete_records('block_advanced_notifications', array('id'=>$tableaction));

                echo json_encode(array("done"=>$tableaction));
                exit();
            }
        }

        //Update existing notification, instead of inserting a new one
        if ($purpose == 'update')
        {
            //Only check for id parameter when updating
            $id = optional_param('id', '', PARAM_INT);

//            echo json_encode($enable);
//            exit();

            //Update an existing notification
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
            $urow->date_from = $date_from;
            $urow->date_to = $date_to;
            $urow->times = $times;

            $DB->update_record('block_advanced_notifications', $urow);

            echo json_encode(array("updated"=>$title));
            exit();
        }

        echo json_encode("Aj: Successful");
        exit();


    } elseif (isloggedin()) {

        //Create a new notification - Used for both Ajax Calls & NON-JS method atm
        $row = new stdClass();

        $row->title = $title;
        $row->message = $message;
        $row->type = $type;
        $row->icon = $icon;
        $row->enabled = $enable;
        $row->global = $global;
        $row->blockid = $blockinstance;
        $row->dismissible = $dismissible;
        $row->date_from = $date_from;
        $row->date_to = $date_to;
        $row->times = $times;
        $row->deleted = 0;
        $row->deleted_at = 0;

//        echo json_encode($row);
//        exit();

        $DB->insert_record('block_advanced_notifications', $row);

        //Return Successful
        echo json_encode("I: Successful");
        exit();
    }
}