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
     * Initialise block, load JS, set title.
     */
    public function init() {
        global $CFG, $PAGE;
        $PAGE->requires->jquery();
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/advnotifications/javascript/custom.js'));
        $this->title = get_string('advnotifications', 'block_advnotifications');
    }

    /**
     * Get and render content of block.
     *
     * @return bool|stdClass|stdObject
     */
    public function get_content() {
        global $PAGE;
        if (get_config('block_advnotifications', 'enable')) {
            $this->content = new stdClass();

            // Get the renderer for this page.
            $renderer = $PAGE->get_renderer('block_advnotifications');
            $html = $renderer->render_notification($this->instance->id);

            $this->content->text = $html;

            return $this->content;
        } else {
            return false;
        }
    }

    /* TODO | This was only added to suppress an 'error' that would occur, as get_content would be called twice,
    *  TODO | which affects the DB calls when we record the number of times an user has seen the notification.
    */
    /**
     * Set block as not being empty.
     *
     * @return bool
     */
    public function is_empty() {
        return false;
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
            $attributes['class'] .= " " . $this->config->class;
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
    function hide_header() {
        global $PAGE;
        // If editing, show header.
        if ($PAGE->user_is_editing()) {
            return false;
        }
        return true;
    }

    /**
     * Remove old (or deleted) notifications from table block_advnotifications & cleanup table
     * block_advnotificationsdissed
     */
    public function cron() {
        global $DB;

        echo "\n\t" . get_string('advnotifications_cron_heading', 'block_advnotifications') . "\n";

        // Auto-Permanent Delete Feature.
        if (get_config('block_advnotifications', 'auto_perma_delete')) {
            echo "\n\t\t- " . get_string('advnotifications_cron_auto_perma_delete', 'block_advnotifications') . "\n";

            // Permanently delete notifications that's had the deleted flag for more than 30 days.
            $DB->delete_records_select('block_advnotifications',
                                        'deleted_at < :limit AND deleted_at <> 0 AND deleted = 1',
                                        array('limit' => strtotime('-30 days'))
            );
        }

        // Auto Delete Flagging Feature.
        if (get_config('block_advnotifications', 'auto_delete')) {
            echo "\t\t- " . get_string('advnotifications_cron_auto_delete', 'block_advnotifications') . "\n";

            // Add deleted flag to notifications that's passed their end-date.
            $DB->set_field_select('block_advnotifications',
                                    'deleted',
                                    '1',
                                    'date_to < :now AND date_from <> date_to',
                                    array('now' => time())
            );

            // Record time of setting 'deleted' flag.
            $DB->set_field_select('block_advnotifications',
                                    'deleted_at',
                                    time(),
                                    'deleted = 1'
            );
        }

        // Auto User Data Deletion Feature.
        if (get_config('block_advnotifications', 'auto_delete_user_data')) {
            echo "\t\t- " . get_string('advnotifications_cron_auto_delete_udata', 'block_advnotifications') . "\n\n";

            // Remove user records that relates to notifications that don't exist anymore.
            $todelete = $DB->get_records_sql('SELECT band.id
                                            FROM {block_advnotificationsdissed} band
                                            LEFT JOIN {block_advnotifications} ban ON band.not_id = ban.id
                                            WHERE ban.id IS NULL');

            $DB->delete_records_list('block_advnotificationsdissed',
                                        'id',
                                        array_keys((array)$todelete)
            );
        }
    }
}