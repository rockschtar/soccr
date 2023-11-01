<?php
/**
 * Plugin Name:     Soccr Reloaded
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     soccr-reloaded
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Soccr_Reloaded
 */


define('SOCCR_PLUGIN_DIR', plugin_dir_path(__FILE__));

spl_autoload_register(static function ($class) {
    // Define the base directory for your project
    $baseDir = __DIR__ . '/src/';

    // Namespace prefix for your classes
    $prefix = 'Rockschtar\\WordPress\\Soccr\\';

    // Check if the class uses the correct namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // This class does not use the specified namespace, so skip it.
    }

    // Get the relative class name (without the prefix)
    $relativeClass = substr($class, $len);

    // Convert the class name into a file path
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // Check if the file exists and include it
    if (file_exists($file)) {
        include $file;
    }
});

\Rockschtar\WordPress\Soccr\Controller\PluginController::init();
