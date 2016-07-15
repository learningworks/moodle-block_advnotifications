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
 * Created by LearningWorks Ltd.
 * Date: 4/07/16
 * Time: 2:31 PM
 */

global $CFG;

// Load Moodle config.
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
// Load Tablelib lib.
require_once($CFG->dirroot .'/lib/tablelib.php');

class advanced_notifications_restore_table extends table_sql {

    // Lang strings that get re-used below is stored in variables to improve efficiency (Don't have to get strings many times).
    private $yes = null;
    private $no = null;

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array(
            'id',
            'title',
            'type',
            'enabled',
            'global',
            'icon',
            'dismissible',
            'times',
            'date_from',
            'date_to',
            'actions'
        );
        $this->define_columns($columns);

        // Define the titles of columns to show in header from lang file.                           // Examples.
        $headers = array(
            get_string('advanced_notifications_field_id', 'block_advanced_notifications'),          // Id: 1.
            get_string('advanced_notifications_field_title', 'block_advanced_notifications'),       // Title: Site Maintenance.
            get_string('advanced_notifications_field_type', 'block_advanced_notifications'),        // Type: info.
            get_string('advanced_notifications_field_enabled', 'block_advanced_notifications'),     // Enabled: Yes.
            get_string('advanced_notifications_field_global', 'block_advanced_notifications'),      // Global: Yes.
            get_string('advanced_notifications_field_icon', 'block_advanced_notifications'),        // Icon: Yes.
            get_string('advanced_notifications_field_dismissible', 'block_advanced_notifications'), // Dismissible: Yes.
            get_string('advanced_notifications_field_times', 'block_advanced_notifications'),       // Times: 10.
            get_string('advanced_notifications_field_date_from', 'block_advanced_notifications'),   // Date From: dd/mm/yyyy.
            get_string('advanced_notifications_field_date_to', 'block_advanced_notifications'),     // Date To: dd/mm/yyyy.
            get_string('advanced_notifications_field_actions', 'block_advanced_notifications'),     // Actions: Edit | Delete.
        );
        $this->define_headers($headers);

        $this->sortable(true, 'id', SORT_DESC);
        $this->no_sorting('actions');

        // Lang string initialisation.
        $this->yes = get_string('advanced_notifications_cell_yes', 'block_advanced_notifications'); // Yes.
        $this->no = get_string('advanced_notifications_cell_no', 'block_advanced_notifications');   // No.
    }

    /**
     * This function is called for each data row to allow processing of the
     * id value.
     *
     * @param object $values Contains object with all the values of record.
     * @return integer Returns notification ids - easier sorting
     */
    public function col_id($values) {
        return $values->id;
    }

    /**
     * This function is called for each data row to allow processing of the
     * title value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Returns notification's title - easier sorting
     */
    public function col_title($values) {
        return $values->title;
    }

    /**
     * This function is called for each data row to allow processing of the
     * type value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return notification type (for styling purposes)
     */
    public function col_type($values) {
        return $values->type;
    }

    /**
     * This function is called for each data row to allow processing of the
     * enabled value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return whether notification is enabled or not
     */
    public function col_enabled($values) {
        return ($values->enabled == 1 ? $this->yes : $this->no);
    }

    /**
     * This function is called for each data row to allow processing of the
     * global value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return whether notification is enabled or not
     */
    public function col_global($values) {
        return ($values->global == 1 ? $this->yes : $this->no);
    }

    /**
     * This function is called for each data row to allow processing of the
     * icon value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return whether notification is enabled or not
     */
    public function col_icon($values) {
        return ($values->icon == 1 ? $this->yes : $this->no);
    }

    /**
     * This function is called for each data row to allow processing of the
     * dismissible value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return whether notification is dismissible or not
     */
    public function col_dismissible($values) {
        return ($values->dismissible == 1 ? $this->yes : $this->no);
    }

    /**
     * This function is called for each data row to allow processing of the
     * times value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return number of times the user should view the notification
     */
    public function col_times($values) {
        return $values->times;
    }

    /**
     * This function is called for each data row to allow processing of the
     * date_from value.
     *
     * @param object $values Contains object with all the values of record.
     * @return integer Return value from when the notification should be displayed
     */
    public function col_date_from($values) {
        return date('d/m/Y', $values->date_from);
    }

    /**
     * This function is called for each data row to allow processing of the
     * date_to value.
     *
     * @param object $values Contains object with all the values of record.
     * @return integer Return value until when the notification should be displayed
     */
    public function col_date_to($values) {
        return date('d/m/Y', $values->date_to);
    }

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
            return get_string('advanced_notifications_restore_label', 'block_advanced_notifications') . ' | ' .
                    get_string('advanced_notifications_delete_label', 'block_advanced_notifications');
        } else {
            return sprintf(
                '<a id="tr'.$values->id.'" data-restore="' . $values->id . '" href="' . $CFG->wwwroot .
                '/blocks/advanced_notifications/pages/process.php?sesskey=' . sesskey() . '&restore=' . $values->id .
                '">%s</a> | <a data-permdelete="' . $values->id . '" href="' . $CFG->wwwroot .
                '/blocks/advanced_notifications/pages/process.php?sesskey=' . sesskey() . '&permdelete=' . $values->id .
                '">%s</a>',
                get_string('advanced_notifications_restore_label', 'block_advanced_notifications'),
                get_string('advanced_notifications_delete_label', 'block_advanced_notifications')
            );
        }
    }

    /**
     * This function is called for each data row to allow processing of
     * columns which do not have a *_cols function.
     * @return string return processed value. Return NULL if no change has
     *     been made.
     */
    public function other_cols($colname, $value) {
        // Leaving here for future reference.
    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;
        $this->print_initials_bar();

        printf(
            '<p class="notifications--empty">%s</p>',
            get_string('advanced_notifications_table_empty', 'block_advanced_notifications')
        );
    }
}