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
     * Gets Javascript that may be required for navigation
     */
    public function get_required_javascript() {
        global $CFG;

        parent::get_required_javascript();

        $this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/advnotifications/javascript/notif.js'));
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
    public function hide_header() {
        // If editing, show header.
        if ($this->page->user_is_editing()) {
            return false;
        }
        return true;
    }

    /**
     * RSS functionality additions start here
     */

    /** The maximum time in seconds that cron will wait between attempts to retry failing RSS feeds. */
    const CLIENT_MAX_SKIPTIME = 43200; // 60 * 60 * 12 seconds.

    /** @var bool track whether any of the output feeds have recorded failures */
    private $hasfailedfeeds = false;


    /**
     * Returns the html of a feed to be displaed in the block
     *
     * @param mixed feedrecord The feed record from the database
     * @param int maxentries The maximum number of entries to be displayed
     * @param boolean showtitle Should the feed title be displayed in html
     * @return block_rss_client\output\feed|null The renderable feed or null of there is an error
     */
    public function get_feed($feedrecord, $maxentries, $showtitle) {
        global $CFG;
        require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

        if ($feedrecord->skipuntil) {
            // Last attempt to gather this feed via cron failed - do not try to fetch it now.
            $this->hasfailedfeeds = true;
            return null;
        }

        $simplepiefeed = new moodle_simplepie($feedrecord->url);

        if(isset($CFG->block_advnotifications_timeout)){
            $simplepiefeed->set_cache_duration($CFG->block_advnotifications_timeout * 60);
        }

        if ($simplepiefeed->error()) {
            debugging($feedrecord->url .' Failed with code: '.$simplepiefeed->error());
            return null;
        }

        if(empty($feedrecord->preferredtitle)){
            // Simplepie does escape HTML entities.
            $feedtitle = $this->format_title($simplepiefeed->get_title());
        }else{
            // Moodle custom title does not does escape HTML entities.
            $feedtitle = $this->format_title(s($feedrecord->preferredtitle));
        }

        if (empty($this->config->title)){
            //NOTE: this means the 'last feed' displayed wins the block title - but
            //this is exiting behaviour..
            $this->title = strip_tags($feedtitle);
        }

        $feed = new \block_advnotifications\output\feed($feedtitle, $showtitle, $this->config->block_advnotifications_show_channel_image);

        if ($simplepieitems = $simplepiefeed->get_items(0, $maxentries)) {
            foreach ($simplepieitems as $simplepieitem) {
                try {
                    $item = new \block_advnotifications\output\item(
                        $simplepieitem->get_id(),
                        new moodle_url($simplepieitem->get_link()),
                        $simplepieitem->get_title(),
                        $simplepieitem->get_description(),
                        new moodle_url($simplepieitem->get_permalink()),
                        $simplepieitem->get_date('U'),
                        $this->config->display_description
                    );

                    $feed->add_item($item);
                } catch (moodle_exception $e) {
                    // If there is an error with the RSS item, we don't
                    // want to crash the page. Specifically, moodle_url can
                    // throw an exception of the param is an extremely
                    // malformed url.
                    debugging($e->getMessage());
                }
            }
        }

        // Feed image.
        if ($imageurl = $simplepiefeed->get_image_url()) {
            try {
                $image = new \block_advnotifications\output\channel_image(
                    new moodle_url($imageurl),
                    $simplepiefeed->get_image_title(),
                    new moodle_url($simplepiefeed->get_image_link())
                );

                $feed->set_image($image);
            } catch (moodle_exception $e) {
                // If there is an error with the RSS image, we don'twant to
                // crash the page. Specifically, moodle_url can throw an
                // exception if the param is an extremely malformed url.
                debugging($e->getMessage());
            }
        }

        return $feed;
    }

    /**
     * Strips a large title to size and adds ... if title too long
     * This function does not escape HTML entities, so they have to be escaped
     * before being passed here.
     *
     * @param string title to shorten
     * @param int max character length of title
     * @return string title shortened if necessary
     */
    function format_title($title,$max=64) {

        if (core_text::strlen($title) <= $max) {
            return $title;
        } else {
            return core_text::substr($title, 0, $max - 3) . '...';
        }
    }

    /**
     * cron - goes through all the feeds. If the feed has a skipuntil value
     * that is less than the current time cron will attempt to retrieve it
     * with the cache duration set to 0 in order to force the retrieval of
     * the item and refresh the cache.
     *
     * If a feed fails then the skipuntil time of that feed is set to be
     * later than the next expected cron time. The amount of time will
     * increase each time the fetch fails until the maximum is reached.
     *
     * If a feed that has been failing is successfully retrieved it will
     * go back to being handled as though it had never failed.
     *
     * CRON should therefor process requests for permanently broken RSS
     * feeds infrequently, and temporarily unavailable feeds will be tried
     * less often until they become available again.
     *
     * @return boolean Always returns true
     */
    function cron() {
        global $CFG, $DB;
        require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

        // Get the legacy cron time, strangely the cron property of block_base
        // does not seem to get set. This means we must retrive it here.
        $this->cron = $DB->get_field('block', 'cron', array('name' => 'advnotifications'));

        // We are going to measure execution times
        $starttime =  microtime();
        $starttimesec = time();

        // Fetch all site feeds.
        $rs = $DB->get_recordset('block_advnotificationsrss');
        $counter = 0;
        mtrace('');
        foreach ($rs as $rec) {
            mtrace('    ' . $rec->url . ' ', '');

            // Skip feed if it failed recently.
            if ($starttimesec < $rec->skipuntil) {
                mtrace('skipping until ' . userdate($rec->skipuntil));
                continue;
            }

            // Fetch the rss feed, using standard simplepie caching
            // so feeds will be renewed only if cache has expired
            core_php_time_limit::raise(60);

            $feed =  new moodle_simplepie();
            // set timeout for longer than normal to be agressive at
            // fetching feeds if possible..
            $feed->set_timeout(40);
            $feed->set_cache_duration(0);
            $feed->set_feed_url($rec->url);
            $feed->init();

            if ($feed->error()) {
                // Skip this feed (for an ever-increasing time if it keeps failing).
                $rec->skiptime = $this->calculate_skiptime($rec->skiptime);
                $rec->skipuntil = time() + $rec->skiptime;
                $DB->update_record('block_advnotificationsrss', $rec);
                mtrace("Error: could not load/find the RSS feed - skipping for {$rec->skiptime} seconds.");
            } else {
                mtrace ('ok');
                // It worked this time, so reset the skiptime.
                if ($rec->skiptime > 0) {
                    $rec->skiptime = 0;
                    $rec->skipuntil = 0;
                    $DB->update_record('block_advnotificationsrss', $rec);
                }
                // Only increase the counter when a feed is sucesfully refreshed.
                $counter ++;
            }
        }
        $rs->close();

        // Show times
        mtrace($counter . ' feeds refreshed (took ' . microtime_diff($starttime, microtime()) . ' seconds)');

        return true;
    }

    /**
     * Calculates a new skip time for a record based on the current skip time.
     *
     * @param int $currentskip The curreent skip time of a record.
     * @return int A new skip time that should be set.
     */
    protected function calculate_skiptime($currentskip) {
        // The default time to skiptime.
        $newskiptime = $this->cron * 1.1;
        if ($currentskip > 0) {
            // Double the last time.
            $newskiptime = $currentskip * 2;
        }
        if ($newskiptime > self::CLIENT_MAX_SKIPTIME) {
            // Do not allow the skip time to increase indefinatly.
            $newskiptime = self::CLIENT_MAX_SKIPTIME;
        }
        return $newskiptime;
    }
}
