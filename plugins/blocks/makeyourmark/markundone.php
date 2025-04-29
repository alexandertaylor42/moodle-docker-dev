<?php
// markundone.php — remove the “done” marker for a calendar event

require_once(__DIR__ . '/../../config.php');
require_login();

$eventid = required_param('eventid', PARAM_INT);
global $DB, $USER;

// Delete the record that marks this event as done
$DB->delete_records('block_makeyourmark_done', [
    'userid'  => $USER->id,
    'eventid' => $eventid
]);

// Redirect back to the page where your block lives
redirect(new moodle_url('/my/'));
