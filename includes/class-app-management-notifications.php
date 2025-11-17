<?php
/**
 * Handles sending push notifications via FCM HTTP v1 API.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

class App_Management_Notifications {

    private $service_account_json_content;
    private $firebase_project_id;
    private $access_token;
    private $token_expires_at;

    public function __construct() {
        if ( ! class_exists( 'App_Management_Settings' ) ) {
            require_once IAAM_PLUGIN_DIR . 'includes/class-app-management-settings.php';
        }
        $this->service_account_json_content = App_Management_Settings::get_setting('settings/firebase_analytics/fcm_service_account_json');
        $this->firebase_project_id = App_Management_Settings::get_setting('settings/firebase_analytics/firebase_project_id');

        if (empty($this->firebase_project_id) && !empty($this->service_account_json_content)) {
            $decoded_json = json_decode(trim($this->service_account_json_content), true);
            if (isset($decoded_json['project_id'])) {
                $this->firebase_project_id = $decoded_json['project_id'];
            }
        }
        add_action( 'publish_post', array( $this, 'handle_new_post_notification' ), 10, 2 );
    }

    /**
     * Get a valid OAuth 2.0 access token for FCM.
     * This is a simplified placeholder. A robust solution would use Google's PHP client library.
     * @return string|false The access token or false on failure.
     */
    private function get_access_token() {
        if ( !empty($this->access_token) && time() < $this->token_expires_at ) {
            return $this->access_token;
        }

        if ( empty($this->service_account_json_content) ) {
            error_log('IAAM FCM Error: Service Account JSON is not configured.');
            return false;
        }

        $service_account = json_decode(trim($this->service_account_json_content), true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($service_account['client_email']) || empty($service_account['private_key'])) {
            error_log('IAAM FCM Error: Invalid Service Account JSON.');
            return false;
        }

        $jwt_header = base64_url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        
        $now = time();
        $expiry = $now + 3600; // Token valid for 1 hour

        $jwt_claim_set = base64_url_encode(json_encode([
            'iss'   => $service_account['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $expiry,
            'iat'   => $now,
        ]));

        $signature_input = $jwt_header . '.' . $jwt_claim_set;
        $signature = '';
        // Sign using private key
        if ( !openssl_sign($signature_input, $signature, $service_account['private_key'], 'sha256') ) {
            error_log('IAAM FCM Error: Failed to sign JWT. OpenSSL error: ' . openssl_error_string());
            return false;
        }
        $jwt = $signature_input . '.' . base64_url_encode($signature);

        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'method'    => 'POST',
            'headers'   => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body'      => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            error_log('IAAM FCM Token Error (wp_remote_post): ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['access_token'])) {
            $this->access_token = $data['access_token'];
            $this->token_expires_at = $now + (isset($data['expires_in']) ? intval($data['expires_in']) - 60 : 3540); // Store with a 60s buffer
            return $this->access_token;
        } else {
            error_log('IAAM FCM Token Error: Failed to retrieve access token. Response: ' . $body);
            return false;
        }
    }


    public function send_fcm_notification( $device_tokens, $title, $body, $data = array() ) {
        if ( empty($this->service_account_json_content) || empty($this->firebase_project_id) ) {
            error_log( 'IAAM FCM Error: FCM Service Account JSON or Project ID is not configured.' );
            return new WP_Error( 'fcm_config_missing', __( 'FCM Service Account JSON or Project ID is not configured.', 'iaam' ) );
        }
        
        $access_token = $this->get_access_token();
        if (!$access_token) {
            return new WP_Error('fcm_auth_failed', __('Failed to obtain FCM access token.', 'iaam'));
        }

        if ( empty( $device_tokens ) ) {
            return new WP_Error( 'no_device_tokens', __( 'No device tokens provided.', 'iaam' ) );
        }

        $device_tokens = array_unique((array) $device_tokens);
        
        // FCM HTTP v1 API endpoint
        $fcm_url = 'https://fcm.googleapis.com/v1/projects/' . $this->firebase_project_id . '/messages:send';

        $headers = array(
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        );

        $results = array();
        $all_successful = true;

        // HTTP v1 sends to one token at a time in the 'token' field, or use 'topic' or 'condition'.
        // For multiple tokens, you need to send multiple messages or use topic messaging.
        // For simplicity here, we'll send one by one if many tokens (not ideal for large numbers).
        // A better approach for many tokens is to use the batch API if FCM supports it, or topic messaging.
        // The legacy API allowed up to 1000 tokens in 'registration_ids'. V1 is different.
        // Let's assume for now we are sending to a manageable number of tokens or will implement batching/topics later.
        // This example will send one by one if $device_tokens is an array.

        foreach ($device_tokens as $token) {
            $message_payload = array(
                'message' => array(
                    'token' => $token,
                    'notification' => array(
                        'title' => $title,
                        'body'  => $body,
                    ),
                    'data' => $data,
                     // Android specific configuration
                    'android' => [
                        'priority' => 'high', // 'normal' or 'high'
                        // 'notification' => [ // More Android specific notification options
                        //    'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // If your app uses this
                        // ]
                    ],
                    // APNS specific configuration (for iOS)
                    'apns' => [
                        'headers' => [
                            'apns-priority' => '10', // 5 or 10
                        ],
                        'payload' => [
                            'aps' => [
                                'alert' => [
                                    'title' => $title,
                                    'body' => $body,
                                ],
                                'sound' => 'default',
                                // 'badge' => 1, // Optional
                            ],
                        ],
                    ],
                )
            );

            $args = array(
                'body'    => wp_json_encode( $message_payload ),
                'headers' => $headers,
                'timeout' => 30,
            );

            $response = wp_remote_post( $fcm_url, $args );
            $current_result = array('token' => $token);

            if ( is_wp_error( $response ) ) {
                error_log( 'IAAM FCM v1 Error (wp_remote_post) for token ' . $token . ': ' . $response->get_error_message() );
                $current_result['error'] = $response->get_error_message();
                $all_successful = false;
            } else {
                $response_code = wp_remote_retrieve_response_code( $response );
                $response_body_json = wp_remote_retrieve_body( $response );
                $response_body = json_decode($response_body_json, true);
                $current_result['response_code'] = $response_code;
                $current_result['response_body'] = $response_body;

                if ( $response_code !== 200 ) {
                    error_log( 'IAAM FCM v1 Error (HTTP ' . $response_code . ') for token ' . $token . ': ' . $response_body_json );
                    $all_successful = false;
                    if (isset($response_body['error']['details'][0]['errorCode'])) {
                        $fcm_error_code = $response_body['error']['details'][0]['errorCode'];
                        if ($fcm_error_code === 'UNREGISTERED' || $fcm_error_code === 'INVALID_ARGUMENT') { // INVALID_ARGUMENT can mean bad token
                            $this->remove_device_token($token);
                        }
                    }
                } else {
                    // Successfully sent to this token
                }
            }
            $results[] = $current_result;
        } // end foreach token

        if (!$all_successful) {
             // You might want to return more detailed error info or the $results array
            return new WP_Error('fcm_v1_send_some_failed', __('Some notifications failed to send. Check error log.', 'iaam'), array('details' => $results));
        }
        return true;
    }

    // ... (handle_new_post_notification, extract_keywords_from_post, get_device_tokens_for_keywords, remove_device_token, send_manual_notification functions remain largely the same,
    // but they will now use the updated send_fcm_notification method)
    public function handle_new_post_notification( $post_id, $post ) { /* ... */ }
    public function extract_keywords_from_post( WP_Post $post ) { /* ... */ return []; } // Make public if called from admin
    public function get_device_tokens_for_keywords( array $keywords ) { /* ... */ return []; } // Make public if called from admin
    private function remove_device_token( $device_token ) { /* ... */ }
    public function send_manual_notification( $title, $body, $data = array(), $target_tokens = 'all_subscribed' ) { /* ... */ }

}

// Helper function for base64 URL encoding
if (!function_exists('base64_url_encode')) {
    function base64_url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
?>
