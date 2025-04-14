<?php
class block_makeyourmark extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_makeyourmark');
    }
    public function get_content() {
        global $OUTPUT, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        // Load the stylesheet for this block
        $PAGE->requires->css('/blocks/makeyourmark/styles.css');

        // Start calendar layout
        $this->content->text = html_writer::start_div('weekly-calendar');

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($days as $day) {
            $this->content->text .= html_writer::start_div('day-column');
            $this->content->text .= html_writer::tag('h4', $day);
            $this->content->text .= html_writer::div('No events yet', 'day-content');
            $this->content->text .= html_writer::end_div();
        }

        $this->content->text .= html_writer::end_div();
        $this->content->footer = '';

        return $this->content;
    }

}

