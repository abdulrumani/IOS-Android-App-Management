<?php
/**
 * Manages plugin settings, defaults, and retrieval.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class App_Management_Settings {

    private static $instance = null;
    const OPTION_NAME = 'iaam_settings';
    private static $default_settings = array();

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::init_defaults();
        }
        return self::$instance;
    }

    private function __construct() {}

    private static function init_defaults() {
        $ad_network_keys = ['admob', 'google_admanager', 'facebook_meta_audience', 'unity_ads', 'applovin_max'];
        $platforms = ['android', 'ios'];
        $ads_structure = array();
        foreach ($platforms as $platform) {
            foreach ($ad_network_keys as $network) {
                // Initialize all possible keys to empty strings to define structure
                $ads_structure[$platform][$network] = array(
                    'app_id' => '',
                    'sdk_key' => '',
                    'game_id' => '',
                    'app_open_unit_id' => '', 
                    'banner_unit_id' => '', 
                    'interstitial_unit_id' => '', 
                    'rewarded_interstitial_unit_id' => '', 
                    'rewarded_unit_id' => '', 
                    'native_advanced_unit_id' => '', 
                    'app_open_ad_unit_id' => '', 
                    'banner_ad_unit_id' => '', 
                    'interstitial_ad_unit_id' => '', 
                    'rewarded_interstitial_ad_unit_id' => '', 
                    'rewarded_ad_unit_id' => '', 
                    'native_ad_unit_id' => '', 
                    'rewarded_video_placement_id' => '', 
                    'interstitial_placement_id' => '', 
                    'rewarded_interstitial_placement_id' => '', 
                    'native_placement_id' => '', 
                    'native_banner_placement_id' => '', 
                    'banner_placement_id' => '', 
                    'medium_rectangle_placement_id' => '', 
                    'rewarded_placement_id' => '', 
                    'rewarded_android_placement_id' => '', 
                    'interstitial_android_placement_id' => '', 
                    'banner_android_placement_id' => '', 
                    'rewarded_ios_placement_id' => '', 
                    'interstitial_ios_placement_id' => '', 
                    'banner_ios_placement_id' => '', 
                    'mrec_ad_unit_id' => '', 
                );
            }
        }

        self::$default_settings = array(
            'ads' => $ads_structure,
            'ads_settings' => array(
                'global_ads_control' => array(
                    'enable_ads'           => true,
                    'primary_network'      => 'admob',
                    'backup_network'       => '',
                    'onclick_threshold'    => 3,
                    'native_after_x_items' => 3,
                ),
                'ads_placement' => array(
                    'home' => array( 'enable_top_banner' => true, 'enable_bottom_banner' => true, 'enable_in_list_native' => true, 'enable_article_header_native' => false, 'enable_article_in_content_native' => false ),
                    'search' => array( 'enable_top_banner' => true, 'enable_bottom_banner' => true, 'enable_in_list_native' => true, 'enable_article_header_native' => false, 'enable_article_in_content_native' => false ),
                    'bookmark' => array( 'enable_top_banner' => true, 'enable_bottom_banner' => true, 'enable_in_list_native' => true, 'enable_article_header_native' => false, 'enable_article_in_content_native' => false ),
                    'category' => array( 'enable_top_banner' => true, 'enable_bottom_banner' => true, 'enable_in_list_native' => true, 'enable_article_header_native' => false, 'enable_article_in_content_native' => false ),
                    'article_detail' => array( 'enable_top_banner' => false, 'enable_bottom_banner' => true, 'enable_in_list_native' => false, 'enable_article_header_native' => true, 'enable_article_in_content_native' => true ),
                ),
            ),
            'settings' => array(
                'firebase_analytics' => array(
                    'fcm_service_account_json' => '',
                    'firebase_project_id'      => '',
                    'ga_id'                    => '',
                ),
                'deep_link' => array(
                    'enable_disable' => array( 'desktop' => false, 'android' => true, 'ios'     => true ),
                    'download_links' => array( 'playstore'  => '', 'appgallery' => '', 'appstore'   => '' ),
                    'desktop_redirect_url' => '',
                ),
                'app_settings' => array(
                    'update_popup' => array( 'enable' => true, 'playstore_link' => '', 'appstore_link'  => '', 'message' => __('A new version of our app is available! Please update to get the latest features and improvements.', 'iaam') ),
                    'privacy_policy' => array( 'link' => '' ),
                    'contact_details' => array( 'email' => '' ),
                    'category_display' => array( 'show_parent' => true, 'show_child'  => true ),
                ),
            ),
        );
    }

    public static function get_defaults() {
        if (empty(self::$default_settings)) {
            self::init_defaults();
        }
        return self::$default_settings;
    }

    public static function get_all_settings() {
        $saved_settings = get_option( self::OPTION_NAME, array() );
        $defaults = self::get_defaults(); 
        return self::array_merge_recursive_distinct( $defaults, $saved_settings );
    }

    public static function get_setting( $path, $default = null ) {
        $settings = self::get_all_settings(); 
        $current = $settings;
        if ( is_string( $path ) ) $path = explode( '/', $path );
        if ( ! is_array( $path ) ) return $default;
        foreach ( $path as $key ) {
            if ( isset( $current[ $key ] ) ) {
                $current = $current[ $key ];
            } else {
                // ** اہم تبدیلی: اگر $default null ہے تو get_default_by_path کال کریں **
                return ($default !== null) ? $default : self::get_default_by_path($path);
            }
        }
        return $current;
    }

    /**
     * Helper to get a default value by path from the static $default_settings.
     * Changed from private static to public static.
     *
     * @param array $path Path to the setting.
     * @return mixed|null The default value or null if not found.
     */
    public static function get_default_by_path(array $path) { // **تبدیلی: private سے public**
        $defaults = self::get_defaults(); 
        $current = $defaults;
        foreach ($path as $key) {
            if (isset($current[$key])) {
                $current = $current[$key];
            } else {
                return null; 
            }
        }
        return $current;
    }

    public static function array_merge_recursive_distinct( array &$array1, array &$array2 ) {
        foreach ( $array2 as $key => &$value ) {
            if ( isset( $array1[ $key ] ) && is_array( $array1[ $key ] ) && is_array( $value ) ) {
                self::array_merge_recursive_distinct( $array1[ $key ], $value );
            } else {
                $array1[ $key ] = $value;
            }
        }
        return $array1;
    }
}
?>
