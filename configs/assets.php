<?php

use Terra\AssetsLoader;

class Theme__Assets {

    public function run() {
        $assets_loader = new AssetsLoader();
        $assets_loader->run();
    }
}

add_action('wp', function () {
    $theme_assets = new Theme__Assets();
    $theme_assets->run();
});