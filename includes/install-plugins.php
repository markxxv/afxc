<?php

/**
 * Programmatically install and activate wordpress plugins
 * https://gist.github.com/squarestar/37fe1ff964adaddc0697dd03155cf0d0
 *
 * Usage:
 * 1. Edit the $pluginSlugs array at the beginning of this file to include the slugs of all the 
 * plugins you want to install and activate
 * 2. Upload this file to the wordpress root directory (the same directory that contains the 
 * 'wp-admin' directory).
 * 3. Navigate to <your-domain-wordpress-root>/install-wp-plugins.php (If wordpress is installed 
 * in its own sub-directory, <your-domain-wordpress-root> is that sub-directory, not the root 
 * domain of the site, e.g., example.com/wordpress/install-wp-plugins.php)
 */

$pluginSlugs = array(
    'regenerate-thumbnails',
    'autoptimize',
    'cyr-and-lat',
    'contact-form-7',
);

require_once(ABSPATH . 'wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/misc.php');
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

/* 
 * Hide the 'Activate Plugin' and other links when not using QuietSkin as these links will 
 * fail when not called from /wp-admin 
 */

echo '<style>a {display: none;}</style>';

class QuietSkin extends \WP_Upgrader_Skin
{
    public function feedback($string) { /* no output */ }
}

/**
 * Download, install and activate a plugin
 * 
 * If the plugin directory already exists, this will only try to activate the plugin
 * 
 * @param string $slug The slug of the plugin (should be the same as the plugin's directory name
 */

function sswInstallActivatePlugin($slug)
{
    $pluginDir = WP_PLUGIN_DIR . '/' . $slug;    
    /* 
     * Don't try installing plugins that already exist (wastes time downloading files that 
     * won't be used 
     */
    if (!is_dir($pluginDir)) {
        $api = plugins_api(
            'plugin_information',
            array(
                'slug' => $slug,
                'fields' => array(
                    'short_description' => false,
                    'sections' => false,
                    'requires' => false,
                    'rating' => false,
                    'ratings' => false,
                    'downloaded' => false,
                    'last_updated' => false,
                    'added' => false,
                    'tags' => false,
                    'compatibility' => false,
                    'homepage' => false,
                    'donate_link' => false,
                ),
            )
        );
        
        // Replace with new QuietSkin for no output
        $skin = new Plugin_Installer_Skin(array('api' => $api));
        $upgrader = new Plugin_Upgrader($skin);
        $install = $upgrader->install($api->download_link);
        if ($install !== true) {
            echo 'Error: Install process failed (' . $slug . '). var_dump of result follows.<br>' 
                . "\n";
            var_dump($install); // can be 'null' or WP_Error
        }
    }

    /*
     * The install results don't indicate what the main plugin file is, so we just try to
     * activate based on the slug. It may fail, in which case the plugin will have to be activated
     * manually from the admin screen.
     */

    $pluginPath = $pluginDir . '/' . $slug . '.php';
    if (file_exists($pluginPath)) {
        activate_plugin($pluginPath);
    } else {
        echo 'Error: Plugin file not activated (' . $slug . '). This probably means the main '
            . 'file\'s name does not match the slug. Check the plugins listing in wp-admin.<br>' 
            . "\n";
    }
}

foreach ($pluginSlugs as $pluginSlug) {
    sswInstallActivatePlugin($pluginSlug);
}