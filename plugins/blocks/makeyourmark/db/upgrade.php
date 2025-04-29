<?php
// db/upgrade.php — Create block_makeyourmark_done on plugin upgrade

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for block_makeyourmark
 *
 * @param int $oldversion the version you are upgrading from
 * @return bool
 */
function xmldb_block_makeyourmark_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // New version number must match the one you set in version.php
    if ($oldversion < 2025042901) {

        // Define table block_makeyourmark_done to be created.
        $table = new xmldb_table('block_makeyourmark_done');

        // Add fields.
        $table->add_field('id',            XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('userid',        XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL);
        $table->add_field('eventid',       XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL);
        $table->add_field('timecompleted', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL);

        // Add keys.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userfk',   XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Add a unique index on (userid,eventid).
        $table->add_index('user_event', XMLDB_INDEX_UNIQUE, ['userid','eventid']);

        // Create the table if it doesn’t already exist.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Mark the upgrade as complete.
        upgrade_plugin_savepoint(true, 2025042901, 'block', 'makeyourmark');
    }

    return true;
}
