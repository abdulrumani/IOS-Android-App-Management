<?php
/**
 * The public-facing functionality of the plugin.
 * Handles deep linking redirection.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

class App_Management_Public {

    private $version;
    private $settings;

    public function __construct( $version ) {
        $this->version = $version;

        if ( ! class_exists( 'App_Management_Settings' ) ) {
            require_once IAAM_PLUGIN_DIR . 'includes/class-app-management-settings.php';
        }
        // App_Management_Settings::get_instance(); // Ensure defaults are loaded if not already
        $this->settings = App_Management_Settings::get_all_settings();

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
    }

    public function enqueue_public_scripts() {
        $deep_link_settings = $this->settings['settings']['deep_link'] ?? App_Management_Settings::get_default_by_path(['settings', 'deep_link']);

        if ( empty( $deep_link_settings ) ) {
            return;
        }

        $desktop_enabled = $deep_link_settings['enable_disable']['desktop'] ?? false;
        $android_enabled = $deep_link_settings['enable_disable']['android'] ?? false;
        $ios_enabled     = $deep_link_settings['enable_disable']['ios'] ?? false;

        // اگر ڈیسک ٹاپ کے لیے ڈیپ لنکنگ فعال ہے، تو ہر صفحے پر اسکرپٹ لوڈ کریں
        // موبائل کے لیے، صرف سنگل پوسٹ پر (یا آپ کی ضرورت کے مطابق تبدیل کریں)
        if ( ! ( ($desktop_enabled && !wp_is_mobile()) || (is_singular('post') && ($android_enabled || $ios_enabled) && wp_is_mobile()) ) ) {
            // اگر ڈیسک ٹاپ نہیں اور ڈیسک ٹاپ ڈیپ لنکنگ فعال ہے، یا
            // اگر سنگل پوسٹ نہیں اور موبائل ڈیپ لنکنگ فعال ہے، تو کچھ نہ کریں
            // یہ شرط تھوڑی پیچیدہ ہے، اسے سادہ بھی کیا جا سکتا ہے۔
            // بنیادی مقصد: اگر ڈیسک ٹاپ ڈیپ لنکنگ فعال ہے تو ہمیشہ اسکرپٹ لوڈ ہو،
            // بصورت دیگر موبائل کے لیے صرف سنگل پوسٹ پر۔

            // سادہ شرط:
            $load_script = false;
            if ($desktop_enabled && !wp_is_mobile() ) { // wp_is_mobile() ڈیسک ٹاپ پر false دے گا
                $load_script = true;
            } elseif (is_singular('post') && ($android_enabled || $ios_enabled) && wp_is_mobile()) {
                $load_script = true;
            }

            if (!$load_script) {
                return;
            }
        }


        wp_enqueue_script(
            'iaam-public-scripts',
            IAAM_PLUGIN_URL . 'public/assets/js/public-scripts.js',
            array( 'jquery' ), 
            $this->version,
            true 
        );

        $current_post_id = is_singular() ? get_the_ID() : 0; // اگر سنگل پیج ہے تو ID، ورنہ 0
        $current_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) ); // موجودہ مکمل URL

        // ایپ کے لیے کسٹم اسکیم
        $app_custom_scheme_base = apply_filters('iaam_app_custom_scheme_base', 'yourblogapp'); 
        $app_post_specific_scheme = '';
        if (is_singular('post') && $current_post_id) {
            $app_post_specific_scheme = $app_custom_scheme_base . '://post/' . $current_post_id;
        } elseif (is_home() || is_front_page()) {
             $app_post_specific_scheme = $app_custom_scheme_base . '://home'; // ہوم پیج کے لیے مثال
        } else {
            // دیگر صفحات کے لیے آپ مزید منطق شامل کر سکتے ہیں
            // ابھی کے لیے، صرف پوسٹ یا ہوم کے لیے مخصوص اسکیم
            // ورنہ ایپ کو صرف کھولنے کی کوشش کی جا سکتی ہے
             $app_post_specific_scheme = $app_custom_scheme_base . '://open';
        }


        $localized_data = array(
            'postId'               => $current_post_id,
            'currentUrl'           => $current_url, // موجودہ صفحے کا URL
            'appScheme'            => $app_post_specific_scheme,
            'isSingularPost'       => is_singular('post'), // تاکہ JS میں بھی چیک کیا جا سکے
            'deepLinkEnabled'      => array(
                'desktop' => (bool) $desktop_enabled,
                'android' => (bool) $android_enabled,
                'ios'     => (bool) $ios_enabled,
            ),
            'redirectUrls'         => array(
                'playStore'        => esc_url( $deep_link_settings['download_links']['playstore'] ?? '' ),
                'appGallery'       => esc_url( $deep_link_settings['download_links']['appgallery'] ?? '' ),
                'appStore'         => esc_url( $deep_link_settings['download_links']['appstore'] ?? '' ),
                'desktop'          => esc_url( $deep_link_settings['desktop_redirect_url'] ?? '' ),
            ),
            'fallbackTimeout'      => apply_filters('iaam_deeplink_fallback_timeout', 2500)
        );

        wp_localize_script( 'iaam-public-scripts', 'iaamPublicData', $localized_data );
    }
}
?>
