<?php
require('../../config.php');
require_login(); // Require the user to be logged in

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/makeyourmark/index.php'));
$PAGE->set_title(get_string('makeyourmark', 'local_makeyourmark'));
$PAGE->set_heading(get_string('makeyourmark', 'local_makeyourmark'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('makeyourmark', 'local_makeyourmark'));
echo html_writer::div("This is your custom planner plugin.");
echo $OUTPUT->footer();

