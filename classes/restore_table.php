<?php
/**
 * Created by LearningWorks Ltd.
 * Date: 4/07/16
 * Time: 2:31 PM
 */

global $CFG;

// Load Moodle config
require_once dirname(__FILE__) . '/../../../config.php';
// Load Tablelib lib
require_once $CFG->dirroot .'/lib/tablelib.php';

class advanced_notifications_restore_table extends table_sql {

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($uniqueid) {
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array(
            'id',
            'title',
            'type',
            'date_from',
            'date_to',
            'dismissible',
            'times',
            'enabled',
            'actions'
        );
        $this->define_columns($columns);

        // Define the titles of columns to show in header from lang file.                               // Examples
        $headers = array(
            get_string('advanced_notifications_field_id', 'block_advanced_notifications'),              // 1
            get_string('advanced_notifications_field_title', 'block_advanced_notifications'),           // Site Maintenance
            get_string('advanced_notifications_field_type', 'block_advanced_notifications'),            // Information
            get_string('advanced_notifications_field_date_from', 'block_advanced_notifications'),       // dd/mm/yyyy
            get_string('advanced_notifications_field_date_to', 'block_advanced_notifications'),         // dd/mm/yyyy
            get_string('advanced_notifications_field_dismissible', 'block_advanced_notifications'),     // Yes
            get_string('advanced_notifications_field_times', 'block_advanced_notifications'),           // 10
            get_string('advanced_notifications_field_enabled', 'block_advanced_notifications'),         // Yes
            get_string('advanced_notifications_field_actions', 'block_advanced_notifications'),         // Edit | Delete
        );
        $this->define_headers($headers);

        $this->sortable(true, 'id', SORT_DESC);
        $this->no_sorting('actions');
    }

    /**
     * This function is called for each data row to allow processing of the
     * id value.
     *
     * @param object $values Contains object with all the values of record.
     * @return integer Returns notification ids - easier sorting
     */
    function col_id($values) {
        return $values->id;
    }

    /**
     * This function is called for each data row to allow processing of the
     * title value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Returns notification's title - easier sorting
     */
    function col_title($values) {
        return $values->title;
    }

    /**
     * This function is called for each data row to allow processing of the
     * type value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return notification type (for styling purposes)
     */
    function col_type($values){
        return $values->type;
    }

    /**
     * This function is called for each data row to allow processing of the
     * date_from value.
     *
     * @param object $values Contains object with all the values of record.
     * @return integer Return value from when the notification should be displayed
     */
    function col_date_from($values){
        return date('d/m/Y', $values->date_from);
    }

    /**
     * This function is called for each data row to allow processing of the
     * date_to value.
     *
     * @param object $values Contains object with all the values of record.
     * @return integer Return value until when the notification should be displayed
     */
    function col_date_to($values){
        return date('d/m/Y', $values->date_to);
    }

    /**
     * This function is called for each data row to allow processing of the
     * dismissible value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return whether notification is dismissible or not
     */
    function col_dismissible($values){
        return ($values->dismissible == 1 ? "Yes" : "No");
    }

    /**
     * This function is called for each data row to allow processing of the
     * times value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return number of times the user should view the notification
     */
    function col_times($values){
        return $values->times;
    }

    /**
     * This function is called for each data row to allow processing of the
     * enabled value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return whether notification is enabled or not
     */
    function col_enabled($values){
        return ($values->enabled == 1 ? "Yes" : "No");
    }

    /**
     * This function is called for each data row to allow processing of the
     * actions value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return url to view the individual transaction
     */
    function col_actions($values) {
        global $CFG;

        if ($this->is_downloading()) {
            return get_string('advanced_notifications_restore_label', 'block_advanced_notifications') . ' | ' . get_string('advanced_notifications_delete_label', 'block_advanced_notifications');
        } else {
            return sprintf(
                '<a id="tr'.$values->id.'" data-restore="' . $values->id . '" href="' . $CFG->wwwroot . '/blocks/advanced_notifications/pages/process.php?sesskey=' . sesskey() . '&restore=' . $values->id . '">%s</a> | <a data-permdelete="' . $values->id . '" href="' . $CFG->wwwroot . '/blocks/advanced_notifications/pages/process.php?sesskey=' . sesskey() . '&permdelete=' . $values->id . '">%s</a>',
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
    function other_cols($colname, $value) {
        // --- Leaving here for future reference ---

        // For security reasons we don't want to show the password hash.
        // if ($colname == 'password') {
        //     return "****";
        // }
    }

    /**
     * This function is not part of the public api.
     */
    function print_nothing_to_display() {
        global $OUTPUT;
        $this->print_initials_bar();

        printf(
            '<p class="notifications--empty">%s</p>',
            get_string('advanced_notifications_table_empty', 'block_advanced_notifications')
        );
    }
}