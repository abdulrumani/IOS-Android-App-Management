<?php
/**
 * Ads Management - iOS View (Robust targeting)
 *
 * @package    iOS_Android_App_Management
 * @subpackage iOS_Android_App_Management/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

$platform = 'ios';
// $settings is passed from class-app-management-admin.php
if ( ! isset( $settings ) && class_exists( 'App_Management_Settings' ) ) {
    $settings = App_Management_Settings::get_all_settings();
} elseif ( ! isset( $settings ) ) {
    $settings = array(); // Fallback
}

$ad_networks = array(
    'admob' => __('AdMob', 'iaam'),
    'google_admanager' => __('Google AdManager', 'iaam'),
    'facebook_meta_audience' => __('Facebook Meta Audience', 'iaam'),
    'unity_ads' => __('Unity Ads', 'iaam'),
    'applovin_max' => __('Applovin MAX', 'iaam'),
);

$current_ad_type_tab_key = isset( $_GET['ad_type_tab'] ) ? sanitize_key( $_GET['ad_type_tab'] ) : 'admob';
?>
<div class="iaam-vertical-tabs-container ios-vertical-tabs">
    <div class="iaam-vertical-tabs-nav">
        <?php foreach ( $ad_networks as $network_key => $network_label ) : ?>
            <a href="?page=iaam-main&tab=ads_management&sub_tab=<?php echo esc_attr($platform); ?>&ad_type_tab=<?php echo esc_attr( $network_key ); ?>"
               class="nav-tab vertical-tab-link" 
               data-tab-key="<?php echo esc_attr( $network_key ); ?>">
                <?php echo esc_html( $network_label ); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="iaam-vertical-tab-content-wrapper">
        <?php
        // AdMob Fields
        $network_key_admob = 'admob';
        echo '<div id="vertical-pane-' . esc_attr($platform) . '-' . esc_attr($network_key_admob) . '" class="iaam-vertical-tab-pane" data-pane-key="' . esc_attr($network_key_admob) . '">';
        echo '<h3>' . esc_html__('AdMob', 'iaam') . ' - ' . esc_html__('iOS Settings', 'iaam') . '</h3>';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row"><label for="iaam_admob_app_id_ios">' . esc_html__('App ID', 'iaam') . '</label></th><td><input type="text" id="iaam_admob_app_id_ios" name="iaam_ads['.$platform.']['.$network_key_admob.'][app_id]" value="' . esc_attr( $settings['ads'][$platform][$network_key_admob]['app_id'] ?? '' ) . '" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_admob_app_open_ios">'.esc_html__('App Open Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_admob_app_open_ios" name="iaam_ads['.$platform.']['.$network_key_admob.'][app_open_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_admob]['app_open_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_admob_banner_ios">'.esc_html__('Banner Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_admob_banner_ios" name="iaam_ads['.$platform.']['.$network_key_admob.'][banner_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_admob]['banner_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_admob_interstitial_ios">'.esc_html__('Interstitial Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_admob_interstitial_ios" name="iaam_ads['.$platform.']['.$network_key_admob.'][interstitial_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_admob]['interstitial_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_admob_rewarded_interstitial_ios">'.esc_html__('Rewarded Interstitial Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_admob_rewarded_interstitial_ios" name="iaam_ads['.$platform.']['.$network_key_admob.'][rewarded_interstitial_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_admob]['rewarded_interstitial_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_admob_rewarded_ios">'.esc_html__('Rewarded Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_admob_rewarded_ios" name="iaam_ads['.$platform.']['.$network_key_admob.'][rewarded_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_admob]['rewarded_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_admob_native_advanced_ios">'.esc_html__('Native Advanced Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_admob_native_advanced_ios" name="iaam_ads['.$platform.']['.$network_key_admob.'][native_advanced_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_admob]['native_advanced_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '</tbody></table></div>';

        // Google AdManager Fields
        $network_key_gam = 'google_admanager';
        echo '<div id="vertical-pane-' . esc_attr($platform) . '-' . esc_attr($network_key_gam) . '" class="iaam-vertical-tab-pane" data-pane-key="' . esc_attr($network_key_gam) . '">';
        echo '<h3>' . esc_html__('Google AdManager', 'iaam') . ' - ' . esc_html__('iOS Settings', 'iaam') . '</h3>';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row"><label for="iaam_gam_app_id_ios">' . esc_html__('App ID / Ad Manager Network Code', 'iaam') . '</label></th><td><input type="text" id="iaam_gam_app_id_ios" name="iaam_ads['.$platform.']['.$network_key_gam.'][app_id]" value="' . esc_attr( $settings['ads'][$platform][$network_key_gam]['app_id'] ?? '' ) . '" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_gam_app_open_ios">'.esc_html__('App Open Ad Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_gam_app_open_ios" name="iaam_ads['.$platform.']['.$network_key_gam.'][app_open_ad_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_gam]['app_open_ad_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_gam_banner_ios">'.esc_html__('Banner Ad Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_gam_banner_ios" name="iaam_ads['.$platform.']['.$network_key_gam.'][banner_ad_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_gam]['banner_ad_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_gam_interstitial_ios">'.esc_html__('Interstitial Ad Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_gam_interstitial_ios" name="iaam_ads['.$platform.']['.$network_key_gam.'][interstitial_ad_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_gam]['interstitial_ad_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_gam_rewarded_interstitial_ios">'.esc_html__('Rewarded Interstitial Ad Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_gam_rewarded_interstitial_ios" name="iaam_ads['.$platform.']['.$network_key_gam.'][rewarded_interstitial_ad_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_gam]['rewarded_interstitial_ad_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_gam_rewarded_ios">'.esc_html__('Rewarded Ad Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_gam_rewarded_ios" name="iaam_ads['.$platform.']['.$network_key_gam.'][rewarded_ad_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_gam]['rewarded_ad_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_gam_native_advanced_ios">'.esc_html__('Native Ad Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_gam_native_advanced_ios" name="iaam_ads['.$platform.']['.$network_key_gam.'][native_ad_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_gam]['native_ad_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '</tbody></table></div>';

        // Facebook Meta Audience Fields
        $network_key_fb = 'facebook_meta_audience';
        echo '<div id="vertical-pane-' . esc_attr($platform) . '-' . esc_attr($network_key_fb) . '" class="iaam-vertical-tab-pane" data-pane-key="' . esc_attr($network_key_fb) . '">';
        echo '<h3>' . esc_html__('Facebook Meta Audience', 'iaam') . ' - ' . esc_html__('iOS Settings', 'iaam') . '</h3>';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row"><label for="iaam_fb_app_id_ios">' . esc_html__('App ID', 'iaam') . '</label></th><td><input type="text" id="iaam_fb_app_id_ios" name="iaam_ads['.$platform.']['.$network_key_fb.'][app_id]" value="' . esc_attr( $settings['ads'][$platform][$network_key_fb]['app_id'] ?? '' ) . '" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_fb_rewarded_video_ios">'.esc_html__('Rewarded Video Ads Placement ID', 'iaam').'</label></th><td><input type="text" id="iaam_fb_rewarded_video_ios" name="iaam_ads['.$platform.']['.$network_key_fb.'][rewarded_video_placement_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_fb]['rewarded_video_placement_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_fb_interstitial_ios">'.esc_html__('Interstitial Ads Placement ID', 'iaam').'</label></th><td><input type="text" id="iaam_fb_interstitial_ios" name="iaam_ads['.$platform.']['.$network_key_fb.'][interstitial_placement_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_fb]['interstitial_placement_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_fb_rewarded_interstitial_ios">'.esc_html__('Rewarded Interstitial Ads Placement ID', 'iaam').'</label></th><td><input type="text" id="iaam_fb_rewarded_interstitial_ios" name="iaam_ads['.$platform.']['.$network_key_fb.'][rewarded_interstitial_placement_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_fb]['rewarded_interstitial_placement_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_fb_native_ios">'.esc_html__('Native Ads Placement ID', 'iaam').'</label></th><td><input type="text" id="iaam_fb_native_ios" name="iaam_ads['.$platform.']['.$network_key_fb.'][native_placement_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_fb]['native_placement_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_fb_native_banner_ios">'.esc_html__('Native Banner Ads Placement ID', 'iaam').'</label></th><td><input type="text" id="iaam_fb_native_banner_ios" name="iaam_ads['.$platform.']['.$network_key_fb.'][native_banner_placement_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_fb]['native_banner_placement_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_fb_banner_ios">'.esc_html__('Banner Ads Placement ID', 'iaam').'</label></th><td><input type="text" id="iaam_fb_banner_ios" name="iaam_ads['.$platform.']['.$network_key_fb.'][banner_placement_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_fb]['banner_placement_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_fb_medium_rectangle_ios">'.esc_html__('Medium Rectangle Ads Placement ID', 'iaam').'</label></th><td><input type="text" id="iaam_fb_medium_rectangle_ios" name="iaam_ads['.$platform.']['.$network_key_fb.'][medium_rectangle_placement_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_fb]['medium_rectangle_placement_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '</tbody></table></div>';

        // Unity Ads Fields
        $network_key_unity = 'unity_ads';
        echo '<div id="vertical-pane-' . esc_attr($platform) . '-' . esc_attr($network_key_unity) . '" class="iaam-vertical-tab-pane" data-pane-key="' . esc_attr($network_key_unity) . '">';
        echo '<h3>' . esc_html__('Unity Ads', 'iaam') . ' - ' . esc_html__('iOS Settings', 'iaam') . '</h3>';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row"><label for="iaam_unity_game_id_ios">' . esc_html__('Game ID', 'iaam') . '</label></th><td><input type="text" id="iaam_unity_game_id_ios" name="iaam_ads['.$platform.']['.$network_key_unity.'][game_id]" value="' . esc_attr( $settings['ads'][$platform][$network_key_unity]['game_id'] ?? '' ) . '" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_unity_rewarded_ios_placement">'.esc_html__('Rewarded Placement ID', 'iaam').'</label></th><td><input type="text" id="iaam_unity_rewarded_ios_placement" name="iaam_ads['.$platform.']['.$network_key_unity.'][rewarded_ios_placement_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_unity]['rewarded_ios_placement_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_unity_interstitial_ios_placement">'.esc_html__('Interstitial Placement ID', 'iaam').'</label></th><td><input type="text" id="iaam_unity_interstitial_ios_placement" name="iaam_ads['.$platform.']['.$network_key_unity.'][interstitial_ios_placement_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_unity]['interstitial_ios_placement_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_unity_banner_ios_placement">'.esc_html__('Banner Placement ID', 'iaam').'</label></th><td><input type="text" id="iaam_unity_banner_ios_placement" name="iaam_ads['.$platform.']['.$network_key_unity.'][banner_ios_placement_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_unity]['banner_ios_placement_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '</tbody></table></div>';

        // Applovin MAX Fields
        $network_key_applovin = 'applovin_max';
        echo '<div id="vertical-pane-' . esc_attr($platform) . '-' . esc_attr($network_key_applovin) . '" class="iaam-vertical-tab-pane" data-pane-key="' . esc_attr($network_key_applovin) . '">';
        echo '<h3>' . esc_html__('Applovin MAX', 'iaam') . ' - ' . esc_html__('iOS Settings', 'iaam') . '</h3>';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row"><label for="iaam_applovin_sdk_key_ios">' . esc_html__('SDK Key', 'iaam') . '</label></th><td><input type="text" id="iaam_applovin_sdk_key_ios" name="iaam_ads['.$platform.']['.$network_key_applovin.'][sdk_key]" value="' . esc_attr( $settings['ads'][$platform][$network_key_applovin]['sdk_key'] ?? '' ) . '" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_applovin_rewarded_ios">'.esc_html__('Rewarded Ad Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_applovin_rewarded_ios" name="iaam_ads['.$platform.']['.$network_key_applovin.'][rewarded_ad_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_applovin]['rewarded_ad_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_applovin_interstitial_ios">'.esc_html__('Interstitial Ad Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_applovin_interstitial_ios" name="iaam_ads['.$platform.']['.$network_key_applovin.'][interstitial_ad_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_applovin]['interstitial_ad_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_applovin_banner_ios">'.esc_html__('Banner Ad Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_applovin_banner_ios" name="iaam_ads['.$platform.']['.$network_key_applovin.'][banner_ad_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_applovin]['banner_ad_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_applovin_native_ios">'.esc_html__('Native Ad Unit ID (Manual)', 'iaam').'</label></th><td><input type="text" id="iaam_applovin_native_ios" name="iaam_ads['.$platform.']['.$network_key_applovin.'][native_ad_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_applovin]['native_ad_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '<tr><th scope="row"><label for="iaam_applovin_mrec_ios">'.esc_html__('MREC Ad Unit ID', 'iaam').'</label></th><td><input type="text" id="iaam_applovin_mrec_ios" name="iaam_ads['.$platform.']['.$network_key_applovin.'][mrec_ad_unit_id]" value="'.esc_attr($settings['ads'][$platform][$network_key_applovin]['mrec_ad_unit_id'] ?? '').'" class="regular-text"></td></tr>';
        echo '</tbody></table></div>';
        ?>
    </div></div>