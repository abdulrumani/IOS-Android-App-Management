<?php
/**
 * Settings - Firebase & Google Analytics View (Updated for FCM v1)
 *
 * @package    iOS_Android_App_Management
 * @subpackage iOS_Android_App_Management/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// $settings متغیر class-app-management-admin.php سے پاس کیا جاتا ہے
if ( ! isset( $settings ) && class_exists( 'App_Management_Settings' ) ) {
    $settings = App_Management_Settings::get_all_settings();
} elseif ( ! isset( $settings ) ) {
    $settings = array(); // Fallback
}

function iaam_get_firebase_analytics_setting_v2( $settings_array, $key, $default = '' ) {
    return isset( $settings_array['settings']['firebase_analytics'][$key] ) ? $settings_array['settings']['firebase_analytics'][$key] : $default;
}

$fcm_service_account_json = iaam_get_firebase_analytics_setting_v2( $settings, 'fcm_service_account_json' );
$project_id_from_json = '';
if ( !empty($fcm_service_account_json) ) {
    $json_data = json_decode(trim($fcm_service_account_json), true);
    if (isset($json_data['project_id'])) {
        $project_id_from_json = $json_data['project_id'];
    }
}

?>
<div class="iaam-settings-section">
    <h3><?php esc_html_e('Firebase & Google Analytics Settings', 'iaam'); ?></h3>

    <h4><?php esc_html_e('Firebase Cloud Messaging (FCM) - HTTP v1 API', 'iaam'); ?></h4>
    <p>
        <?php esc_html_e('To send push notifications, you need to provide your Firebase Project\'s Service Account JSON key. This is more secure than the legacy server key.', 'iaam'); ?>
        <a href="https://console.firebase.google.com/" target="_blank"><?php esc_html_e('Go to Firebase Console', 'iaam'); ?></a> &rarr;
        <?php esc_html_e('Project settings', 'iaam'); ?> &rarr;
        <?php esc_html_e('Service accounts', 'iaam'); ?> &rarr;
        <?php esc_html_e('Generate new private key.', 'iaam'); ?>
    </p>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="iaam_fcm_service_account_json"><?php esc_html_e('Service Account JSON', 'iaam'); ?></label>
                </th>
                <td>
                    <textarea id="iaam_fcm_service_account_json" name="iaam_settings[firebase_analytics][fcm_service_account_json]"
                              class="large-text" rows="10"
                              placeholder="<?php esc_attr_e('Paste the entire content of your Firebase service account JSON file here.', 'iaam'); ?>"><?php
                        echo esc_textarea( $fcm_service_account_json );
                    ?></textarea>
                    <p class="description">
                        <?php esc_html_e('This JSON key contains your Project ID, private key, and other necessary credentials for the new FCM HTTP v1 API.', 'iaam'); ?>
                    </p>
                    <?php if (!empty($project_id_from_json)): ?>
                        <p style="color: green;">
                            <?php esc_html_e('Detected Project ID from JSON:', 'iaam'); ?> <strong><?php echo esc_html($project_id_from_json); ?></strong>
                        </p>
                    <?php elseif (!empty($fcm_service_account_json)): ?>
                        <p style="color: red;">
                            <?php esc_html_e('Could not detect Project ID from the provided JSON. Please ensure it is valid.', 'iaam'); ?>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="iaam_firebase_project_id"><?php esc_html_e('Firebase Project ID (Optional)', 'iaam'); ?></label>
                </th>
                <td>
                    <input type="text" id="iaam_firebase_project_id" name="iaam_settings[firebase_analytics][firebase_project_id]"
                           value="<?php echo esc_attr( iaam_get_firebase_analytics_setting_v2( $settings, 'firebase_project_id', $project_id_from_json ) ); ?>"
                           class="regular-text" placeholder="<?php esc_attr_e('e.g., my-app-12345', 'iaam'); ?>">
                    <p class="description">
                        <?php esc_html_e('Your Firebase Project ID. If the Service Account JSON is valid, this can often be extracted automatically. If not, or to override, enter it here.', 'iaam'); ?>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>

    <hr>

    <h4><?php esc_html_e('Google Analytics Settings', 'iaam'); ?></h4>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="iaam_ga_id"><?php esc_html_e('Analytics ID', 'iaam'); ?></label>
                </th>
                <td>
                    <input type="text" id="iaam_ga_id" name="iaam_settings[firebase_analytics][ga_id]"
                           value="<?php echo esc_attr( iaam_get_firebase_analytics_setting_v2( $settings, 'ga_id' ) ); ?>"
                           class="regular-text" placeholder="G-XXXXXX">
                    <p class="description">
                        <?php esc_html_e('Enter your Google Analytics 4 Measurement ID (e.g., G-XXXXXX).', 'iaam'); ?>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
