<?php
// markdone.php — mark a calendar event as completed by this user

require_once(__DIR__ . '/../../config.php');
require_login();

// Grab and validate the event ID from POST
$eventid = required_param('eventid', PARAM_INT);

global $DB, $USER;

// Insert a “done” record (ignore duplicates if already done)
if (!$DB->record_exists('block_makeyourmark_done', ['userid' => $USER->id, 'eventid' => $eventid])) {
    $DB->insert_record('block_makeyourmark_done', (object)[
        'userid'        => $USER->id,
        'eventid'       => $eventid,
        'timecompleted' => time(),
    ]);
}

// Redirect back to “My Moodle” where your block lives
redirect(new moodle_url('/my/'));
