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

// Load in Moodle's Tablelib lib.
require_once($CFG->dirroot . '/lib/tablelib.php');
// Call in block's table file.
require_once($CFG->dirroot . '/blocks/advnotifications/classes/notifications_table.php');

// PARAMS.
$params = array();

// Determines whether or not to download the table.
$download = optional_param('download', '', PARAM_ALPHA);

// Determines whether notification is global or instance-based.
$blockinstance = optional_param('blockid', '', PARAM_INT);

// Used for navigation links to keep track of blockid (if any).
$param = '';
$xparam = '';

// Build params array (used to build url later).
if (isset($download) && $download !== '') {
    $params['download'] = 1;
}

// TODO: Use 'new moodle_url()' instead?
if (isset($blockinstance) && $blockinstance !== '') {
    $param = '?blockid=' . $blockinstance;
    $xparam = '&blockid=' . $blockinstance;
    $params['blockid'] = $blockinstance;
    $bcontext = context_block::instance($blockinstance);
}

// Force the user to login/create an account to access this page.
require_login();

$context = context_system::instance();
$allnotifs = has_capability('block/advnotifications:managenotifications', $context);
$ownnotifs = false;

if (!$allnotifs) {
    if (empty($blockinstance) || !isset($blockinstance) || $blockinstance === -1) {
        throw new moodle_exception('advnotifications_err_nocapability', 'block_advnotifications');
    }
    $ownnotifs = has_capability('block/advnotifications:manageownnotifications', $bcontext);
}

if (!$allnotifs && !$ownnotifs) {
    throw new moodle_exception('advnotifications_err_nocapability', 'block_advnotifications');
}

// Set PAGE variables.
$url = new moodle_url($CFG->wwwroot . '/blocks/advnotifications/pages/notifications.php');
$PAGE->set_context($context);
$PAGE->set_url($url, $params);

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
    $PAGE->requires->js_call_amd('block_advnotifications/custom', 'initialise');
    if (isset($bcontext) && $ccontext = $bcontext->get_course_context(false)) {
        $course = $DB->get_field('course', 'fullname', ['id' => $ccontext->instanceid]);
        $PAGE->navbar->add(format_string($course), new moodle_url('/course/view.php', ['id' => $ccontext->instanceid]));
    }
    $PAGE->navbar->add(get_string('blocks'));
    $PAGE->navbar->add(get_string('pluginname', 'block_advnotifications'));
    $PAGE->navbar->add(get_string('advnotifications_table_title_short', 'block_advnotifications'));

    echo $OUTPUT->header();

    echo '<h1 class="page__title">' . get_string('advnotifications_table_title', 'block_advnotifications') . '</h1>';
}

// Configure the table.
$table->define_baseurl($url, $params);

$table->set_attribute('class', 'admin_table general_table notifications_table');
$table->collapsible(false);

$table->is_downloadable(true);
$table->show_download_buttons_at(array(TABLE_P_BOTTOM));

// Set SQL params for table.
$sqlwhere = 'deleted = :deleted';
$sqlparams = array('deleted' => 0);
if ($ownnotifs && !$allnotifs) {
    $sqlwhere .= ' AND created_by = :created_by';
    $sqlparams['created_by'] = $USER->id;
}

$table->set_sql('*', "{block_advnotifications}", $sqlwhere, $sqlparams);

$navbuttons['left'] = '<a class="btn btn-secondary instance"
                                href="' . $CFG->wwwroot . '/blocks/advnotifications/pages/restore.php' . $param . '">' .
                                get_string('advnotifications_nav_restore', 'block_advnotifications') . '</a>';
$navbuttons['right'] = '';
if ($allnotifs) {
    $navbuttons['right'] = '<a class="btn btn-secondary instance" href="' .
        $CFG->wwwroot . '/admin/settings.php?section=blocksettingadvnotifications' . $xparam . '">' .
        get_string('advnotifications_nav_settings', 'block_advnotifications') .
        '</a>';

    $params['global'] = true;
}

// Add navigation controls before the table.
echo '<div id="advnotifications_manage">' .
        get_string('setting/navigation_desc', 'block_advnotifications', $navbuttons) .
        '</div><br><br>';

// Add a wrapper with an id, which makes reloading the table easier (when using ajax).
echo '<div id="advnotifications_table_wrapper">';
$table->out(20, true);
echo '</div><hr>';

echo $renderer->add_notification($params);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
