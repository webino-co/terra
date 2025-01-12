<?php

namespace Terra;

class TemplateLoader {

    public function __construct() {
        $pages = $this->get_pages();
        $this->include_page_templates( $pages );
    }

    /**
     * Get list of Pages from pages.json file
     */
    private function get_pages() {

        $file_path = get_template_directory() . "/terra.json";

        if (!file_exists($file_path)) {
            return [];
        }

        $content = json_decode(file_get_contents($file_path));

        return $content->pages ?: [];
    }

    /**
     * Register page templates as valid template
     */
    private function include_page_templates( $pages ) {

        foreach( $pages as $page ) {
            
            add_filter("theme_page_templates", function ($templates) use ($page) {

                $templates[$page->file] = $page->title;
            
                return $templates;
            });
            
            add_filter("template_include", function ($template) use ($page) {

                if (is_page_template($page->file)) {
                    $custom_template = get_stylesheet_directory() . $page->file;
                    if (file_exists($custom_template)) {
                        return $custom_template;
                    }
                }
                return $template;
            });
        }
        
    }
}