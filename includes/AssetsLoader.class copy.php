<?php

namespace Terra;

class AssetsLoader {

    protected $styles = [];
    protected $scripts = [];

    public function __construct() {

        $assets = $this->get_assets_list();
        $assets = $this->purge_assets_list($assets);

        $this->seperate_styles_and_scripts($assets);
        
        add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ) );
    }

    /**
     * Get List of all assets built using Webpack
     */
    private function get_assets_list() {

        $file_path = get_stylesheet_directory() . "/dist/manifest.json";

        if (!file_exists($file_path)) {
            return [];
        }

        $assets = json_decode(file_get_contents($file_path));

        return $assets;
    }

    /**
     * Seperate CSS & JS files
     */
    private function seperate_styles_and_scripts( $assets ) {
        foreach($assets as $name => $filename) {
            if( end(explode(".", $filename)) === "js" ) {
                $this->scripts[$name] = $filename;
            } else {
                $this->styles[$name] = $filename;
            }
        }
    }

    /**
     * Hadnle Loading OF All Assets
     */
    public function load_assets() {
        $this->load_styles();
        $this->load_scripts();
    }

    /**
     * Load CSS Files
     */
    private function load_styles() {

        foreach( $this->styles as $name => $filename ) {
            wp_enqueue_style(
                $name,
                get_stylesheet_directory_uri() . "/dist/{$filename}",
                array(),
                wp_get_theme()->get('Version')
            );
        }
    }

    /**
     * Load JS Files
     */
    private function load_scripts() {

        foreach( $this->scripts as $name => $filename ) {
            wp_enqueue_script(
                $name,
                get_stylesheet_directory_uri() . "/dist/{$filename}",
                array(),
                wp_get_theme()->get('Version'),
                true
            );
        }
    }

    /**
     * Remove unneccesary assets
     */
    private function purge_assets_list( $assets ) {

        $purged_assets= [];

        foreach( $assets as $name => $filename ) {

            $file_ownership = explode("_", $name)[0];

            if(
                $file_ownership === "theme"
            ) {
                $purged_assets[$name] = $filename;
            }
        }

        return $purged_assets;
    }
}