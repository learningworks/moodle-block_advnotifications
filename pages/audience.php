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
 * Manage notifications audiences.
 *
 * @package   block_advnotifications
 * @copyright 2021 Daniel Neis Araujo <daniel@adapta.online>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

$id = required_param('id', PARAM_INT);

$notification = $DB->get_record('block_advnotifications', ['id' => $id]);

if ($notification->blockid) {
    $bcontext = context_block::instance($notification->blockid);
    $ctx = $bcontext->get_course_context(false);
}
if (empty($ctx)) {
    $ctx = context_system::instance();
}

require_login();

$url = new moodle_url('/blocks/advnotifications/pages/audience.php', ['id' => $id]);

$str = get_string('editing_audiences', 'block_advnotifications');

$manageurl = new moodle_url('/blocks/advnotifications/pages/notifications.php');

$PAGE->set_context($ctx);
$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_title($str);
$PAGE->set_heading($str);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_advnotifications'));
$PAGE->navbar->add(get_string('advnotifications_table_title_short', 'block_advnotifications'), $manageurl);
$PAGE->navbar->add($str);

$output = $PAGE->get_renderer('block_advnotifications');

// TODO!
$userfieldfilters = $DB->get_records('block_advnotifications_field', ['notificationid' => $id]);
$notification->userfieldfilters = [];
foreach ($userfieldfilters as $f) {
    $notification->userfieldfilters[] = [
        'userfield' => $f->userfield,
        'operator' => $f->operator,
        'fieldvalue' => $f->fieldvalue,
    ];
}
if ($userfieldfilters) {
    $filterscount = count($userfieldfilters);
} else {
    $filterscount = 1;
}
$form = new \block_advnotifications\output\form\audience($url->out(false),
    ['notificationid' => $id, 'filterscount' => $filterscount]);

if ($form->is_cancelled()) {
    redirect($manageurl);
} else if ($data = $form->get_data()) {
    $DB->delete_records('block_advnotifications_coh', ['notificationid' => $id]);
    $DB->delete_records('block_advnotifications_field', ['notificationid' => $id]);
    $DB->delete_records('block_advnotifications_roles', ['notificationid' => $id]);
    $coh = (object)['notificationid' => $id];
    foreach ($data->cohorts as $c) {
        $coh->cohortid = $c;
        $DB->insert_record_raw('block_advnotifications_coh', $coh);
    }
    $field = (object)['notificationid' => $id];
    foreach ($data->userfieldfilters as $f) {
        $field->userfield = $f['userfield'];
        $field->operator = $f['operator'];
        $field->fieldvalue = $f['fieldvalue'];
        $DB->insert_record_raw('block_advnotifications_field', $field);
    }
    $role = (object)['notificationid' => $id];
    foreach ($data->roles as $r) {
        $role->roleid = $r;
        $DB->insert_record_raw('block_advnotifications_roles', $role);
    }
    redirect($url, get_string('audiencesaved', 'block_advnotifications'));
}
$form->set_data($notification);

echo $output->header(),
     $output->heading($notification->title),
     $form->render(),
     $output->footer();
