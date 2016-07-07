<?php
class block_advanced_notifications extends block_base
{
    public function init()
    {
        global $CFG, $PAGE;
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/advanced_notifications/javascript/custom.js'));
        $this->title = get_string('advanced_notifications', 'block_advanced_notifications');
    }

    public function get_content() {

        global $PAGE;
        if (get_config('block_advanced_notifications', 'enable')) {

            // Get the renderer for this page
            $renderer = $PAGE->get_renderer('block_advanced_notifications');
            $html = $renderer->render_notification();

            $this->content->text = $html;

            return $this->content;
        }
        else
        {
            return false;
        }
    }

//    public function hide_header() {
//        return true;
//    }

    /* TODO | This was only added to suppress an 'error' that would occur, as get_content would be called twice
    *  TODO | which affects the DB calls when we record the number of times an user has seen the notification
    */
    function is_empty()
    {
        return false;
    }

    function instance_allow_multiple() {
        // Are you going to allow multiple instances of each block?
        // If yes, then it is assumed that the block WILL USE per-instance configuration
        return true;
    }

    function html_attributes()
    {
        $attributes = parent::html_attributes();

        if (!empty($this->config->class)) {

            $attributes['class'] .= " " . $this->config->class;
        }

        return $attributes;
    }

    /**
     * Specifies that block has global configurations/admin settings
     */
    public function has_config() {
        return true;
    }

    /**
     * Remove old (or deleted) notifications from table block_advanced_notifications & cleanup table block_advanced_notifications_dismissed
     */
    public function cron() {
        global $DB;

        echo "\n\tCleaning Advanced Notifications\n";

        if (get_config('block_advanced_notifications', 'auto_perma_delete')) {
            echo "\n\t\t- Permanently delete notifications that's had the deleted flag for more than 30 days...\n";
            // Permanently delete notifications that's had the deleted flag for more than 30 days
            $DB->delete_records_select('block_advanced_notifications', 'deleted_at < :limit AND deleted_at <> 0 AND deleted = 1',
                array('limit' => strtotime('-30 days')));
        }

        if (get_config('block_advanced_notifications', 'auto_delete')) {
            echo "\t\t- Add deleted flag to notifications that's passed their end-date...\n";
            // Add deleted flag to notifications that's passed their end-date
            $DB->set_field_select('block_advanced_notifications', 'deleted', '1', 'date_to < :now', array('now' => time()));
            $DB->set_field_select('block_advanced_notifications', 'deleted_at', time(), 'date_to < :now', array('now' => time()));
        }

        if (get_config('block_advanced_notifications', 'auto_delete_user_data')) {
            echo "\t\t- Remove user records that relates to notifications that don't exist anymore...\n\n";
            //Remove user records that relates to notifications that don't exist anymore
            $todelete = $DB->get_records_sql('SELECT band.id
                                            FROM {block_advanced_notifications_dismissed} AS band
                                            LEFT JOIN {block_advanced_notifications} AS ban ON band.not_id = ban.id
                                            WHERE ban.id IS NULL');

            $DB->delete_records_list('block_advanced_notifications_dismissed', 'id', array_keys((array)$todelete));
        }
    }
}