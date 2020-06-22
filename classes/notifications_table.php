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
 * Table that lists notifications.
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

defined('MOODLE_INTERNAL') || die;

// Load parent (& tablelib lib).
require_once(dirname(__FILE__) . '/base_table.php');

// The word 'notifications' is used twice, as I'm using the 'pluginname_filename' convention.

/**
 * Sets up the table which lists notifications and allows for management of listed items.
 *
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class advnotifications_notifications_table extends advnotifications_base_table {

    /**
     * This function is called for each data row to allow processing of the
     * actions value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return url to view the individual transaction
     */
    public function col_actions($values) {
        global $CFG;

        if ($this->is_downloading()) {
            return get_string('advnotifications_edit_label', 'block_advnotifications') . ' | ' .
                    get_string('advnotifications_delete_label', 'block_advnotifications');
        } else {
            return '<form id="tr'.$values->id.'" data-edit="' . $values->id . '" method="POST" action="' . $CFG->wwwroot .
                '/blocks/advnotifications/pages/process.php">
                    <input type="hidden" class="edit_notification_sesskey" name="sesskey" value="' . sesskey() . '">
                    <input type="hidden" class="edit_notification_purpose" name="purpose" value="edit">
                    <input type="hidden" class="edit_notification_tableaction" name="tableaction" value="' . $values->id . '">
                    <input type="submit" class="edit_notification_edit btn btn-light" name="edit" value="' .
                        get_string('advnotifications_edit_label', 'block_advnotifications') . '">
                </form> <form id="tr'.$values->id.'" data-delete="' . $values->id . '" method="POST" action="' . $CFG->wwwroot .
                '/blocks/advnotifications/pages/process.php">
                    <input type="hidden" class="delete_notification_sesskey" name="sesskey" value="' . sesskey() . '">
                    <input type="hidden" class="delete_notification_purpose" name="purpose" value="delete">
                    <input type="hidden" class="delete_notification_tableaction" name="tableaction" value="' . $values->id . '">
                    <input type="submit" class="delete_notification_delete btn btn-light" name="delete" value="' .
                get_string('advnotifications_delete_label', 'block_advnotifications') . '">
                </form>';
        }
    }
}