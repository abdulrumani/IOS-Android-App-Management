<?php
/**
 * Ads Management - Ads Settings View
 * Contains Global Ads Control and Ads Placement settings.
 *
 * @package    iOS_Android_App_Management
 * @subpackage iOS_Android_App_Management/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// $settings متغیر class-app-management-admin.php سے پاس کیا جاتا ہے

$ads_settings_sub_tabs = array(
    'global_ads_control' => __('Global Ads Control', 'iaam'),
    'ads_placement'      => __('Ads Placement', 'iaam'),
);

$current_ads_settings_sub_tab = isset( $_GET['ads_set_sub_tab'] ) ? sanitize_key( $_GET['ads_set_sub_tab'] ) : 'global_ads_control';

$ad_networks_for_dropdown = array(
    '' => __('Select Ad Network', 'iaam'), // Default empty option
    'admob' => __('AdMob', 'iaam'),
    'google_admanager' => __('Google AdManager', 'iaam'),
    'facebook_meta_audience' => __('Facebook Meta Audience', 'iaam'),
    'unity_ads' => __('Unity Ads', 'iaam'),
    'applovin_max' => __('Applovin MAX', 'iaam'),
);

$ads_placement_screens = array(
    'home'           => __('Home Screen', 'iaam'),
    'search'         => __('Search Results Screen', 'iaam'),
    'bookmark'       => __('Bookmark List Screen', 'iaam'),
    'category'       => __('Category List Screen', 'iaam'),
    'article_detail' => __('Article Detail Screen', 'iaam'),
);

$ads_placement_types = array(
    'enable_top_banner'          => __('Enable Top Banner', 'iaam'), // For list screens
    'enable_bottom_banner'       => __('Enable Bottom Banner', 'iaam'), // For all screens
    'enable_in_list_native'      => __('Enable In-List Native Ad', 'iaam'), // For list screens
    'enable_article_header_native' => __('Enable Article Header Native Ad', 'iaam'), // For Article Detail (small native after title)
    'enable_article_in_content_native' => __('Enable Article In-Content Native Ad', 'iaam'), // For Article Detail (medium native in long articles)
);

// Helper function to get saved setting value
function iaam_get_ads_setting( $settings_array, $group, $key, $default = '' ) {
    return isset( $settings_array['ads_settings'][$group][$key] ) ? $settings_array['ads_settings'][$group][$key] : $default;
}

function iaam_get_ads_placement_setting( $settings_array, $screen, $type, $default = false ) {
    return isset( $settings_array['ads_settings']['ads_placement'][$screen][$type] ) ? (bool) $settings_array['ads_settings']['ads_placement'][$screen][$type] : $default;
}

?>
<div class="iaam-sub-nav-tab-wrapper ads-settings-sub-tabs">
    <?php foreach ( $ads_settings_sub_tabs as $tab_key => $tab_label ) : ?>
        <a href="?page=iaam-main&tab=ads_management&sub_tab=ads_settings&ads_set_sub_tab=<?php echo esc_attr( $tab_key ); ?>"
           class="nav-tab <?php echo $current_ads_settings_sub_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html( $tab_label ); ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="iaam-ads-settings-content-wrapper">
    <?php // ----- Global Ads Control ----- ?>
    <div id="iaam-ads-settings-pane-global-ads-control" class="iaam-vertical-tab-pane"> <h3><?php esc_html_e('Global Ads Control for Android & iOS', 'iaam'); ?></h3>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Ads', 'iaam'); ?></th>
                    <td>
                        <label class="iaam-toggle-switch">
                            <input type="checkbox" name="iaam_ads_settings[global_ads_control][enable_ads]" value="1"
                                <?php checked( (bool) iaam_get_ads_setting( $settings, 'global_ads_control', 'enable_ads', true ) ); ?>>
                            <span class="iaam-toggle-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e('Globally enable or disable all ads in the app.', 'iaam'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="iaam_primary_ads_network"><?php esc_html_e('Primary Ads Network', 'iaam'); ?></label></th>
                    <td>
                        <select id="iaam_primary_ads_network" name="iaam_ads_settings[global_ads_control][primary_network]">
                            <?php foreach ($ad_networks_for_dropdown as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected(iaam_get_ads_setting($settings, 'global_ads_control', 'primary_network'), $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select the primary ad network. Ads will be served from this network first.', 'iaam'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="iaam_backup_ads_network"><?php esc_html_e('Backup Ads Network', 'iaam'); ?></label></th>
                    <td>
                        <select id="iaam_backup_ads_network" name="iaam_ads_settings[global_ads_control][backup_network]">
                             <?php foreach ($ad_networks_for_dropdown as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected(iaam_get_ads_setting($settings, 'global_ads_control', 'backup_network'), $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('If the primary network fails to load an ad, the app will attempt to load from this backup network.', 'iaam'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="iaam_onclick_interstitial_threshold"><?php esc_html_e('On-Click Interstitial Threshold', 'iaam'); ?></label></th>
                    <td>
                        <input type="number" id="iaam_onclick_interstitial_threshold" name="iaam_ads_settings[global_ads_control][onclick_threshold]"
                               value="<?php echo esc_attr( iaam_get_ads_setting( $settings, 'global_ads_control', 'onclick_threshold', 3 ) ); ?>" class="small-text" min="1">
                        <p class="description"><?php esc_html_e('Show an interstitial ad after this many clicks within the app (e.g., 3 clicks).', 'iaam'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="iaam_native_ads_after_x_list"><?php esc_html_e('Native Ads After X List Items', 'iaam'); ?></label></th>
                    <td>
                        <input type="number" id="iaam_native_ads_after_x_list" name="iaam_ads_settings[global_ads_control][native_after_x_items]"
                               value="<?php echo esc_attr( iaam_get_ads_setting( $settings, 'global_ads_control', 'native_after_x_items', 3 ) ); ?>" class="small-text" min="1">
                        <p class="description"><?php esc_html_e('In list views (Home, Search, etc.), show a native ad after every X items (e.g., 3 items).', 'iaam'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php // ----- Ads Placement ----- ?>
    <div id="iaam-ads-settings-pane-ads-placement" class="iaam-vertical-tab-pane"> <h3><?php esc_html_e('Global Ads Placement Settings for Android & iOS', 'iaam'); ?></h3>
        <p class="description">
            <?php esc_html_e('Control which types of ads are allowed on specific screens. The actual display of these ads (e.g., native ad frequency, interstitial click threshold) is governed by the Global Ads Control settings above and the app\'s logic. If "Enable Ads" (globally) is off, these settings will have no effect.', 'iaam'); ?>
        </p>
        <table class="wp-list-table widefat striped iaam-ads-placement-table">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Screen', 'iaam'); ?></th>
                    <?php foreach ($ads_placement_types as $type_key => $type_label) : ?>
                        <?php if ( $type_key !== 'enable_in_list_native' && $type_key !== 'enable_article_header_native' && $type_key !== 'enable_article_in_content_native' ): // General types first ?>
                            <th scope="col"><?php echo esc_html($type_label); ?></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <th scope="col"><?php esc_html_e('Enable In-List Native Ad', 'iaam'); ?></th>
                    <th scope="col"><?php esc_html_e('Enable Article Header Native Ad', 'iaam'); ?></th>
                    <th scope="col"><?php esc_html_e('Enable Article In-Content Native Ad', 'iaam'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ads_placement_screens as $screen_key => $screen_label) : ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($screen_label); ?></th>
                        
                        <?php // Top Banner ?>
                        <td>
                            <?php if ($screen_key !== 'article_detail'): // Top banner not typical for article detail ?>
                            <label class="iaam-toggle-switch small-switch">
                                <input type="checkbox" name="iaam_ads_settings[ads_placement][<?php echo esc_attr($screen_key); ?>][enable_top_banner]" value="1"
                                    <?php checked( iaam_get_ads_placement_setting( $settings, $screen_key, 'enable_top_banner' ) ); ?>>
                                <span class="iaam-toggle-slider"></span>
                            </label>
                            <?php else: echo '&mdash;'; endif; ?>
                        </td>

                        <?php // Bottom Banner ?>
                        <td>
                             <label class="iaam-toggle-switch small-switch">
                                <input type="checkbox" name="iaam_ads_settings[ads_placement][<?php echo esc_attr($screen_key); ?>][enable_bottom_banner]" value="1"
                                    <?php checked( iaam_get_ads_placement_setting( $settings, $screen_key, 'enable_bottom_banner' ) ); ?>>
                                <span class="iaam-toggle-slider"></span>
                            </label>
                        </td>
                        
                        <?php // In-List Native (Only for list screens) ?>
                        <td>
                            <?php if ($screen_key === 'home' || $screen_key === 'search' || $screen_key === 'bookmark' || $screen_key === 'category'): ?>
                            <label class="iaam-toggle-switch small-switch">
                                <input type="checkbox" name="iaam_ads_settings[ads_placement][<?php echo esc_attr($screen_key); ?>][enable_in_list_native]" value="1"
                                    <?php checked( iaam_get_ads_placement_setting( $settings, $screen_key, 'enable_in_list_native' ) ); ?>>
                                <span class="iaam-toggle-slider"></span>
                            </label>
                            <?php else: echo '&mdash;'; endif; ?>
                        </td>

                        <?php // Article Header Native (Only for article_detail) ?>
                        <td>
                            <?php if ($screen_key === 'article_detail'): ?>
                            <label class="iaam-toggle-switch small-switch">
                                <input type="checkbox" name="iaam_ads_settings[ads_placement][<?php echo esc_attr($screen_key); ?>][enable_article_header_native]" value="1"
                                    <?php checked( iaam_get_ads_placement_setting( $settings, $screen_key, 'enable_article_header_native' ) ); ?>>
                                <span class="iaam-toggle-slider"></span>
                            </label>
                            <?php else: echo '&mdash;'; endif; ?>
                        </td>

                        <?php // Article In-Content Native (Only for article_detail) ?>
                        <td>
                            <?php if ($screen_key === 'article_detail'): ?>
                            <label class="iaam-toggle-switch small-switch">
                                <input type="checkbox" name="iaam_ads_settings[ads_placement][<?php echo esc_attr($screen_key); ?>][enable_article_in_content_native]" value="1"
                                    <?php checked( iaam_get_ads_placement_setting( $settings, $screen_key, 'enable_article_in_content_native' ) ); ?>>
                                <span class="iaam-toggle-slider"></span>
                            </label>
                            <?php else: echo '&mdash;'; endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
