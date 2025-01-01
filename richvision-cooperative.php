<?php
/**
 * Plugin Name: RichVision Cooperative
 * Plugin URI:  https://example.com/
 * Description: A comprehensive cooperative management plugin for WordPress.
 * Version:     1.0.0
 * Author:      Your Name/Organization
 * Author URI:  https://example.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: richvision-cooperative
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Plugin Constants
 */
define('RICHVISION_COOPERATIVE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RICHVISION_COOPERATIVE_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Activation Hook
 */
require_once RICHVISION_COOPERATIVE_PLUGIN_DIR . 'includes/class-richvision-cooperative-activator.php';
register_activation_hook( __FILE__, [ 'RichVision_Cooperative_Activator', 'activate' ] );

/**
 * Deactivation Hook
 */
require_once RICHVISION_COOPERATIVE_PLUGIN_DIR . 'includes/class-richvision-cooperative-deactivator.php';
register_deactivation_hook( __FILE__, [ 'RichVision_Cooperative_Deactivator', 'deactivate' ] );


/**
 * Core plugin class
 */
require_once RICHVISION_COOPERATIVE_PLUGIN_DIR . 'includes/class-richvision-cooperative.php';

// Instantiate the main plugin class.
if ( class_exists( 'RichVision_Cooperative' ) ) {
	new RichVision_Cooperative();
}

/**
 * Helper function to load files
 */
function load_files($dir){
  if ( ! is_dir( $dir ) ){
		return;
	}

    $files = scandir( $dir );

	foreach ( $files as $file ) {
		if ( in_array( $file, ['.', '..'] ) ) {
			continue;
		}

		if ( is_dir( $dir . '/' . $file ) ){
			load_files( $dir . '/' . $file );
        }
        elseif ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' ) {
            require_once $dir . '/' . $file;
        }
	}
}
load_files(RICHVISION_COOPERATIVE_PLUGIN_DIR . 'includes');
