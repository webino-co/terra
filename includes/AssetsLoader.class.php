<?php

namespace Terra;

class AssetsLoader {

    protected $assets = [];
    protected $pages = [];
    protected $assetsConfig = null;

    public function __construct() {
        $this->assets = $this->get_assets();
        $themeConfig = $this->get_theme_config();

        $this->pages = $themeConfig["pages"];
        $this->assetsConfig = $themeConfig["configs"];
    }

    public function run() {
        $this->load_global_assets();
        $this->load_page_assets();
    }

    /**
     * Get list of Pages from pages.json file
     */
    private function get_theme_config() {

        $file_path = get_template_directory() . "/terra.json";

        if (!file_exists($file_path)) {
            return [];
        }

        $content = json_decode(file_get_contents($file_path));

        return [
            "pages" => $content->pages ?: [],
            "configs" => $content->config->assets ?: null
        ];
    }

    /**
     * Get List of all assets built using Webpack
     */
    private function get_assets() {

        $file_path = get_template_directory() . "/dist/manifest.json";

        if (!file_exists($file_path)) {
            return [];
        }

        $assets = json_decode(file_get_contents($file_path));

        return $assets;
    }

    /**
     * Load Theme Global Assets
     */
    private function load_global_assets() {

        // Enqueue Global Styles
        $this->enqueue_page_assets("theme_styles", $this->assets->theme_styles);

        // Enqueue Global Scripts
        $this->enqueue_page_assets("theme_scripts", $this->assets->theme_scripts);
    }

    /**
     * Load Page Specific Assets
     */
    private function load_page_assets() {

        $current_template = get_page_template_slug();

        foreach ($this->pages as $page) {
            if ($current_template === $page->file) {
                // Enqueue Page Scripts
                $this->enqueue_page_assets($page->id, $this->assets->home_scripts);
            }
        }
    }

    private function enqueue_page_assets( $id, $path ) {

        $type = pathinfo($path, PATHINFO_EXTENSION);
        
        $file_path = get_template_directory() . "/dist/{$path}";

        $file_size = filesize($file_path);

        if(
            $this->assetsConfig->inlineStrategy &&
            $file_size <= $this->assetsConfig->inlineStrategy->maxSize
        ) {

            if( $type === "js" ) {
                add_action(
                    "wp_footer",
                    function() use ($id, $file_path) {
                        echo "<script id='{$id}_scripts'>" . file_get_contents($file_path) . "</script>";
                    }
                );
            } else {
                add_action(
                    "wp_head",
                    function() use ($id, $file_path) {
                        echo "<style id='{$id}_styles'>" . file_get_contents($file_path) . "</style>";
                    }
                );
            }
            
            return;
        }

        add_action("wp_enqueue_scripts", function() use ($id, $path, $type) {

            if( $type === "js" ) {
                wp_enqueue_script(
                    "{$id}_scripts",
                    get_template_directory_uri() . "/dist/{$path}",
                    array(),
                    wp_get_theme()->get("Version"),
                    true
                );
                return;
            } else {
                wp_enqueue_style(
                    "{$id}_styles",
                    get_template_directory_uri() . "/dist/{$path}",
                    array(),
                    wp_get_theme()->get("Version")
                );
            }
        
        });
    }
}