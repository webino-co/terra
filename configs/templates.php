<?php

use Terra\TemplateLoader;

class Theme__Templates {

    public function run() {
        $template_loader = new TemplateLoader();
        $template_loader->run();
    }
}

$theme_templates = new Theme__Templates();
$theme_templates->run();