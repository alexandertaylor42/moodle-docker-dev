<?php

class block_makeyourmark extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_makeyourmark');
    }

    public function get_content() {
        global $USER;

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
            $USER->id,
            0,
            0,
            0,
            $startofweek,
            $endofweek,
            true,
            false,
            [$USER->id],
            [],
            $courseids,
            ['type' => 'action']
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

        return $this->content;
    }

    public function applicable_formats() {
        return ['my' => true, 'site' => true];
    }
}
