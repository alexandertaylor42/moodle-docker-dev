<?php
require_once(__DIR__ . '/../../config.php');
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventname = required_param('eventname', PARAM_TEXT);
    $eventdatetime = required_param('eventdatetime', PARAM_TEXT); // comes from datetime-local input

    // Convert datetime-local string to timestamp
    $timestamp = strtotime($eventdatetime);

    if ($timestamp) {
        $event = new stdClass();
        $event->name = $eventname;
        $event->description = '';
        $event->format = FORMAT_HTML;
        $event->courseid = 0; // site-level
        $event->groupid = 0;
        $event->userid = $USER->id;
        $event->modulename = '';
        $event->instance = 0;
        $event->eventtype = 'user';
        $event->timestart = $timestamp;
        $event->timeduration = 0;

        require_once($CFG->dirroot . '/calendar/lib.php');
        calendar_event::create($event);
    }
}

redirect(new moodle_url('/my'));
