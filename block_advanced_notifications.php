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

        global $PAGE, $USER;
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

    /* TODO This was only added to suppress an 'error' that would occur, as get_content would be called twice
    *  TODO which affects the DB calls when we record the number of times an user has seen the notification
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
}