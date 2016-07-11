<?php
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
function xmldb_block_advanced_notifications_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 201607071321) {

        // Define table block_advanced_notifications to be created.
        $table = new xmldb_table('block_advanced_notifications');

        // Adding fields to table block_advanced_notifications.
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

        // Adding keys to table block_advanced_notifications.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_advanced_notifications.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_advanced_notifications_dismissed to be created.
        $table = new xmldb_table('block_advanced_notifications_dismissed');

        // Adding fields to table block_advanced_notifications_dismissed.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('not_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('dismissed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('seen', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_advanced_notifications_dismissed.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_advanced_notifications_dismissed.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Advanced_notifications savepoint reached.
        upgrade_block_savepoint(true, 201607071321, 'advanced_notifications');
    }

}