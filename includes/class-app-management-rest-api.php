<?php
/**
 * Handles the registration and callbacks for the plugin's REST API endpoints.
 *
 * @package    iOS_Android_App_Management
 * @subpackage iOS_Android_App_Management/includes
 * @author     Your Name <email@example.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class App_Management_Rest_Api {

    /**
     * The namespace for the plugin's REST API.
     * @var string
     */
    protected $namespace = 'iaam/v1'; // iOS Android App Management version 1

    /**
     * Constructor.
     */
    public function __construct() {
        // Ensure App_Management_Settings class is available if needed by callbacks
        if ( ! class_exists( 'App_Management_Settings' ) && file_exists(IAAM_PLUGIN_DIR . 'includes/class-app-management-settings.php') ) {
            require_once IAAM_PLUGIN_DIR . 'includes/class-app-management-settings.php';
        }
        add_action( 'rest_api_init', array( $this, 'register_routes' ), 20 ); // Using a slightly higher priority
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        // error_log('IAAM DEBUG: register_routes function CALLED.'); // For debugging

        // Endpoint: Get all settings
        register_rest_route(
            $this->namespace,
            '/settings',
            array(
                'methods'             => WP_REST_Server::READABLE, // GET
                'callback'            => array( $this, 'get_all_settings_callback' ),
                'permission_callback' => array( $this, 'public_permission_check' ), // Consider making this more secure
                'args'                => array(), // No specific args needed for GET all settings
            )
        );

        // Endpoint: Subscribe to keyword notification
        register_rest_route(
            $this->namespace,
            '/subscribe-keyword',
            array(
                'methods'             => WP_REST_Server::CREATABLE, // POST
                'callback'            => array( $this, 'subscribe_keyword_callback' ),
                'permission_callback' => array( $this, 'public_permission_check' ),
                'args'                => array(
                    'device_token' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'description'       => esc_html__( 'Firebase device token.', 'iaam' ),
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => function( $param, $request, $key ) {
                            return ! empty( $param );
                        },
                    ),
                    'keyword'      => array(
                        'required'          => true,
                        'type'              => 'string',
                        'description'       => esc_html__( 'Keyword to subscribe to.', 'iaam' ),
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => function( $param, $request, $key ) {
                            return ! empty( $param ) && strlen( $param ) <= 100; // Example length limit
                        },
                    ),
                ),
            )
        );

        // Endpoint: Unsubscribe from keyword notification
        register_rest_route(
            $this->namespace,
            '/unsubscribe-keyword',
            array(
                'methods'             => WP_REST_Server::CREATABLE, // POST (or WP_REST_Server::DELETABLE if preferred)
                'callback'            => array( $this, 'unsubscribe_keyword_callback' ),
                'permission_callback' => array( $this, 'public_permission_check' ),
                'args'                => array(
                    'device_token' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                         'validate_callback' => function( $param, $request, $key ) {
                            return ! empty( $param );
                        },
                    ),
                    'keyword'      => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                         'validate_callback' => function( $param, $request, $key ) {
                            return ! empty( $param ) && strlen( $param ) <= 100;
                        },
                    ),
                ),
            )
        );

        // Endpoint: Get subscribed keywords for a device
        register_rest_route(
            $this->namespace,
            '/get-subscribed-keywords',
            array(
                'methods'             => WP_REST_Server::READABLE, // GET
                'callback'            => array( $this, 'get_subscribed_keywords_callback' ),
                'permission_callback' => array( $this, 'public_permission_check' ),
                'args'                => array(
                    'device_token' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'description'       => esc_html__( 'Firebase device token (as GET parameter).', 'iaam' ),
                        'sanitize_callback' => 'sanitize_text_field',
                         'validate_callback' => function( $param, $request, $key ) {
                            return ! empty( $param );
                        },
                    ),
                ),
            )
        );

        // ** نیا اینڈ پوائنٹ: ایک مخصوص پیج کا مواد سلگ کے ذریعے حاصل کریں **
        register_rest_route(
            $this->namespace,
            //  '(?P<slug>[a-zA-Z0-9_%-]+)' ایک ریجیکس ہے جو پیج سلگ کو میچ کرتا ہے
            //  یہاں % بھی شامل کیا گیا ہے تاکہ انکوڈڈ سلگز بھی کام کر سکیں
            '/page/(?P<slug>[a-zA-Z0-9_%~.-]+)', // Updated regex for more slug characters
            array(
                'methods'             => WP_REST_Server::READABLE, // GET
                'callback'            => array( $this, 'get_page_by_slug_callback' ),
                'permission_callback' => array( $this, 'public_permission_check' ),
                'args'                => array(
                    'slug' => array(
                        'required'    => true,
                        'type'        => 'string',
                        'description' => esc_html__( 'The slug of the page to retrieve.', 'iaam' ),
                        'sanitize_callback' => 'sanitize_title', // sanitize_title is good for slugs
                        'validate_callback' => function( $param, $request, $key ) {
                            return ! empty( $param );
                        }
                    ),
                ),
            )
        );
    }

    /**
     * Permission check for public endpoints.
     * For production, consider implementing API key or other authentication.
     */
    public function public_permission_check( WP_REST_Request $request ) {
        // TODO: Implement a proper authentication method for production.
        // For example, check for a secret API key in headers or as a parameter.
        // $api_key_setting = App_Management_Settings::get_setting('api_settings/secret_key');
        // $request_api_key = $request->get_header('X-APP-API-KEY');
        // if (empty($api_key_setting) || $request_api_key !== $api_key_setting) {
        //     return new WP_Error('rest_forbidden', esc_html__('Invalid API Key.', 'iaam'), array('status' => 401));
        // }
        return true; 
    }

    /**
     * Callback to get all plugin settings.
     */
    public function get_all_settings_callback( WP_REST_Request $request ) {
        $all_settings = App_Management_Settings::get_all_settings();
        
        // Create a copy to modify for the response
        $app_safe_settings = $all_settings; 
        
        // Remove sensitive data like FCM server key before sending to app
        if (isset($app_safe_settings['settings']['firebase_analytics']['fcm_server_key'])) {
            unset($app_safe_settings['settings']['firebase_analytics']['fcm_server_key']);
        }
        // Add a flag indicating if push notifications can be sent (based on FCM key presence)
        $app_safe_settings['features_enabled']['push_notifications'] = !empty($all_settings['settings']['firebase_analytics']['fcm_server_key']);

        // Add more feature flags if needed
        // $app_safe_settings['features_enabled']['ads_globally_enabled'] = $all_settings['ads_settings']['global_ads_control']['enable_ads'] ?? false;

        return new WP_REST_Response( $app_safe_settings, 200 );
    }

    /**
     * Callback to subscribe to keyword notifications.
     */
    public function subscribe_keyword_callback( WP_REST_Request $request ) {
        global $wpdb;
        $device_token = $request->get_param( 'device_token' );
        $keyword      = $request->get_param( 'keyword' );

        // Validation is handled by 'args', but an extra check here is fine.
        if ( empty( $device_token ) || empty( $keyword ) ) {
            return new WP_Error( 'missing_parameters', esc_html__( 'Device token and keyword are required.', 'iaam' ), array( 'status' => 400 ) );
        }

        $table_name = $wpdb->prefix . 'iaam_keyword_subscriptions';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'device_token' => $device_token,
                'keyword'      => $keyword,
            ),
            array( '%s', '%s' )
        );

        if ( false === $result ) {
            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE device_token = %s AND keyword = %s", $device_token, $keyword ) );
            if ($exists) {
                 return new WP_REST_Response( array( 'success' => true, 'message' => esc_html__( 'Already subscribed to this keyword.', 'iaam' ) ), 200 );
            }
            return new WP_Error( 'db_error_subscribe', esc_html__( 'Could not subscribe. Database error.', 'iaam' ), array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array( 'success' => true, 'message' => esc_html__( 'Successfully subscribed to keyword.', 'iaam' ) ), 201 );
    }

    /**
     * Callback to unsubscribe from keyword notifications.
     */
    public function unsubscribe_keyword_callback( WP_REST_Request $request ) {
        global $wpdb;
        $device_token = $request->get_param( 'device_token' );
        $keyword      = $request->get_param( 'keyword' );

        if ( empty( $device_token ) || empty( $keyword ) ) {
            return new WP_Error( 'missing_parameters', esc_html__( 'Device token and keyword are required.', 'iaam' ), array( 'status' => 400 ) );
        }

        $table_name = $wpdb->prefix . 'iaam_keyword_subscriptions';
        $deleted_rows = $wpdb->delete(
            $table_name,
            array( 'device_token' => $device_token, 'keyword' => $keyword ),
            array( '%s', '%s' )
        );

        if ( false === $deleted_rows ) {
            return new WP_Error( 'db_error_unsubscribe', esc_html__( 'Could not unsubscribe. Database error.', 'iaam' ), array( 'status' => 500 ) );
        }
        if ( 0 === $deleted_rows ) {
            return new WP_REST_Response( array( 'success' => true, 'message' => esc_html__( 'Not currently subscribed or already unsubscribed.', 'iaam' ) ), 200 );
        }
        return new WP_REST_Response( array( 'success' => true, 'message' => esc_html__( 'Successfully unsubscribed from keyword.', 'iaam' ) ), 200 );
    }

    /**
     * Callback to get subscribed keywords for a device.
     */
    public function get_subscribed_keywords_callback( WP_REST_Request $request ) {
        global $wpdb;
        $device_token = $request->get_param( 'device_token' );

        if ( empty( $device_token ) ) {
            return new WP_Error( 'missing_device_token', esc_html__( 'Device token is required as a GET parameter.', 'iaam' ), array( 'status' => 400 ) );
        }
        $table_name = $wpdb->prefix . 'iaam_keyword_subscriptions';
        $keywords = $wpdb->get_col( $wpdb->prepare( "SELECT keyword FROM {$table_name} WHERE device_token = %s ORDER BY keyword ASC", $device_token ) );
        if ( is_wp_error( $keywords ) ) {
            return new WP_Error( 'db_error_get_keywords', esc_html__( 'Could not retrieve subscribed keywords.', 'iaam' ), array( 'status' => 500 ) );
        }
        return new WP_REST_Response( array( 'success' => true, 'keywords' => $keywords ?: array() ), 200 );
    }

    /**
     * Callback to get a specific page content by its slug.
     *
     * @param WP_REST_Request $request Full details object.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error on failure.
     */
    public function get_page_by_slug_callback( WP_REST_Request $request ) {
        $slug = $request->get_param('slug'); // Already sanitized by 'args' in register_rest_route

        if ( empty( $slug ) ) {
            return new WP_Error( 'missing_slug_param', esc_html__( 'Page slug parameter is required.', 'iaam' ), array( 'status' => 400 ) );
        }

        $page = get_page_by_path( $slug, OBJECT, 'page' ); // Use get_page_by_path for slugs

        if ( ! $page || $page->post_status !== 'publish' ) {
            return new WP_Error( 'page_not_found_or_published', esc_html__( 'Page with the specified slug not found or is not published.', 'iaam' ), array( 'status' => 404 ) );
        }

        // Prepare data to return
        $page_data = array(
            'id'      => $page->ID,
            'title'   => array(
                'rendered' => get_the_title($page) // Match WP REST API structure
            ),
            'content' => array(
                'rendered' => apply_filters('the_content', $page->post_content) // Apply content filters
            ),
            'slug'    => $page->post_name,
            'date_gmt'=> $page->post_date_gmt, // Use GMT date
            'modified_gmt' => $page->post_modified_gmt,
            'link'    => get_permalink($page),
            // آپ مزید فیلڈز شامل کر سکتے ہیں
        );

        return new WP_REST_Response( $page_data, 200 );
    }
}
?>
