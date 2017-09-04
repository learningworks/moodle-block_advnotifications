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
 * Notification page where notfications are created and managed.
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

// Load in Moodle config.
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

global $CFG;

// Load in Moodle's Tablelib lib.
require_once($CFG->dirroot . '/lib/tablelib.php');
// Call in block's table file.
require_once($CFG->dirroot . '/blocks/advnotifications/classes/notifications_table.php');

// PARAMS.
$params = array();

// Determines if the user want to start creating a new notification.
$new = optional_param('new', null, PARAM_BOOL);

// Determines which notification the user wishes to edit.
$edit = optional_param('edit', null, PARAM_INT);

// Determines which notification the user wishes to delete.
$delete = optional_param('delete', null, PARAM_INT);

// Determines whether or not to download the table.
$download = optional_param('download', '', PARAM_ALPHA);

// Determines whether notification is global or instance-based.
$blockinstance = optional_param('blockid', '', PARAM_INT);

// Build params array (used to build url later).
if ( !!$new ) {
    $params['new'] = 1;
}

if ( !!$download ) {
    $params['download'] = 1;
}

if (isset($blockinstance) && $blockinstance != "") {
    $params['blockid'] = $blockinstance;
}

global $DB, $USER, $PAGE;

if ( !!$edit ) {
    $toedit = $DB->get_record('block_advnotifications', array('id' => $edit));
}

if ( !!$delete ) {
    // If wanting to delete a notification, delete from DB immediately before the table is rendered.

    $todelete = new stdClass();

    $todelete->id = $delete;
    $todelete->deleted = 1;
    $todelete->enabled = 0;
    $sql = $DB->update_record('block_advnotifications', $todelete);
}

$context = context_system::instance();
$url = new moodle_url($CFG->wwwroot . '/blocks/advnotifications/pages/notifications.php');

// Set PAGE variables.
$PAGE->set_context($context);
$PAGE->set_url($url, $params);

// Force the user to login/create an account to access this page.
require_login();

if ( !has_capability('block/advnotifications:managenotifications', $context) ) {
    require_capability('block/advnotifications:managenotifications', $context);
}

// Get the renderer for this page.
$renderer = $PAGE->get_renderer('block_advnotifications');

$table = new advnotifications_notifications_table('advnotifications-list');
$table->is_downloading($download, 'advnotifications-list', 'Advanced Notifications List');

if (!$table->is_downloading()) {
    // Only print headers if not asked to download data.
    // Print the page header.
    $PAGE->set_title(get_string('advnotifications_table_title', 'block_advnotifications'));
    $PAGE->set_heading(get_string('advnotifications_table_heading', 'block_advnotifications'));
    $PAGE->requires->jquery();
    $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/advnotifications/javascript/custom.js'));

    echo $OUTPUT->header();

    printf('<h1 class="page__title">%s</h1>', get_string('advnotifications_table_title', 'block_advnotifications'));
}

// Configure the table.
$table->define_baseurl($url, $params);

$table->set_attribute('class', 'admin_table general_table notifications_table');
$table->collapsible(false);

$table->is_downloadable(true);
$table->show_download_buttons_at(array(TABLE_P_BOTTOM));

$table->set_sql('*', "{block_advnotifications}", "deleted = 0");

// Add navigation controls before the table.
echo '<div id="advnotifications_manage">
        <a class="btn instance" href="' . $CFG->wwwroot . '/blocks/advnotifications/pages/restore.php">' .
            get_string('advnotifications_nav_restore', 'block_advnotifications') .
        '</a>&nbsp;&nbsp;
        <a class="btn instance" href="' . $CFG->wwwroot . '/admin/settings.php?section=blocksettingadvnotifications">' .
            get_string('advnotifications_nav_settings', 'block_advnotifications') .
        '</a><br><br>
      </div>';

// Add a wrapper with an id, which makes reloading the table easier (when using ajax).
echo '<div id="advnotifications_table_wrapper">';
$table->out(20, true);
echo '</div>';

echo $renderer->add_notification($params);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
