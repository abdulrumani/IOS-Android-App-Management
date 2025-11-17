<?php
/**
 * Settings - Deep Link View
 *
 * @package    iOS_Android_App_Management
 * @subpackage iOS_Android_App_Management/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// $settings متغیر class-app-management-admin.php سے پاس کیا جاتا ہے
// اگر $settings یہاں دستیاب نہیں ہے (مثلاً براہ راست ٹیسٹنگ کے لیے)، تو اسے حاصل کریں:
if ( ! isset( $settings ) && class_exists( 'App_Management_Settings' ) ) {
    $settings = App_Management_Settings::get_all_settings();
} elseif ( ! isset( $settings ) ) {
    $settings = array(); // Fallback
}

// Helper function to get saved deep link setting values
// This function is specific to the structure of 'deep_link' settings
if ( ! function_exists( 'iaam_get_dl_setting' ) ) {
    function iaam_get_dl_setting( $settings_array, $key1, $key2 = null, $default = '' ) {
        if ( $key2 !== null ) { // For nested settings like enable_disable[desktop] or download_links[playstore]
            return isset( $settings_array['settings']['deep_link'][$key1][$key2] ) ? $settings_array['settings']['deep_link'][$key1][$key2] : $default;
        }
        // For direct settings like desktop_redirect_url
        return isset( $settings_array['settings']['deep_link'][$key1] ) ? $settings_array['settings']['deep_link'][$key1] : $default;
    }
}
?>
<div class="iaam-settings-section">
    <h3><?php esc_html_e('Deep Linking Settings', 'iaam'); ?></h3>
    <p><?php esc_html_e('Configure how links to your content should behave on different platforms when the app is or isn\'t installed.', 'iaam'); ?></p>

    <h4><?php esc_html_e('Deep Linking Enable/Disable', 'iaam'); ?></h4>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><?php esc_html_e('Desktop', 'iaam'); ?></th>
                <td>
                    <?php 
                    iaam_render_toggle_switch(
                        'iaam_settings[deep_link][enable_disable][desktop]',
                        (bool) iaam_get_dl_setting( $settings, 'enable_disable', 'desktop', false ),
                        '1',
                        '', // No label text next to switch
                        __('Enable deep linking for desktop browsers. If enabled, users opening a content link on desktop will be redirected to the specified "Desktop Redirect URL" below.', 'iaam')
                    );
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Android', 'iaam'); ?></th>
                <td>
                     <?php 
                    iaam_render_toggle_switch(
                        'iaam_settings[deep_link][enable_disable][android]',
                        (bool) iaam_get_dl_setting( $settings, 'enable_disable', 'android', true ), // Default true
                        '1',
                        '',
                        __('Enable deep linking for Android devices. If the app is installed, it will attempt to open the content in the app. If not, it will redirect to the Play Store/App Gallery.', 'iaam')
                    );
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('iOS', 'iaam'); ?></th>
                <td>
                    <?php 
                    iaam_render_toggle_switch(
                        'iaam_settings[deep_link][enable_disable][ios]',
                        (bool) iaam_get_dl_setting( $settings, 'enable_disable', 'ios', true ), // Default true
                        '1',
                        '',
                        __('Enable deep linking for iOS devices. If the app is installed, it will attempt to open the content in the app. If not, it will redirect to the App Store.', 'iaam')
                    );
                    ?>
                </td>
            </tr>
        </tbody>
    </table>

    <hr>

    <h4><?php esc_html_e('App Download Links', 'iaam'); ?></h4>
    <p class="description"><?php esc_html_e('These links are used when deep linking is enabled and the app is not installed on the user\'s device.', 'iaam'); ?></p>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="iaam_playstore_link"><?php esc_html_e('Play Store Link', 'iaam'); ?></label>
                </th>
                <td>
                    <input type="url" id="iaam_playstore_link" name="iaam_settings[deep_link][download_links][playstore]"
                           value="<?php echo esc_url( iaam_get_dl_setting( $settings, 'download_links', 'playstore' ) ); ?>"
                           class="large-text" placeholder="https://play.google.com/store/apps/details?id=com.example.app">
                    <p class="description"><?php esc_html_e('Enter your Google Play Store app link.', 'iaam'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="iaam_appgallery_link"><?php esc_html_e('App Gallery Link (Huawei)', 'iaam'); ?></label>
                </th>
                <td>
                    <input type="url" id="iaam_appgallery_link" name="iaam_settings[deep_link][download_links][appgallery]"
                           value="<?php echo esc_url( iaam_get_dl_setting( $settings, 'download_links', 'appgallery' ) ); ?>"
                           class="large-text" placeholder="https://appgallery.huawei.com/#/app/C100000000">
                    <p class="description"><?php esc_html_e('Enter your Huawei App Gallery app link (optional).', 'iaam'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="iaam_appstore_link"><?php esc_html_e('App Store Link (Apple)', 'iaam'); ?></label>
                </th>
                <td>
                    <input type="url" id="iaam_appstore_link" name="iaam_settings[deep_link][download_links][appstore]"
                           value="<?php echo esc_url( iaam_get_dl_setting( $settings, 'download_links', 'appstore' ) ); ?>"
                           class="large-text" placeholder="https://apps.apple.com/app/your-app-name/id0000000000">
                    <p class="description"><?php esc_html_e('Enter your Apple App Store app link.', 'iaam'); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <hr>

    <h4><?php esc_html_e('Desktop Redirect URL', 'iaam'); ?></h4>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="iaam_desktop_redirect_url"><?php esc_html_e('Desktop Page URL', 'iaam'); ?></label> </th>
                <td>
                    <input type="url" id="iaam_desktop_redirect_url" name="iaam_settings[deep_link][desktop_redirect_url]"
                           value="<?php echo esc_url( iaam_get_dl_setting( $settings, 'desktop_redirect_url', null, '' ) ); ?>"
                           class="large-text" placeholder="https://example.com/mobile-app/">
                    <p class="description"><?php esc_html_e('If "Desktop Deep Linking" is enabled, users on a desktop browser who click a content link will be redirected to this URL. This page should ideally inform users that the content is best viewed in the app and provide download links.', 'iaam'); ?></p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
