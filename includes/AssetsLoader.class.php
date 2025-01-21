<?php

namespace Terra;

class AssetsLoader {

    protected $assets = [];
    protected $pages = [];
    protected $currentPage = null;
    protected $assetsConfig = null;

    public function __construct() {
        $this->assets = $this->get_assets();
        $themeConfig = $this->get_theme_config();

        $this->pages = $themeConfig["pages"];
        $this->assetsConfig = $themeConfig["configs"];

        $this->currentPage = $this->get_current_page_data();
    }

    /**
     * Run the Assets Loader
     */
    public function run() {
        $this->dequeue_default_assets();
        $this->load_global_assets();
        $this->load_page_assets();
    }

    /**
     * Get list of Pages from pages.json file
     */
    private function get_theme_config() {

        $file_path = get_stylesheet_directory() . "/terra.json";

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

        $file_path = get_stylesheet_directory() . "/dist/manifest.json";

        if (!file_exists($file_path)) {
            return [];
        }

        $assets = json_decode(file_get_contents($file_path));

        return $assets;
    }

    /**
     * Get Current Page Data
     */
    private function get_current_page_data() {

        $current_template = get_page_template_slug();

        foreach ($this->pages as $page) {
            if ($current_template === $page->file) {
                return $page;
            }
        }
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

       // Enqueue Current age Scripts
       if(!$this->currentPage) return;

       $assetName = $this->currentPage->id . "_scripts";
       $this->enqueue_page_assets($this->currentPage->id, $this->assets->$assetName);
    }

    /**
     * Enqueue Page Assets Based on strategy
     */
    private function enqueue_page_assets( $id, $path ) {

        $type = pathinfo($path, PATHINFO_EXTENSION);
        
        $file_path = get_stylesheet_directory() . "/dist/{$path}";

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
                    get_stylesheet_directory_uri() . "/dist/{$path}",
                    array(),
                    wp_get_theme()->get("Version"),
                    true
                );
                return;
            } else {
                wp_enqueue_style(
                    "{$id}_styles",
                    get_stylesheet_directory_uri() . "/dist/{$path}",
                    array(),
                    wp_get_theme()->get("Version")
                );
            }
        
        }, PHP_INT_MAX);
    }

    /**
     * Dequeue Default WordPress Assets
     */
    private function dequeue_default_assets() {

        add_action("wp_enqueue_scripts", function() {  
            // Get all registered/enqueued styles and scripts
            global $wp_styles, $wp_scripts;

            // Dequeue all styles
            if (isset($wp_styles->queue)) {
                foreach ($wp_styles->queue as $handle) {
                    if(
                        $this->should_dequeue_asset($handle)
                    ) {
                        wp_dequeue_style($handle);
                        wp_deregister_style($handle);
                    }
                }
            }

            // Dequeue all scripts
            if (isset($wp_scripts->queue)) {
                foreach ($wp_scripts->queue as $handle) {
                    if(
                        $this->should_dequeue_asset($handle)
                    ) {
                        wp_dequeue_script($handle);
                        wp_deregister_script($handle);
                    }
                }
            }
        }, PHP_INT_MAX - 1);
    }

    /**
     * Check if asset should be dequeued
     */
    private function should_dequeue_asset( $asset_id ) {
        
        if(
            $this->currentPage->assets->dequeue === "all" &&
            (
                (
                    is_array($this->currentPage->assets->enqueue) &&
                    !in_array($asset_id, $this->currentPage->assets->enqueue)
                ) ||
                !is_array($this->currentPage->assets->enqueue)
            )
        ) {
            return true;
        }

        if(
            is_array($this->currentPage->assets->dequeue) &&
            in_array($asset_id, $this->currentPage->assets->dequeue)
        ) {
            return true;
        }

        return false;
    }
}