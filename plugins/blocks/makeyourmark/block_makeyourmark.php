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
 * Make Your Mark block - Displays course events and custom events for students
 *
 * @package    block_makeyourmark
 * @copyright  2025 Make Your Mark
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_makeyourmark extends block_base {

    /**
     * Initialize the block
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_makeyourmark');
    }

    public function get_content() {
        global $USER, $OUTPUT, $PAGE, $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';

        // Step 1: Week range (Sunday to Saturday)
        $startofweek = strtotime('last sunday', time());
        if (date('w') == 0) {
            $startofweek = strtotime('today');
        }
        $endofweek = strtotime('+6 days', $startofweek) + 86399;

        // Step 2: Get user's enrolled course IDs across all contexts
        $courses = enrol_get_users_courses($USER->id, true, '*');
        $courseids = array_keys($courses);

        // Step 3: Get events for the current user across those courses
        $events = \core_calendar\local\api::get_events(
            null,
            null,
            null,
            null,
            null,
            null,
            20,
            null,
            [],
        );
        

        // Step 4: Group by day of week and then by course
        $weekdays = array_fill(0, 7, []);
        foreach ($events as $event) {
            $timestamp = $event->get_times()->get_start_time()->getTimestamp();
            $weekday = date('w', $timestamp);

            $course = $event->get_course();           
            if (!$course) {
                $courseid = 0;
                $course = (object)['id' => 0, 'fullname' => 'Unknown Course'];
            } else {
                $courseid = method_exists($course, 'get') ? $course->get('id') : $course->id;
                $fullname = method_exists($course, 'get') ? $course->get('fullname') : $course->fullname;
                $course = (object)['id' => $courseid, 'fullname' => $fullname];
            }

            if (!isset($courses[$courseid])) {
                $courses[$courseid] = $course;
            }

            $weekdays[$weekday][$courseid][] = $event;
        }

        // Step 5: Render weekly output
        $output = '<div class="makeyourmark-week">';
        $daynames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $currentday = date('w');  // 0 (Sunday) to 6 (Saturday)

        foreach ($weekdays as $daynum => $coursesByDay) {
            $isToday = ($daynum == $currentday) ? ' makeyourmark-today' : '';
            $output .= "<div class='makeyourmark-day{$isToday}'><strong>{$daynames[$daynum]}</strong><ul>";

            if (empty($coursesByDay)) {
                $output .= "<li>No events yet</li>";
            } else {
                foreach ($coursesByDay as $courseid => $events) {
                    $coursename = format_string($courses[$courseid]->fullname);
                    $output .= "<li><em>{$coursename}</em><ul>";
                    foreach ($events as $event) {
                        $name = format_string($event->get_name());
                        $time = userdate($event->get_times()->get_start_time()->getTimestamp(), '%I:%M %p');

                        // Try to get a submission or content link
                        $url = null;
                        if (method_exists($event, 'get_url')) {
                            $url = $event->get_url();
                        } elseif (method_exists($event, 'get_action')) {
                            $action = $event->get_action();
                            if (isset($action['url'])) {
                                $url = $action['url'];
                            }
                        }

                        if ($url) {
                            $output .= "<li><a href='{$url}'><strong>{$name}</strong> <span class='event-time'>($time)</span></a></li>";
                        } else {
                            $output .= "<li>{$name} <span class='event-time'>($time)</span> <span class='no-link'>(no link)</span></li>";
                        }
                    }
                    $output .= "</ul></li>";
                }
            }

            $output .= "</ul></div>";
        }

        $output .= '</div>';
        $this->content->text = $output;

        // ========== START OF CUSTOM EVENTS FEATURE ==========

        // Only load CSS if we haven't done so already
        if ($PAGE && !empty($PAGE->requires)) {
            $PAGE->requires->css('/blocks/makeyourmark/styles.css');
            $PAGE->requires->js_call_amd('block_makeyourmark/calendar', 'init');
        }

        // Get user's custom events for this week
        require_once($CFG->dirroot . '/calendar/lib.php');
        // Make sure the calendar functions are available before attempting to call them
        if (function_exists('calendar_get_events')) {
            $userevents = calendar_get_events($startofweek, $endofweek, false, 0, $USER->id);
        } else {
            $userevents = array(); // Initialize as empty if function not available
        }

        // Only display custom events section if there are events or user is logged in
        if (isloggedin() && isset($userevents)) {
            $this->content->text .= html_writer::tag('h3', 'My Custom Events', ['class' => 'custom-events-heading']);
            
            // Start custom events container
            $this->content->text .= html_writer::start_div('custom-events-container');
            
            // Check if there are any custom events
            if (isset($userevents) && !empty($userevents)) {
                $this->content->text .= html_writer::start_tag('ul', ['class' => 'custom-events-list']);
                
                foreach ($userevents as $event) {
                    // Format each event with delete button
                    $eventTime = userdate($event->timestart, '%A, %d %B %Y, %I:%M %p');
                    
                    $this->content->text .= html_writer::start_tag('li', ['class' => 'custom-event-item']);
                    $this->content->text .= html_writer::tag('strong', format_string($event->name));
                    $this->content->text .= html_writer::tag('span', " - {$eventTime}", ['class' => 'event-datetime']);

                    // Delete button for each event
                    $this->content->text .= html_writer::start_tag('form', [
                        'method' => 'post',
                        'action' => (new moodle_url('/blocks/makeyourmark/deleteevent.php'))->out(),
                        'style' => 'display:inline-block; margin-left:10px;'
                    ]);
                    $this->content->text .= html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => 'eventid',
                        'value' => $event->id
                    ]);
                    $this->content->text .= html_writer::empty_tag('input', [
                        'type' => 'submit',
                        'value' => 'Delete',
                        'class' => 'btn btn-danger btn-sm'
                    ]);
                    $this->content->text .= html_writer::end_tag('form');
                    
                    $this->content->text .= html_writer::end_tag('li');
                }
                
                $this->content->text .= html_writer::end_tag('ul');
            } else {
                $this->content->text .= html_writer::tag('p', 'No custom events yet', ['class' => 'no-events-message']);
            }
            
            $this->content->text .= html_writer::end_div(); // End custom-events-container

            // Manual event creation form
            $this->content->text .= html_writer::tag('h4', 'Add New Event', ['class' => 'add-event-heading']);
            $this->content->text .= html_writer::start_tag('form', [
                'method' => 'post',
                'action' => (new moodle_url('/blocks/makeyourmark/createevent.php'))->out(),
                'class' => 'create-event-form'
            ]);

            // Event name input
            $this->content->text .= html_writer::start_div('form-group');
            $this->content->text .= html_writer::tag('label', 'Event Name:', ['for' => 'eventname']);
            $this->content->text .= html_writer::empty_tag('input', [
                'type' => 'text',
                'id' => 'eventname',
                'name' => 'eventname',
                'placeholder' => 'Enter event name',
                'required' => true,
                'class' => 'form-control event-name-input'
            ]);
            $this->content->text .= html_writer::end_div();

            // Event date/time input
            $this->content->text .= html_writer::start_div('form-group');
            $this->content->text .= html_writer::tag('label', 'Date & Time:', ['for' => 'eventdatetime']);
            $this->content->text .= html_writer::empty_tag('input', [
                'type' => 'datetime-local',
                'id' => 'eventdatetime',
                'name' => 'eventdatetime',
                'required' => true,
                'class' => 'form-control event-date-input'
            ]);
            $this->content->text .= html_writer::end_div();

            // Submit button
            $this->content->text .= html_writer::start_div('form-group');
            $this->content->text .= html_writer::empty_tag('input', [
                'type' => 'submit',
                'value' => 'Create Event',
                'class' => 'btn btn-primary'
            ]);
            $this->content->text .= html_writer::end_div();

            $this->content->text .= html_writer::end_tag('form');
        }
        
        // ========== END OF CUSTOM EVENTS FEATURE ==========

        $this->content->footer = '';
        return $this->content;
    }

    public function applicable_formats() {
        return ['my' => true, 'site' => true];
    }
}
