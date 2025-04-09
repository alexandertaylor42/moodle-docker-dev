<?php
class block_makeyourmark extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_makeyourmark');
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = 'Welcome to the Make Your Mark planner block!';
        $this->content->footer = '';

        return $this->content;
    }
}

