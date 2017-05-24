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
 * Created by LearningWorksLtd
 * Date: 4/07/16
 * Time: 1:44 PM
 */

/**
 * @param $oldversion
 * @return bool
 * @throws ddl_exception
 * @throws ddl_table_missing_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_block_advnotifications_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 201607071321) {

        // Define table block_advnotifications to be created.
        $table = new xmldb_table('block_advnotifications');

        // Adding fields to table block_advnotifications.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('title', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'info');
        $table->add_field('icon', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('global', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('blockid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('dismissible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('date_from', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0000000000');
        $table->add_field('date_to', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0000000000');
        $table->add_field('times', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('deleted_at', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0000000000');

        // Adding keys to table block_advnotifications.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_advnotifications.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_advnotifications_dismissed to be created.
        $table = new xmldb_table('block_advnotifications_dismissed');

        // Adding fields to table block_advnotifications_dismissed.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('not_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('dismissed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('seen', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_advnotifications_dismissed.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_advnotifications_dismissed.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Advnotifications savepoint reached.
        upgrade_block_savepoint(true, 201607071321, 'advnotifications');
    }

}