<?php
require_once(__DIR__ . '/../../config.php');
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['eventid'])) {
    $eventid = (int) $_POST['eventid'];

    // Load calendar API
    require_once($CFG->dirroot . '/calendar/lib.php');

    // Get the event to ensure the user owns it
    $event = calendar_event::load($eventid);

    if ($event && $event->userid == $USER->id) {
        $event->delete();
    }
}

// Redirect back to the dashboard or block page
redirect(new moodle_url('/my'));
