<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    iOS_Android_App_Management
 * @subpackage iOS_Android_App_Management/admin
 * @author     Your Name <email@example.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class App_Management_Admin {

    private $version;
    private $fcm_key_is_set = false; // Renamed from fcm_service_account_json_is_set for consistency

    public function __construct( $version ) {
        $this->version = $version;

        // Load settings class early to check FCM key
        if ( ! class_exists( 'App_Management_Settings' ) && file_exists(IAAM_PLUGIN_DIR . 'includes/class-app-management-settings.php') ) {
            require_once IAAM_PLUGIN_DIR . 'includes/class-app-management-settings.php';
        }
        if (class_exists('App_Management_Settings')) {
            // Check based on the new FCM v1 setup (service account JSON)
            $fcm_json = App_Management_Settings::get_setting('settings/firebase_analytics/fcm_service_account_json');
            $this->fcm_key_is_set = !empty($fcm_json); // If JSON is provided, consider key "set"
        }

        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );
        
        // Attempt to start session for admin notices if not already started and headers not sent
        if ( ! headers_sent() && !session_id() ) {
            @session_start(); 
        }

        add_action( 'admin_post_iaam_save_settings', array( $this, 'save_plugin_settings' ) );
        add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );

        // Add custom column to post list
        add_filter( 'manage_post_posts_columns', array( $this, 'add_notification_column' ) );
        add_action( 'manage_post_posts_custom_column', array( $this, 'display_notification_column_content' ), 10, 2 );

        // AJAX handler for sending notification
        add_action( 'wp_ajax_iaam_send_post_notification', array( $this, 'handle_ajax_send_post_notification' ) );

        // Ensure Notifications class is loaded for AJAX handler
        if ( ! class_exists( 'App_Management_Notifications' ) && file_exists(IAAM_PLUGIN_DIR . 'includes/class-app-management-notifications.php') ) {
            require_once IAAM_PLUGIN_DIR . 'includes/class-app-management-notifications.php';
        }
    }

    /**
     * Add admin menu page.
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'App Management', 'iaam' ), // Page title
            __( 'App Management', 'iaam' ), // Menu title
            'manage_options',                // Capability
            'iaam-main',                     // Menu slug
            array( $this, 'display_plugin_admin_page' ), // Callback function
            'dashicons-smartphone',          // Icon URL
            75                               // Position
        );
    }

    /**
     * Enqueue admin styles and scripts.
     */
    public function enqueue_styles_scripts( $hook_suffix ) {
        // Only load on our plugin page and edit.php (for post list button)
        if ( 'toplevel_page_iaam-main' !== $hook_suffix && 'edit.php' !== $hook_suffix ) {
            return;
        }
        if ('toplevel_page_iaam-main' === $hook_suffix) { // Styles only for our settings page
            wp_enqueue_style( 
                'iaam-admin-styles', 
                IAAM_PLUGIN_URL . 'admin/assets/css/admin-styles.css', 
                array(), 
                $this->version, 
                'all' 
            );
        }
        // Scripts for both settings page and edit.php
        wp_enqueue_script( 
            'iaam-admin-scripts', 
            IAAM_PLUGIN_URL . 'admin/assets/js/admin-scripts.js', 
            array( 'jquery' ), 
            $this->version, 
            true 
        );
        
        if ('edit.php' === $hook_suffix) {
            global $post_type;
            if ($post_type === 'post') { // Only for 'post' type
                wp_localize_script('iaam-admin-scripts', 'iaamPostList', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'confirm_message' => __('Are you sure you want to send a notification for this post?', 'iaam'),
                    'sending_message' => __('Sending...', 'iaam'),
                    'success_message' => __('Notification sent!', 'iaam'), 
                    'error_message' => __('An error occurred.', 'iaam'),
                    'fcm_key_missing_message' => __('FCM Service Account JSON is not set in plugin settings. Please set it to send notifications.', 'iaam'),
                    'fcm_key_is_set' => $this->fcm_key_is_set // Pass the correct status
                ));
            }
        }
    }

    /**
     * Display the main admin page for the plugin.
     */
    public function display_plugin_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'iaam' ) );
        }

        $active_main_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'ads_management';
        $settings = class_exists('App_Management_Settings') ? App_Management_Settings::get_all_settings() : array();
        ?>
        <div class="wrap iaam-admin-wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=iaam-main&tab=ads_management" class="nav-tab main-nav-tab" data-tab-key="ads_management"><?php esc_html_e( 'Ads Management', 'iaam' ); ?></a>
                <a href="?page=iaam-main&tab=settings" class="nav-tab main-nav-tab" data-tab-key="settings"><?php esc_html_e( 'Settings', 'iaam' ); ?></a>
                <a href="?page=iaam-main&tab=rest_api_endpoints" class="nav-tab main-nav-tab" data-tab-key="rest_api_endpoints"><?php esc_html_e( 'REST API Endpoints', 'iaam' ); ?></a>
            </h2>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="iaam_save_settings">
                <?php wp_nonce_field( 'iaam_save_settings_nonce_action', 'iaam_settings_nonce_field' ); ?>
                
                <input type="hidden" name="iaam_active_tab" value="<?php echo esc_attr( $active_main_tab ); ?>">
                <input type="hidden" name="iaam_active_sub_tab" value="<?php echo esc_attr( isset($_GET['sub_tab']) ? $_GET['sub_tab'] : '' ); ?>">
                <input type="hidden" name="iaam_active_platform_ad_type_tab" value="<?php echo esc_attr( isset($_GET['ad_type_tab']) ? $_GET['ad_type_tab'] : '' ); ?>">
                <input type="hidden" name="iaam_active_ads_settings_sub_tab" value="<?php echo esc_attr( isset($_GET['ads_set_sub_tab']) ? $_GET['ads_set_sub_tab'] : '' ); ?>">

                <div class="iaam-main-tab-content-wrapper">
                    <div id="iaam-main-content-ads-management" class="iaam-main-tab-content">
                        <?php $this->display_ads_management_content( $settings ); ?>
                    </div>
                    <div id="iaam-main-content-settings" class="iaam-main-tab-content">
                        <?php $this->display_settings_content( $settings ); ?>
                    </div>
                    <div id="iaam-main-content-rest-api-endpoints" class="iaam-main-tab-content">
                        <?php $this->display_rest_api_endpoints_content( $settings ); ?>
                    </div>
                </div>

                <?php
                if ( $active_main_tab !== 'rest_api_endpoints' ) {
                    submit_button( __( 'Save Settings', 'iaam' ) );
                }
                ?>
            </form>
        </div><?php
    }

    private function display_ads_management_content( $settings ) {
        ?>
        <div class="nav-tab-wrapper iaam-sub-nav-tab-wrapper ads-management-sub-tabs">
            <a href="?page=iaam-main&tab=ads_management&sub_tab=android" class="nav-tab sub-nav-tab" data-tab-key="android"><?php esc_html_e( 'Android', 'iaam' ); ?></a>
            <a href="?page=iaam-main&tab=ads_management&sub_tab=ios" class="nav-tab sub-nav-tab" data-tab-key="ios"><?php esc_html_e( 'iOS', 'iaam' ); ?></a>
            <a href="?page=iaam-main&tab=ads_management&sub_tab=ads_settings" class="nav-tab sub-nav-tab" data-tab-key="ads_settings"><?php esc_html_e( 'Ads Settings', 'iaam' ); ?></a>
        </div>
        <div class="iaam-sub-tab-content-wrapper">
            <div id="iaam-sub-content-ads-management-android" class="iaam-sub-tab-content">
                <?php include IAAM_PLUGIN_DIR . 'admin/views/ads-management-android-view.php'; ?>
            </div>
            <div id="iaam-sub-content-ads-management-ios" class="iaam-sub-tab-content">
                 <?php include IAAM_PLUGIN_DIR . 'admin/views/ads-management-ios-view.php'; ?>
            </div>
            <div id="iaam-sub-content-ads-management-ads-settings" class="iaam-sub-tab-content">
                 <?php include IAAM_PLUGIN_DIR . 'admin/views/ads-management-settings-view.php'; ?>
            </div>
        </div>
        <?php
    }

    private function display_settings_content( $settings ) {
        ?>
        <div class="nav-tab-wrapper iaam-sub-nav-tab-wrapper settings-sub-tabs">
            <a href="?page=iaam-main&tab=settings&sub_tab=firebase_analytics" class="nav-tab sub-nav-tab" data-tab-key="firebase_analytics"><?php esc_html_e( 'Firebase & Google Analytics', 'iaam' ); ?></a>
            <a href="?page=iaam-main&tab=settings&sub_tab=deep_link" class="nav-tab sub-nav-tab" data-tab-key="deep_link"><?php esc_html_e( 'Deep Link', 'iaam' ); ?></a>
            <a href="?page=iaam-main&tab=settings&sub_tab=app_settings" class="nav-tab sub-nav-tab" data-tab-key="app_settings"><?php esc_html_e( 'App Settings', 'iaam' ); ?></a>
        </div>
        <div class="iaam-sub-tab-content-wrapper">
            <div id="iaam-sub-content-settings-firebase-analytics" class="iaam-sub-tab-content">
                <?php include IAAM_PLUGIN_DIR . 'admin/views/settings-firebase-analytics-view.php'; ?>
            </div>
            <div id="iaam-sub-content-settings-deep-link" class="iaam-sub-tab-content">
                <?php include IAAM_PLUGIN_DIR . 'admin/views/settings-deep-link-view.php'; ?>
            </div>
            <div id="iaam-sub-content-settings-app-settings" class="iaam-sub-tab-content">
                <?php include IAAM_PLUGIN_DIR . 'admin/views/settings-app-settings-view.php'; ?>
            </div>
        </div>
        <?php
    }

    private function display_rest_api_endpoints_content( $settings ) {
        include IAAM_PLUGIN_DIR . 'admin/views/rest-api-endpoints-view.php';
    }

    public function save_plugin_settings() {
        if ( ! headers_sent() && !session_id() ) { @session_start(); }

        if ( ! isset( $_POST['iaam_settings_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['iaam_settings_nonce_field'] ) ), 'iaam_save_settings_nonce_action' ) ) {
            $_SESSION['iaam_admin_notice'] = __('Nonce verification failed! Settings not saved.', 'iaam');
            $_SESSION['iaam_admin_notice_type'] = 'error';
            wp_safe_redirect( $_POST['_wp_http_referer'] ?? admin_url( 'admin.php?page=iaam-main' ) );
            exit;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            $_SESSION['iaam_admin_notice'] = __('You do not have sufficient permissions to save settings.', 'iaam');
            $_SESSION['iaam_admin_notice_type'] = 'error';
            wp_safe_redirect( $_POST['_wp_http_referer'] ?? admin_url( 'admin.php?page=iaam-main' ) );
            exit;
        }

        $active_tab = isset( $_POST['iaam_active_tab'] ) ? sanitize_key( $_POST['iaam_active_tab'] ) : 'ads_management';
        $active_sub_tab = isset( $_POST['iaam_active_sub_tab'] ) ? sanitize_key( $_POST['iaam_active_sub_tab'] ) : '';
        // $active_platform_ad_type_tab, $active_ads_settings_sub_tab are for redirect only, not for deciding what to save.
        // We save based on what's submitted in $_POST.

        if ( ! class_exists( 'App_Management_Settings' ) ) { require_once IAAM_PLUGIN_DIR . 'includes/class-app-management-settings.php'; }
        if ( ! function_exists( 'iaam_sanitize_array_recursive' ) && file_exists(IAAM_PLUGIN_DIR . 'includes/functions.php') ) { require_once IAAM_PLUGIN_DIR . 'includes/functions.php'; }

        $current_settings = App_Management_Settings::get_all_settings();
        $new_settings = $current_settings;

        // Save Ads Data (Android & iOS)
        if (isset($_POST['iaam_ads']) && is_array($_POST['iaam_ads'])) {
            foreach (['android', 'ios'] as $platform) {
                if (isset($_POST['iaam_ads'][$platform]) && is_array($_POST['iaam_ads'][$platform])) {
                    if (function_exists('iaam_sanitize_array_recursive')) {
                        $new_settings['ads'][$platform] = iaam_sanitize_array_recursive($_POST['iaam_ads'][$platform]);
                    } else { // Fallback
                        foreach($_POST['iaam_ads'][$platform] as $network => $ids) {
                            if(is_array($ids)) {
                                foreach($ids as $key => $value) {
                                    $new_settings['ads'][$platform][sanitize_key($network)][sanitize_key($key)] = sanitize_text_field($value);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Save Ads Settings (Global Control & Placement)
        if (isset($_POST['iaam_ads_settings']) && is_array($_POST['iaam_ads_settings'])) {
            $posted_ads_settings = $_POST['iaam_ads_settings'];
            // Global Ads Control
            if (isset($posted_ads_settings['global_ads_control']) && is_array($posted_ads_settings['global_ads_control'])) {
                $global_posted = $posted_ads_settings['global_ads_control'];
                $new_settings['ads_settings']['global_ads_control']['enable_ads'] = isset( $global_posted['enable_ads'] ) ? true : false;
                $new_settings['ads_settings']['global_ads_control']['primary_network'] = isset($global_posted['primary_network']) ? sanitize_text_field($global_posted['primary_network']) : '';
                $new_settings['ads_settings']['global_ads_control']['backup_network'] = isset($global_posted['backup_network']) ? sanitize_text_field($global_posted['backup_network']) : '';
                $new_settings['ads_settings']['global_ads_control']['onclick_threshold'] = isset($global_posted['onclick_threshold']) ? absint($global_posted['onclick_threshold']) : 3;
                $new_settings['ads_settings']['global_ads_control']['native_after_x_items'] = isset($global_posted['native_after_x_items']) ? absint($global_posted['native_after_x_items']) : 3;
            }
            // Ads Placement
            if (isset($posted_ads_settings['ads_placement']) && is_array($posted_ads_settings['ads_placement'])) {
                 $posted_placements = $posted_ads_settings['ads_placement'];
                 $default_placements = App_Management_Settings::get_default_by_path(['ads_settings', 'ads_placement']);
                 $new_settings['ads_settings']['ads_placement'] = $default_placements; 
                 if ( is_array( $posted_placements ) ) {
                    foreach ( $default_placements as $screen_key_default => $screen_values_default ) {
                        if (isset($posted_placements[$screen_key_default]) && is_array($posted_placements[$screen_key_default])) {
                            foreach($screen_values_default as $type_key_default => $type_value_default) {
                                $is_checked = isset( $posted_placements[ $screen_key_default ][ $type_key_default ] );
                                $new_settings['ads_settings']['ads_placement'][ sanitize_key($screen_key_default) ][ sanitize_key($type_key_default) ] = $is_checked;
                            }
                        }
                    }
                }
            } else { 
                // $new_settings['ads_settings']['ads_placement'] = $current_settings['ads_settings']['ads_placement']; // Keep current
            }
        }

        // Save General Settings (Firebase, Deep Link, App Settings)
        if (isset($_POST['iaam_settings']) && is_array($_POST['iaam_settings'])) {
            $posted_general_settings = $_POST['iaam_settings'];
            // Firebase & Analytics
            if (isset($posted_general_settings['firebase_analytics']) && is_array($posted_general_settings['firebase_analytics'])) {
                $fa_posted = $posted_general_settings['firebase_analytics'];
                $json_input = isset($fa_posted['fcm_service_account_json']) ? trim($fa_posted['fcm_service_account_json']) : '';
                $decoded_json = json_decode($json_input, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded_json['project_id']) && isset($decoded_json['private_key'])) {
                    $new_settings['settings']['firebase_analytics']['fcm_service_account_json'] = $json_input;
                } else {
                    $new_settings['settings']['firebase_analytics']['fcm_service_account_json'] = '';
                    if (!empty($json_input)) $_SESSION['iaam_admin_notice_extra_error'] = __('Invalid FCM Service Account JSON provided. It was not saved.', 'iaam');
                }
                $new_settings['settings']['firebase_analytics']['firebase_project_id'] = isset( $fa_posted['firebase_project_id'] ) ? sanitize_text_field( $fa_posted['firebase_project_id'] ) : '';
                $new_settings['settings']['firebase_analytics']['ga_id'] = isset( $fa_posted['ga_id'] ) ? sanitize_text_field( $fa_posted['ga_id'] ) : '';
            }
            // Deep Link
            if (isset($posted_general_settings['deep_link']) && is_array($posted_general_settings['deep_link'])) {
                $dl_posted = $posted_general_settings['deep_link'];
                $dl_enable_posted = $dl_posted['enable_disable'] ?? array();
                $dl_links_posted = $dl_posted['download_links'] ?? array();
                $new_settings['settings']['deep_link']['enable_disable']['desktop'] = isset( $dl_enable_posted['desktop'] ) ? true : false;
                $new_settings['settings']['deep_link']['enable_disable']['android'] = isset( $dl_enable_posted['android'] ) ? true : false;
                $new_settings['settings']['deep_link']['enable_disable']['ios']     = isset( $dl_enable_posted['ios'] ) ? true : false;
                $new_settings['settings']['deep_link']['download_links']['playstore'] = isset($dl_links_posted['playstore']) ? esc_url_raw($dl_links_posted['playstore']) : '';
                $new_settings['settings']['deep_link']['download_links']['appgallery'] = isset($dl_links_posted['appgallery']) ? esc_url_raw($dl_links_posted['appgallery']) : '';
                $new_settings['settings']['deep_link']['download_links']['appstore'] = isset($dl_links_posted['appstore']) ? esc_url_raw($dl_links_posted['appstore']) : '';
                $new_settings['settings']['deep_link']['desktop_redirect_url'] = isset($dl_posted['desktop_redirect_url']) ? esc_url_raw($dl_posted['desktop_redirect_url']) : '';
            }
            // App Settings
            if (isset($posted_general_settings['app_settings']) && is_array($posted_general_settings['app_settings'])) {
                $as_posted = $posted_general_settings['app_settings'];
                $as_update_posted = $as_posted['update_popup'] ?? array();
                $as_privacy_posted = $as_posted['privacy_policy'] ?? array();
                $as_contact_posted = $as_posted['contact_details'] ?? array();
                $as_category_posted = $as_posted['category_display'] ?? array();
                $new_settings['settings']['app_settings']['update_popup']['enable'] = isset( $as_update_posted['enable'] ) ? true : false;
                $new_settings['settings']['app_settings']['update_popup']['playstore_link'] = isset($as_update_posted['playstore_link']) ? esc_url_raw($as_update_posted['playstore_link']) : '';
                $new_settings['settings']['app_settings']['update_popup']['appstore_link'] = isset($as_update_posted['appstore_link']) ? esc_url_raw($as_update_posted['appstore_link']) : '';
                $new_settings['settings']['app_settings']['update_popup']['message'] = isset($as_update_posted['message']) ? sanitize_textarea_field($as_update_posted['message']) : '';
                $new_settings['settings']['app_settings']['privacy_policy']['link'] = isset($as_privacy_posted['link']) ? esc_url_raw($as_privacy_posted['link']) : '';
                $new_settings['settings']['app_settings']['contact_details']['email'] = isset($as_contact_posted['email']) ? sanitize_email($as_contact_posted['email']) : '';
                $new_settings['settings']['app_settings']['category_display']['show_parent'] = isset( $as_category_posted['show_parent'] ) ? true : false;
                $new_settings['settings']['app_settings']['category_display']['show_child']  = isset( $as_category_posted['show_child'] ) ? true : false;
            }
        }
        
        update_option( App_Management_Settings::OPTION_NAME, $new_settings );
        
        $_SESSION['iaam_admin_notice'] = __('Settings saved successfully!', 'iaam');
        $_SESSION['iaam_admin_notice_type'] = 'success';
        if (isset($_SESSION['iaam_admin_notice_extra_error'])) {
            $_SESSION['iaam_admin_notice'] .= ' <span style="color:red;font-weight:normal;">' . $_SESSION['iaam_admin_notice_extra_error'] . '</span>';
            unset($_SESSION['iaam_admin_notice_extra_error']);
        }

        $redirect_url = admin_url( 'admin.php?page=iaam-main&tab=' . $active_tab );
        // Append all active sub-tab parameters for correct redirection
        $active_sub_tab_val = isset($_POST['iaam_active_sub_tab']) ? $_POST['iaam_active_sub_tab'] : '';
        $active_platform_ad_type_tab_val = isset($_POST['iaam_active_platform_ad_type_tab']) ? $_POST['iaam_active_platform_ad_type_tab'] : '';
        $active_ads_settings_sub_tab_val = isset($_POST['iaam_active_ads_settings_sub_tab']) ? $_POST['iaam_active_ads_settings_sub_tab'] : '';

        if ( ! empty( $active_sub_tab_val ) ) $redirect_url = add_query_arg( 'sub_tab', $active_sub_tab_val, $redirect_url );
        if ( !empty($active_platform_ad_type_tab_val) ) $redirect_url = add_query_arg( 'ad_type_tab', $active_platform_ad_type_tab_val, $redirect_url );
        if ( !empty($active_ads_settings_sub_tab_val) ) $redirect_url = add_query_arg( 'ads_set_sub_tab', $active_ads_settings_sub_tab_val, $redirect_url );
        
        wp_safe_redirect( $redirect_url );
        exit;
    }
    
    public function display_admin_notices() {
        if ( !headers_sent() && !session_id() ) {
            @session_start();
        }
        if ( isset( $_SESSION['iaam_admin_notice'] ) && ! empty( $_SESSION['iaam_admin_notice'] ) ) {
            $message = $_SESSION['iaam_admin_notice'];
            $type    = $_SESSION['iaam_admin_notice_type'] ?? 'info';
            $type_class = 'notice-' . $type;
            if ($type === 'updated' || $type === 'success') $type_class = 'notice-success';
            elseif ($type === 'error') $type_class = 'notice-error';
            else $type_class = 'notice-info';
            echo '<div class="notice is-dismissible ' . esc_attr( $type_class ) . '"><p>' . wp_kses( $message, array( 'span' => array('style' => array()) ) ) . '</p></div>';
            unset( $_SESSION['iaam_admin_notice'] );
            unset( $_SESSION['iaam_admin_notice_type'] );
        }
    }

    public function add_notification_column( $columns ) {
        $new_columns = array();
        $comments_column_exists = false;
        foreach ( $columns as $key => $title ) {
            if ( $key === 'comments' && !$comments_column_exists ) { 
                $new_columns['app_notification'] = __( 'App Notification', 'iaam' );
                $comments_column_exists = true;
            }
            $new_columns[ $key ] = $title;
        }
        if ( ! $comments_column_exists ) {
             $new_columns['app_notification'] = __( 'App Notification', 'iaam' );
        }
        return $new_columns;
    }

    public function display_notification_column_content( $column_name, $post_id ) {
        if ( 'app_notification' === $column_name ) {
            if ( get_post_type( $post_id ) === 'post' && current_user_can('publish_posts', $post_id) ) {
                $nonce = wp_create_nonce( 'iaam_send_notification_nonce_' . $post_id );
                $button_disabled = !$this->fcm_key_is_set ? 'disabled="disabled"' : '';
                $button_title = !$this->fcm_key_is_set ? esc_attr__('FCM Service Account JSON is missing.', 'iaam') : esc_attr__( 'Send push notification for this post', 'iaam' );
                ?>
                <button type="button" class="button button-small iaam-send-notification-btn"
                        data-postid="<?php echo esc_attr( $post_id ); ?>"
                        data-nonce="<?php echo esc_attr( $nonce ); ?>"
                        title="<?php echo $button_title; ?>"
                        <?php echo $button_disabled; ?>>
                    <?php esc_html_e( 'Send Notification', 'iaam' ); ?>
                </button>
                <span id="iaam-send-spinner-<?php echo esc_attr( $post_id ); ?>" class="spinner" style="float:none; margin-left:5px; vertical-align: middle; visibility: hidden;"></span>
                <span id="iaam-send-status-<?php echo esc_attr( $post_id ); ?>" style="margin-left:5px; vertical-align: middle;"></span>
                <?php if (!$this->fcm_key_is_set): ?>
                    <p style="color:red; font-size:0.9em; margin-top:5px; clear:both;"><?php esc_html_e('FCM JSON missing!', 'iaam'); ?></p>
                <?php endif; ?>
                <?php
            } else {
                echo '&mdash;';
            }
        }
    }

    public function handle_ajax_send_post_notification() {
        if (!$this->fcm_key_is_set) {
            wp_send_json_error( array( 'message' => __( 'FCM Service Account JSON is not set. Cannot send notification.', 'iaam' ) ) );
            wp_die();
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $nonce   = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

        if ( ! $post_id || ! wp_verify_nonce( $nonce, 'iaam_send_notification_nonce_' . $post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'iaam' ) ) );
        }
        if ( ! current_user_can( 'publish_posts', $post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to send notifications for this post.', 'iaam' ) ) );
        }
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'post' ) {
            wp_send_json_error( array( 'message' => __( 'Invalid post.', 'iaam' ) ) );
        }

        if ( class_exists( 'App_Management_Notifications' ) ) {
            $notifications_manager = new App_Management_Notifications();
            $post_title = wp_strip_all_tags( $post->post_title );
            $post_excerpt = wp_strip_all_tags( get_the_excerpt( $post_id ) );
            if (empty($post_excerpt)) {
                $post_excerpt = wp_trim_words(wp_strip_all_tags($post->post_content), 20, '...');
            }

            $notification_title = apply_filters('iaam_manual_post_notification_title', $post_title, $post);
            $notification_body  = apply_filters('iaam_manual_post_notification_body', $post_excerpt, $post);
            $notification_data  = apply_filters('iaam_manual_post_notification_data', array(
                'type'    => 'manual_post_notification',
                'post_id' => (string) $post_id,
                'title'   => $post_title,
                'message' => $notification_body, 
            ), $post);

            $device_tokens = array();
            if (method_exists($notifications_manager, 'extract_keywords_from_post') && method_exists($notifications_manager, 'get_device_tokens_for_keywords')) {
                $keywords_from_post = $notifications_manager->extract_keywords_from_post( $post );
                if (!empty($keywords_from_post)) {
                    $device_tokens = $notifications_manager->get_device_tokens_for_keywords( $keywords_from_post );
                }
            }
            
            if ( ! empty( $device_tokens ) ) {
                $result = $notifications_manager->send_fcm_notification( $device_tokens, $notification_title, $notification_body, $notification_data );
                if ( is_wp_error( $result ) ) {
                    wp_send_json_error( array( 'message' => $result->get_error_message() ) );
                } else {
                    wp_send_json_success( array( 'message' => sprintf(__( 'Notification sent to %d device(s)!', 'iaam' ), count($device_tokens) ) ) );
                }
            } else {
                 wp_send_json_success( array( 'message' => __( 'No devices found subscribed to this post\'s keywords.', 'iaam' ) ) );
            }
        } else {
            wp_send_json_error( array( 'message' => __( 'Notification class not found.', 'iaam' ) ) );
        }
        wp_die();
    }
}
?>
