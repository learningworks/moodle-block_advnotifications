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
 * Block for displaying notifications to users.
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

defined('MOODLE_INTERNAL') || die;


/**
 * Class block_advnotifications extends base blocks class. Initialise and render notifications.
 *
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_advnotifications extends block_base
{
    /**
     * Initialise block, set title.
     */
    public function init() {
        $this->title = get_string('advnotifications', 'block_advnotifications');
    }

    /**
     * Get and render content of block.
     *
     * @return bool|stdClass|stdObject
     */
    public function get_content() {
        global $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        if (get_config('block_advnotifications', 'enable')) {
            require_once($CFG->dirroot . '/blocks/advnotifications/locallib.php');

            $this->content = new stdClass();

            // Get the renderer for this page.
            $renderer = $this->page->get_renderer('block_advnotifications');

            // Get & prepare notifications to render.
            $notifications = prep_notifications($this->instance->id);

            // Render notifications.
            $html = $renderer->render_notification($notifications);

            $this->content->text = $html;

            return $this->content;
        }

        return false;
    }

    /**
     * FROM ::parent DOCS.
     * Return a block_contents object representing the full contents of this block.
     *
     * This internally calls ->get_content(), and then adds the editing controls etc.
     *
     * You probably should not override this method, but instead override
     * {@link html_attributes()}, {@link formatted_contents()} or {@link get_content()},
     * {@link hide_header()}, {@link (get_edit_controls)}, etc.
     *
     * @param renderer_base $output The core_renderer to use when generating the output.
     * @return block_contents $bc A representation of the block, for rendering.
     * @since Moodle 2.0.
     */
    public function get_content_for_output($output) {
        $bc = parent::get_content_for_output($output);

        // Only do this if bc has been set (block has content, editing mode on, etc).
        if (isset($bc)) {
            $context = context_system::instance();
            if ($this->page->user_can_edit_blocks() && has_capability('block/advnotifications:managenotifications', $context)) {
                // Edit config icon - always show - needed for positioning UI.
                $str = new lang_string('advnotifications_table_title', 'block_advnotifications');
                $controls = new action_menu_link_secondary(
                    new moodle_url('/blocks/advnotifications/pages/notifications.php', array('blockid' => $bc->blockinstanceid)),
                    new pix_icon('a/view_list_active', $str, 'moodle', array('class' => 'iconsmall', 'title' => '')),
                    $str,
                    array('class' => 'editing_manage')
                );

                array_unshift($bc->controls, $controls);
            }
        }

        return $bc;
    }

    /**
     * Gets Javascript that's required by the plugin.
     */
    public function get_required_javascript() {
        parent::get_required_javascript();

        $this->page->requires->js_call_amd('block_advnotifications/notif', 'initialise');
    }

    /**
     * Allow multiple instances of the block throughout the site.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        // Are you going to allow multiple instances of each block?
        // If yes, then it is assumed that the block WILL use per-instance configuration.
        return true;
    }

    /**
     * HTML attributes such as 'class' or 'title' can be injected into the block.
     *
     * @return array
     */
    public function html_attributes() {
        $attributes = parent::html_attributes();

        if (!empty($this->config->class)) {
            $attributes['class'] .= ' ' . $this->config->class;
        }

        return $attributes;
    }

    /**
     * Specifies that block has global configurations/admin settings
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Default return is false - header will be shown. Added check to show heading only if editing.
     *
     * @return boolean
     */
    public function hide_header() {
        // If editing, show header.
        if ($this->page->user_is_editing()) {
            return false;
        }
        return true;
    }
}