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
 * Base table that lists notifications to allow visibility and certain actions.
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

defined('MOODLE_INTERNAL') || die;

// Load tablelib lib.
require_once($CFG->dirroot .'/lib/tablelib.php');

// The word 'notifications' is used twice, as I'm using the 'pluginname_filename' convention.

/**
 * Sets up the table which lists notifications and allows for management of listed items.
 *
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class advnotifications_base_table extends table_sql {

    // Lang strings that get re-used below is stored in variables to improve efficiency (Don't have to get strings many times).
    /**
     * @var null|string
     */
    private $yes = null;
    /**
     * @var null|string
     */
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
            'aicon',
            'dismissible',
            'times',
            'date_from',
            'date_to',
            'actions'
        );
        $this->define_columns($columns);

        // Define the titles of columns to show in header from lang file.                           // Examples.
        $headers = array(
            get_string('advnotifications_field_id', 'block_advnotifications'),          // Id: 1.
            get_string('advnotifications_field_title', 'block_advnotifications'),       // Title: Site Maintenance.
            get_string('advnotifications_field_type', 'block_advnotifications'),        // Type: info.
            get_string('advnotifications_field_enabled', 'block_advnotifications'),     // Enabled: Yes.
            get_string('advnotifications_field_global', 'block_advnotifications'),      // Global: Yes.
            get_string('advnotifications_field_aicon', 'block_advnotifications'),        // AIcon: Yes.
            get_string('advnotifications_field_dismissible', 'block_advnotifications'), // Dismissible: Yes.
            get_string('advnotifications_field_times', 'block_advnotifications'),       // Times: 10.
            get_string('advnotifications_field_date_from', 'block_advnotifications'),   // Date From: dd/mm/yyyy.
            get_string('advnotifications_field_date_to', 'block_advnotifications'),     // Date To: dd/mm/yyyy.
            get_string('advnotifications_field_actions', 'block_advnotifications'),     // Actions: Edit | Delete.
        );
        $this->define_headers($headers);

        $this->sortable(true, 'id', SORT_DESC);
        $this->no_sorting('actions');

        // Lang string initialisation.
        $this->yes = get_string('advnotifications_cell_yes', 'block_advnotifications'); // Yes.
        $this->no = get_string('advnotifications_cell_no', 'block_advnotifications');   // No.
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
        return get_string('advnotifications_add_option_' . $values->type, 'block_advnotifications');
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
     * @return $string Return whether notification will be propogated globally/site-wide or not
     */
    public function col_global($values) {
        return ($values->global == 1 ? $this->yes : $this->no);
    }

    /**
     * This function is called for each data row to allow processing of the
     * aicon value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return whether notification is to display an icon or not
     */
    public function col_aicon($values) {
        return ($values->aicon == 1 ? $this->yes : $this->no);
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
        if ($values->date_from <= 0) {
            return '-';
        }

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
        if ($values->date_from <= 0 || $values->date_from === $values->date_to) {
            return '-';
        }

        return date('d/m/Y', $values->date_to);
    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {
        $this->print_initials_bar();

        echo '<p class="notifications--empty">' . get_string('advnotifications_table_empty', 'block_advnotifications') . '</p>';
    }
}