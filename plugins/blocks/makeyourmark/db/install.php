<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_block_makeyourmark_install() {
    global $DB;

    // Get the system context
    $context = context_system::instance();

    // Get the dashboard page for default user (site home for authenticated user)
    $page = new moodle_page();
    $page->set_context($context);
    $page->set_pagelayout('mydashboard'); // Targeting the "My Moodle" dashboard
    $page->set_url('/my'); // Fallback target

    // Get the default dashboard layout config
    require_once($CFG->dirroot . '/my/lib.php');
    $myoverviewregion = get_my_page_blocks_default_region();
    $weight = -10; // Set weight to push it above other blocks (calendar usually has a higher weight)

    // Check if it's already present
    $exists = $DB->record_exists('block_instances', [
        'blockname' => 'makeyourmark',
        'parentcontextid' => $context->id
    ]);

    if (!$exists) {
        $blockinstance = new stdClass();
        $blockinstance->blockname       = 'makeyourmark';
        $blockinstance->parentcontextid = $context->id;
        $blockinstance->showinsubcontexts = 0;
        $blockinstance->pagetypepattern = 'my-index';
        $blockinstance->subpagepattern  = NULL;
        $blockinstance->defaultregion   = $myoverviewregion;
        $blockinstance->defaultweight   = $weight;
        $blockinstance->configdata      = '';

        // Insert block instance
        $DB->insert_record('block_instances', $blockinstance);
    }
}

