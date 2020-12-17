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
 * Restore page where deleted notifications can be... restored.
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

// Load in Moodle config.
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
// Load in Moodle's Tablelib lib.
require_once($CFG->dirroot . '/lib/tablelib.php');
// Call in block's table file.
require_once($CFG->dirroot . '/blocks/advnotifications/classes/restore_table.php');

// PARAMS.
$params = array();

// Determines which notification the user wishes to restore.
$restore = optional_param('restore', null, PARAM_INT);

// Determines which notification the user wishes to delete.
$delete = optional_param('delete', null, PARAM_INT);

// Determines whether or not to download the table.
$download = optional_param('download', null, PARAM_ALPHA);

// Used for navigation links to keep track of blockid (if any).
$blockid = optional_param('blockid', '', PARAM_INT);

$param = '';
$xparam = '';

if (isset($blockid) && $blockid !== '') {
    $param = '?blockid=' . $blockid;
    $xparam = '&blockid=' . $blockid;
}

if ( !!$download ) {
    $params['download'] = 1;
}

if ( !!$delete ) {
    // If wanting to delete a notification, delete from DB immediately before the table is rendered.
    $DB->delete_records('block_advnotifications', array('id' => $delete));
}

// Force the user to login/create an account to access this page.
require_login();

$context = context_system::instance();
$allnotifs = has_capability('block/advnotifications:managenotifications', $context);
$ownnotifs = false;

if (!$allnotifs) {
    if (empty($blockid) || !isset($blockid) || $blockid === -1) {
        throw new moodle_exception('advnotifications_err_nocapability', 'block_advnotifications');
    }
    $bcontext = context_block::instance($blockid);
    $ownnotifs = has_capability('block/advnotifications:manageownnotifications', $bcontext);
}

if (!$allnotifs && !$ownnotifs) {
    throw new moodle_exception('advnotifications_err_nocapability', 'block_advnotifications');
}

// Set PAGE variables.
$url = new moodle_url($CFG->wwwroot . '/blocks/advnotifications/pages/restore.php');
$PAGE->set_context($context);
$PAGE->set_url($url, $params);


$table = new advnotifications_restore_table('advnotifications-list-restore');
$table->is_downloading($download, 'advnotifications-list-restore', 'Advanced Notifications List Restore');

if (!$table->is_downloading()) {
    // Only print headers if not asked to download data.
    // Print the page header.
    $PAGE->set_title(get_string('advnotifications_restore_table_title', 'block_advnotifications'));
    $PAGE->set_heading(get_string('advnotifications_restore_table_heading', 'block_advnotifications'));
    $PAGE->requires->jquery();
    $PAGE->requires->js_call_amd('block_advnotifications/custom', 'initialise');
    $PAGE->navbar->add(get_string('blocks'));
    $PAGE->navbar->add(get_string('pluginname', 'block_advnotifications'));
    $PAGE->navbar->add(get_string('advnotifications_restore_table_title_short', 'block_advnotifications'));

    echo $OUTPUT->header();

    echo '<h1 class="page__title">' . get_string('advnotifications_restore_table_title', 'block_advnotifications') . '</h1>';
}

// Configure the table.
$table->define_baseurl($url, $params);

$table->set_attribute('class', 'admin_table general_table notifications_restore_table');
$table->collapsible(false);

$table->is_downloadable(true);
$table->show_download_buttons_at(array(TABLE_P_BOTTOM));

// Set SQL params for table.
$sqlwhere = 'deleted = :deleted';
$sqlparams = array('deleted' => 1);
if ($ownnotifs && !$allnotifs) {
    $sqlwhere .= ' AND created_by = :created_by';
    $sqlparams['created_by'] = $USER->id;
}

$table->set_sql('*', "{block_advnotifications}", $sqlwhere, $sqlparams);

// Print warning about permanently deleting notifications.
echo '<div class="restore_notification-block-wrapper">
        <div class="alert alert-danger">
            ' . get_string('advnotifications_restore_table_warning', 'block_advnotifications') . '
        </div>
      </div>';

$navbuttons['left'] = '<a class="btn btn-secondary instance" href="' .
                            $CFG->wwwroot . '/blocks/advnotifications/pages/notifications.php' . $param . '">' .
                            get_string('advnotifications_nav_manage', 'block_advnotifications') . '</a>';
$navbuttons['right'] = '';
if ($allnotifs) {
    $navbuttons['right'] = '<a class="btn btn-secondary instance" href="' .
                                $CFG->wwwroot . '/admin/settings.php?section=blocksettingadvnotifications' . $xparam . '">' .
                                get_string('advnotifications_nav_settings', 'block_advnotifications') . '</a>';
}

// Add navigation controls before the table.
echo '<div id="advnotifications_manage">' .
        get_string('setting/navigation_desc', 'block_advnotifications', $navbuttons) .
        '</div><br><br>';

// Add a wrapper with an id, which makes reloading the table easier (when using ajax).
echo '<div id="advnotifications_restore_table_wrapper">';
$table->out(20, true);
echo '</div>';

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
