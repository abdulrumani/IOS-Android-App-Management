<?php
/**
 * REST API Endpoints View
 * Displays a list of available REST API endpoints provided by the plugin.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $settings ) && class_exists( 'App_Management_Settings' ) ) {
    $settings = App_Management_Settings::get_all_settings();
} elseif ( ! isset( $settings ) ) {
    $settings = array();
}

$api_namespace = 'iaam/v1'; 

$endpoints = array(
    array(
        'name'        => __('Get All Plugin Settings', 'iaam'),
        'route'       => '/settings',
        'method'      => 'GET',
        'description' => __('Retrieves all plugin settings (Ad IDs, App Settings, Deep Link configs, etc.). Sensitive data like FCM server key is omitted. Provides `features_enabled.push_notifications`.', 'iaam'),
        'params'      => __('None', 'iaam'),
        'permission'  => __('Public (Secure with API Key in production)', 'iaam'),
    ),
    array(
        'name'        => __('Subscribe to Keyword Notification', 'iaam'),
        'route'       => '/subscribe-keyword',
        'method'      => 'POST',
        'description' => __('Allows a device to subscribe to notifications for a specific keyword.', 'iaam'),
        'params'      => __('Body (form-data or JSON): `device_token` (string, required), `keyword` (string, required, max 100 chars)', 'iaam'),
        'permission'  => __('Public (Secure with API Key)', 'iaam'),
    ),
    array(
        'name'        => __('Unsubscribe from Keyword Notification', 'iaam'),
        'route'       => '/unsubscribe-keyword',
        'method'      => 'POST',
        'description' => __('Allows a device to unsubscribe from notifications for a specific keyword.', 'iaam'),
        'params'      => __('Body (form-data or JSON): `device_token` (string, required), `keyword` (string, required, max 100 chars)', 'iaam'),
        'permission'  => __('Public (Secure with API Key)', 'iaam'),
    ),
    array(
        'name'        => __('Get Subscribed Keywords', 'iaam'),
        'route'       => '/get-subscribed-keywords',
        'method'      => 'GET',
        'description' => __('Retrieves the list of keywords a specific device is subscribed to.', 'iaam'),
        'params'      => __('Query Parameters: `?device_token=YOUR_TOKEN` (string, required)', 'iaam'),
        'permission'  => __('Public (Secure with API Key)', 'iaam'),
    ),
    array(
        'name'        => __('Get Page by Slug', 'iaam'),
        'route'       => '/page/{page_slug}', // Placeholder for display
        'dynamic_route_example' => '/page/privacy-policy', // Example for copy button
        'method'      => 'GET',
        'description' => __('Retrieves the content of a specific WordPress page by its slug (e.g., "privacy-policy", "contact-us"). The content is processed with `the_content` filter.', 'iaam'),
        'params'      => __('URL Path: Replace `{page_slug}` with the actual page slug (e.g., `privacy-policy`).', 'iaam'),
        'permission'  => __('Public (Secure with API Key)', 'iaam'),
    ),
    array(
        'name'        => __('Get WordPress Posts (WP Default)', 'iaam'),
        'route'       => '/wp/v2/posts',
        'method'      => 'GET',
        'description' => __('Retrieves WordPress posts. Supports various query parameters like `per_page`, `page`, `categories`, `search`, `_embed` etc.', 'iaam'),
        'params'      => __('Various WP REST API query parameters.', 'iaam'),
        'permission'  => __('Public (or as per WP settings)', 'iaam'),
    ),
    array(
        'name'        => __('Get WordPress Categories (WP Default)', 'iaam'),
        'route'       => '/wp/v2/categories',
        'method'      => 'GET',
        'description' => __('Retrieves WordPress categories. Supports query parameters like `per_page`, `search`, etc.', 'iaam'),
        'params'      => __('Various WP REST API query parameters.', 'iaam'),
        'permission'  => __('Public (or as per WP settings)', 'iaam'),
    ),
);
?>
<div class="iaam-settings-section">
    <h3><?php esc_html_e('REST API Endpoints', 'iaam'); ?></h3>
    <p>
        <?php esc_html_e('Base URL for custom plugin endpoints:', 'iaam'); ?>
        <code><?php echo esc_url( get_rest_url( null, $api_namespace ) ); ?>/</code>
        <br>
        <?php esc_html_e('For example, to get settings, the full URL would be:', 'iaam'); ?>
        <code><?php echo esc_url( get_rest_url( null, $api_namespace . '/settings' ) ); ?></code>
    </p>
    <p>
        <?php esc_html_e('For WordPress default endpoints like /wp/v2/posts, the base URL is:', 'iaam'); ?>
        <code><?php echo esc_url( get_rest_url() ); ?></code>
    </p>
    <p><strong><?php esc_html_e('Important Note on API Security:', 'iaam'); ?></strong> <?php esc_html_e('For production, secure endpoints using API keys or other authentication methods.', 'iaam'); ?></p>

    <table class="wp-list-table widefat striped iaam-rest-api-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Name', 'iaam'); ?></th>
                <th><?php esc_html_e('Endpoint Route', 'iaam'); ?></th>
                <th><?php esc_html_e('Method', 'iaam'); ?></th>
                <th><?php esc_html_e('Description', 'iaam'); ?></th>
                <th><?php esc_html_e('Parameters', 'iaam'); ?></th>
                <th><?php esc_html_e('Permission', 'iaam'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $endpoints as $endpoint ) : ?>
                <?php
                    $is_wp_default = strpos( $endpoint['route'], '/wp/v2/' ) === 0;
                    $base_url = $is_wp_default ? get_rest_url() : get_rest_url( null, $api_namespace . '/' );
                    
                    $display_route = $is_wp_default ? trim($endpoint['route'], '/') : trim($endpoint['route'], '/');
                    $full_url_for_copy = $base_url . (isset($endpoint['dynamic_route_example']) ? trim($endpoint['dynamic_route_example'], '/') : $display_route);
                ?>
                <tr>
                    <td><?php echo esc_html( $endpoint['name'] ); ?></td>
                    <td>
                        <code><?php echo esc_html( ($is_wp_default ? '' : $api_namespace . '/') . $display_route ); ?></code>
                        <button class="button button-secondary copy-url-button"
                                data-url="<?php echo esc_attr( $full_url_for_copy ); ?>"
                                data-copied-text="<?php esc_attr_e('Copied!', 'iaam'); ?>">
                            <?php esc_html_e('Copy URL', 'iaam'); ?>
                        </button>
                        <?php if (isset($endpoint['dynamic_route_example'])): ?>
                            <br><small>(<?php esc_html_e('Example for copy button uses:', 'iaam'); ?> <code><?php echo esc_html(trim($endpoint['dynamic_route_example'], '/')); ?></code>)</small>
                        <?php endif; ?>
                    </td>
                    <td><span class="http-method http-method-<?php echo esc_attr( strtolower( $endpoint['method'] ) ); ?>"><?php echo esc_html( $endpoint['method'] ); ?></span></td>
                    <td><?php echo wp_kses_post( $endpoint['description'] ); ?></td>
                    <td><?php echo isset($endpoint['params']) ? wp_kses_post( $endpoint['params'] ) : '&mdash;'; ?></td>
                    <td><?php echo esc_html( $endpoint['permission'] ); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
