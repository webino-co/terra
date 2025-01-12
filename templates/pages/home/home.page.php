<?php

use Terra\PageBuilder;

class HomePage extends PageBuilder {

    protected $page_id = "home";
    protected $props = [
        "name" => "Terra Theme",
    ];

}

/**
 * Render the Page
*/
$page = new HomePage();
$page->render();