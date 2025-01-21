<?php

class Theme__Autoloader {

    public function run() {

        spl_autoload_register(function ($class) {
    
            // Convert the namespace to a file path
            $prefix = 'Terra\\'; // The namespace prefix
            $base_dir = get_template_directory() . '/includes/'; // Base directory of your classes
        
            // Check if the class uses the namespace prefix
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }
        
            // Replace the namespace prefix with the base directory, and replace namespace separators with directory separators
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.class.php';
        
            // If the file exists, require it
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}

$theme_autoloader = new Theme__Autoloader();
$theme_autoloader->run();