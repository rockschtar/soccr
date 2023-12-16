<?php

/**
 * Plugin Name:     Soccr Reloaded
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     soccr
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Rockschtar\WordPress\Soccr
 */


use Rockschtar\WordPress\Soccr\Controller\PluginController;

define('SOCCR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SOCCR_PLUGIN_URL', plugin_dir_url(__FILE__));

spl_autoload_register(static function ($class) {
    $baseDir = __DIR__ . '/src/';
    $prefix = 'Rockschtar\\WordPress\\Soccr\\';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        include $file;
    }
});

PluginController::init();
