<?php
/**
 * Settings - App Settings View
 *
 * @package    iOS_Android_App_Management
 * @subpackage iOS_Android_App_Management/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// $settings متغیر class-app-management-admin.php سے پاس کیا جاتا ہے

// Helper function to get saved setting value specifically for this tab's structure
function iaam_get_app_setting( $settings_array, $group, $key, $default = '' ) {
    if (is_bool($default)) {
         return isset( $settings_array['settings']['app_settings'][$group][$key] ) ? (bool) $settings_array['settings']['app_settings'][$group][$key] : $default;
    }
    return isset( $settings_array['settings']['app_settings'][$group][$key] ) ? $settings_array['settings']['app_settings'][$group][$key] : $default;
}

?>
<div class="iaam-settings-section">
    <h3><?php esc_html_e('App General Settings', 'iaam'); ?></h3>

    <h4><?php esc_html_e('App Update Popup', 'iaam'); ?></h4>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><?php esc_html_e('Enable App Update Popup', 'iaam'); ?></th>
                <td>
                    <label class="iaam-toggle-switch">
                        <input type="checkbox" name="iaam_settings[app_settings][update_popup][enable]" value="1"
                            <?php checked( iaam_get_app_setting( $settings, 'update_popup', 'enable', true ) ); ?>>
                        <span class="iaam-toggle-slider"></span>
                    </label>
                    <p class="description"><?php esc_html_e('If enabled, the app can show a popup prompting users to update when a new version is available (requires app-side logic to check version against store).', 'iaam'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="iaam_update_playstore_link"><?php esc_html_e('Play Store Link (for Update)', 'iaam'); ?></label>
                </th>
                <td>
                    <input type="url" id="iaam_update_playstore_link" name="iaam_settings[app_settings][update_popup][playstore_link]"
                           value="<?php echo esc_url( iaam_get_app_setting( $settings, 'update_popup', 'playstore_link' ) ); ?>"
                           class="large-text" placeholder="https://play.google.com/store/apps/details?id=com.example.app">
                    <p class="description"><?php esc_html_e('The app will redirect to this Play Store link when the update button is tapped. Ensure this matches your Deep Linking Play Store URL if applicable.', 'iaam'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="iaam_update_appstore_link"><?php esc_html_e('App Store Link (for Update)', 'iaam'); ?></label>
                </th>
                <td>
                    <input type="url" id="iaam_update_appstore_link" name="iaam_settings[app_settings][update_popup][appstore_link]"
                           value="<?php echo esc_url( iaam_get_app_setting( $settings, 'update_popup', 'appstore_link' ) ); ?>"
                           class="large-text" placeholder="https://apps.apple.com/app/your-app-name/id0000000000">
                    <p class="description"><?php esc_html_e('The app will redirect to this App Store link for iOS updates.', 'iaam'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="iaam_update_message"><?php esc_html_e('Update Message', 'iaam'); ?></label>
                </th>
                <td>
                    <textarea id="iaam_update_message" name="iaam_settings[app_settings][update_popup][message]"
                              class="large-text" rows="3" placeholder="<?php esc_attr_e('A new version of our app is available! Please update to get the latest features and improvements.', 'iaam'); ?>"><?php
                        echo esc_textarea( iaam_get_app_setting( $settings, 'update_popup', 'message', __('A new version of our app is available! Please update to get the latest features and improvements.', 'iaam') ) );
                    ?></textarea>
                    <p class="description"><?php esc_html_e('This message will be shown in the update popup.', 'iaam'); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <hr>

    <h4><?php esc_html_e('Privacy Policy', 'iaam'); ?></h4>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="iaam_privacy_policy_link"><?php esc_html_e('Privacy Policy Link', 'iaam'); ?></label>
                </th>
                <td>
                    <input type="url" id="iaam_privacy_policy_link" name="iaam_settings[app_settings][privacy_policy][link]"
                           value="<?php echo esc_url( iaam_get_app_setting( $settings, 'privacy_policy', 'link' ) ); ?>"
                           class="large-text" placeholder="https://example.com/privacy-policy">
                    <p class="description"><?php esc_html_e('Enter the URL to your website\'s privacy policy page. This will be displayed in the app.', 'iaam'); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <hr>

    <h4><?php esc_html_e('Contact Details', 'iaam'); ?></h4>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="iaam_contact_email"><?php esc_html_e('Contact Email', 'iaam'); ?></label>
                </th>
                <td>
                    <input type="email" id="iaam_contact_email" name="iaam_settings[app_settings][contact_details][email]"
                           value="<?php echo esc_attr( iaam_get_app_setting( $settings, 'contact_details', 'email' ) ); ?>"
                           class="regular-text" placeholder="support@example.com">
                    <p class="description"><?php esc_html_e('Enter the email address for users to contact for support or inquiries.', 'iaam'); ?></p>
                </td>
            </tr>
            </tbody>
    </table>

    <hr>

    <h4><?php esc_html_e('Category Display Settings', 'iaam'); ?></h4>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><?php esc_html_e('Show Parent Categories', 'iaam'); ?></th>
                <td>
                    <label class="iaam-toggle-switch">
                        <input type="checkbox" name="iaam_settings[app_settings][category_display][show_parent]" value="1"
                            <?php checked( iaam_get_app_setting( $settings, 'category_display', 'show_parent', true ) ); ?>>
                        <span class="iaam-toggle-slider"></span>
                    </label>
                    <p class="description"><?php esc_html_e('Enable to show parent categories in the app.', 'iaam'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Show Sub/Child Categories', 'iaam'); ?></th>
                <td>
                    <label class="iaam-toggle-switch">
                        <input type="checkbox" name="iaam_settings[app_settings][category_display][show_child]" value="1"
                            <?php checked( iaam_get_app_setting( $settings, 'category_display', 'show_child', true ) ); ?>>
                        <span class="iaam-toggle-slider"></span>
                    </label>
                    <p class="description"><?php esc_html_e('Enable to show sub/child categories in the app. This usually depends on "Show Parent Categories" being enabled.', 'iaam'); ?></p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
