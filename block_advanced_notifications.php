<?php
class block_advanced_notifications extends block_base
{
    public function init()
    {
        global $CFG, $PAGE;
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/advanced_notifications/javascript/settings.js'));
        $this->title = get_string('advanced_notifications', 'block_advanced_notifications');
    }

    public function get_content() {

        if ($this->config->enable === 1) {
            $this->content = new stdClass;


            $title = $this->config->titletext;
            $messagetextarea = $this->config->messagetextarea;
            $type = $this->config->type;
            $icon = $this->config->icon;

            $html = '';

            // Get type to know which bootstrap class to apply
            $alerttype = '';

            // Allows for custom styling
            if (!empty($title))
            {
                if ($type == "information")
                {
                    $alerttype = 'info';
                }
                elseif ($type == "success")
                {
                    $alerttype = 'success';
                }
                elseif ($type == "warning")
                {
                    $alerttype = 'warning';
                }
                elseif ($type == "danger")
                {
                    $alerttype = 'danger';
                }
                elseif ($type == "announcement")
                {
                    $alerttype = 'info announcement';
                }
            }
            else
            {
                $alerttype = 'info';
            }

            // Open notification block
            $html .= '<div class="notification-block-wrapper">
                        <div class="alert alert-' . $alerttype . '">';

            if (!empty($title))
            {
                $html .= '<strong>' . $title . '</strong> ';
            }
            if (!empty($messagetextarea))
            {
                $html .= $messagetextarea;
            }

            // Close notification block
            $html .= '    </div>
                      </div>';

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

        if ($this->config->dismissible === 1) {

            $attributes['class'] .= " dismissible";
        }
        return $attributes;
    }
}