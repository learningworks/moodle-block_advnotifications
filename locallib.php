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
 * Library of functions for the plugin to leverage.
 *
 * @package    block_advnotifications
 * @copyright  2018 LearningWorks Ltd - learningworks.co.nz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * This functions determines which notifications to render and what their attributes should be.
 *
 * @param mixed $instanceid Block instance id.
 * @return array Array of notifications' attributes needed for rendering.
 */
function prep_notifications($instanceid) {
    global $DB, $USER;

    $filternotif = false;

    // Check if we should apply filters to title/message or not.
    if (get_config('block_advnotifications', 'multilang')) {
        $filternotif = true;
    }

    // Notifications to render.
    $rendernotif = [];

    // CONDITIONS - add any future conditions here.
    // Initialise/Declare.
    $conditions = array();

    // No deleted notifications.
    $conditions['deleted'] = 0;

    // No disabled notifications.
    $conditions['enabled'] = 1;

    // Get notifications with conditions from above.
    $allnotifs = $DB->get_records('block_advnotifications', $conditions);

    foreach ($allnotifs as $notif) {

        // Keep track of number of times the user has seen the notification.
        // Check if a record of the user exists in the dismissed/seen table.
        // TODO: Move DB queries out of loop.
        $userseen = $DB->get_record('block_advnotificationsdissed',
            array('user_id' => $USER->id, 'not_id' => $notif->id)
        );

        // Get notification settings to determine whether to render it or not.
        $render = false;

        // Check if in date-range.
        if ($notif->date_from === $notif->date_to) {
            $render = true;
        } else if ($notif->date_from < time() && $notif->date_to > time()) {
            $render = true;
        }

        // Don't render if user has seen it more (or equal) to the times specified.
        if ($userseen !== false) {
            if ($userseen->seen >= $notif->times && $notif->times != 0) {
                $render = false;
            } else if ($userseen->dismissed > 0) {
                $render = false;
            }
        }

        // Don't render if notification isn't a global notification and the instanceid's/blockid's don't match.
        if ($notif->blockid != $instanceid && $notif->global == 0) {
            $render = false;
        }

        if ($render) {
            // Update how many times the user has seen the notification.
            if ($userseen === false) {
                $seenrecord = new stdClass();
                $seenrecord->user_id = $USER->id;
                $seenrecord->not_id = $notif->id;
                $seenrecord->dismissed = 0;
                $seenrecord->seen = 1;

                $DB->insert_record('block_advnotificationsdissed', $seenrecord);
            } else {
                $upseenrecord = new stdClass();
                $upseenrecord->id = $userseen->id;
                $upseenrecord->seen = $userseen->seen + 1;

                $DB->update_record('block_advnotificationsdissed', $upseenrecord);
            }

            // Get type to know which (bootstrap) class to apply.
            $alerttype = '';
            $aicon = '';

            // Allows for custom styling and serves as a basic filter if anything unwanted was somehow submitted.
            if (!empty($notif)) {
                if ($notif->type == "info") {
                    $alerttype = 'info';
                    $aicon = 'info';
                } else if ($notif->type == "success") {
                    $alerttype = 'success';
                    $aicon = 'success';
                } else if ($notif->type == "warning") {
                    $alerttype = 'warning';
                    $aicon = 'warning';
                } else if ($notif->type == "danger") {
                    $alerttype = 'danger';
                    $aicon = 'danger';
                } else if ($notif->type == "announcement") {
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
            if ($notif->dismissible == 1) {
                $extraclasses .= ' dismissible';
            }
            if ($notif->times > 0) {
                $extraclasses .= ' limitedtimes';
            }
            if ($notif->aicon == 1) {
                $extraclasses .= ' aicon';
            }

            // Construct notification - also format title/text to support multilang (filtered) strings.
            $rendernotif[] = array('extraclasses' => $extraclasses,                                         // Additional classes.
                'notifid' => $notif->id,                                                                    // Notification id.
                'alerttype' => $alerttype,                                                                  // Alert type )styling).
                'aiconflag' => $notif->aicon,                                                               // Render icon flag.
                'aicon' => $aicon,                                                                          // Which icon to render.
                'title' => $filternotif ? format_text($notif->title, FORMAT_HTML) : $notif->title,          // Title
                'message' => $filternotif ? format_text($notif->message, FORMAT_HTML) : $notif->message,    // Notification text.
                'dismissible' => $notif->dismissible);                                                      // Dismissible flag.
        }
    }

    return $rendernotif;
}