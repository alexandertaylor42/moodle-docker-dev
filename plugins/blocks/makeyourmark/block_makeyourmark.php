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
    
        // Step 2: Get user's enrolled course IDs
        $courses = enrol_get_users_courses($USER->id, true);
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
            []
        );
    
        // Step 4: Group by day of week
        $weekdays = array_fill(0, 7, []);
        foreach ($events as $event) {
            $timestamp = $event->get_times()->get_start_time()->getTimestamp(); // Correctly using the getter
            $weekday = date('w', $timestamp);
            $weekdays[$weekday][] = $event;
        }
    
        // Step 5: Render weekly output
        $output = '<div class="makeyourmark-week">';
        $daynames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    
        foreach ($weekdays as $daynum => $events) {
            $output .= "<div class='makeyourmark-day'><strong>{$daynames[$daynum]}</strong><ul>";
            if (empty($events)) {
                $output .= "<li>No events yet</li>";
            } else {
                foreach ($events as $event) {
                    $name = $event->get_name(); // Proper getter for name
                    $output .= "<li>" . format_string($name) . "</li>";
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

