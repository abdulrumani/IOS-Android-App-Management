<?php
/**
 * Plugin Name:       iOS & Android App Management
 * Plugin URI:        https://example.com/plugins/ios-android-app-management/
 * Description:       Manages settings for the iOS & Android companion app, including ads, deep linking, notifications, and API configurations for a WordPress site.
 * Version:           1.0.2
 * Author:            Your Name or Company
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       iaam
 * Domain Path:       /languages
 * Requires at least: 5.2
 * Requires PHP:      7.2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 */
define( 'IAAM_VERSION', '1.0.2' ); // Updated version

/**
 * Plugin directory path.
 */
define( 'IAAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'IAAM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'IAAM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.2
 */
function iaam_load_textdomain() {
    load_plugin_textdomain(
        'iaam',
        false,
        dirname( IAAM_PLUGIN_BASENAME ) . '/languages/'
    );
}
add_action( 'init', 'iaam_load_textdomain' ); // Load textdomain on init hook


/**
 * Load helper functions.
 */
if ( file_exists( IAAM_PLUGIN_DIR . 'includes/functions.php' ) ) {
    require_once IAAM_PLUGIN_DIR . 'includes/functions.php';
}

/**
 * Load core classes.
 */
if ( file_exists( IAAM_PLUGIN_DIR . 'includes/class-app-management-settings.php' ) ) {
    require_once IAAM_PLUGIN_DIR . 'includes/class-app-management-settings.php';
}
if ( file_exists( IAAM_PLUGIN_DIR . 'admin/class-app-management-admin.php' ) ) {
    require_once IAAM_PLUGIN_DIR . 'admin/class-app-management-admin.php';
}
if ( file_exists( IAAM_PLUGIN_DIR . 'includes/class-app-management-rest-api.php' ) ) {
    require_once IAAM_PLUGIN_DIR . 'includes/class-app-management-rest-api.php';
}
if ( file_exists( IAAM_PLUGIN_DIR . 'includes/class-app-management-notifications.php' ) ) {
    require_once IAAM_PLUGIN_DIR . 'includes/class-app-management-notifications.php';
}
if ( file_exists( IAAM_PLUGIN_DIR . 'public/class-app-management-public.php' ) ) {
    require_once IAAM_PLUGIN_DIR . 'public/class-app-management-public.php';
}


/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_iaam_app_management() {
    if ( class_exists( 'App_Management_Settings' ) ) {
        App_Management_Settings::get_instance(); // Initialize settings
    }
    if ( is_admin() ) { // Admin area
        if ( class_exists( 'App_Management_Admin' ) ) {
            new App_Management_Admin( IAAM_VERSION );
        }
    } else { // Public-facing
        if ( class_exists( 'App_Management_Public' ) ) {
            new App_Management_Public( IAAM_VERSION );
        }
    }

    // REST API and Notifications should be initialized regardless of admin or public context if they hook into WordPress actions.
    if ( class_exists( 'App_Management_Rest_Api' ) ) {
        new App_Management_Rest_Api();
    }
    if ( class_exists( 'App_Management_Notifications' ) ) {
        new App_Management_Notifications();
    }
}
// Changed from plugins_loaded to init to ensure textdomain is loaded first,
// or keep on plugins_loaded if classes don't use translatable strings in constructors before init.
// Since textdomain is now loaded on 'init', other initializations can also happen on 'init' or later.
// For safety, let's move the main run function to 'init' as well.
add_action( 'init', 'run_iaam_app_management', 1 ); // Priority 1 to run after textdomain load (if textdomain also on init 0)


/**
 * Code that runs on plugin activation.
 */
function iaam_activate() {
    // Create custom database table for keyword subscriptions
    global $wpdb;
    $table_name = $wpdb->prefix . 'iaam_keyword_subscriptions';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        device_token VARCHAR(255) NOT NULL,
        keyword VARCHAR(100) NOT NULL,
        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_subscription (device_token(191), keyword)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Set default options if not already set
    // Ensure App_Management_Settings class is loaded before calling its static methods
    if ( ! class_exists( 'App_Management_Settings' ) && file_exists(IAAM_PLUGIN_DIR . 'includes/class-app-management-settings.php') ) {
        require_once IAAM_PLUGIN_DIR . 'includes/class-app-management-settings.php';
    }
    if ( class_exists('App_Management_Settings') && false === get_option( App_Management_Settings::OPTION_NAME ) ) {
        update_option( App_Management_Settings::OPTION_NAME, App_Management_Settings::get_defaults() );
    }
     // error_log('IAAM Plugin Activated and table checked/created.');
}
register_activation_hook( __FILE__, 'iaam_activate' );

/**
 * Code that runs on plugin deactivation.
 */
function iaam_deactivate() {
    // You might want to clear scheduled cron jobs here if any were added.
    // error_log('IAAM Plugin Deactivated');
}
register_deactivation_hook( __FILE__, 'iaam_deactivate' );

// uninstall.php will handle data cleanup on deletion.

?>
