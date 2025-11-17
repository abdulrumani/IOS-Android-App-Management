<?php
/**
 * Uninstall script for iOS & Android App Management Plugin.
 * This script is executed when the plugin is deleted (uninstalled) through the WordPress admin.
 *
 * @package    iOS_Android_App_Management
 * @author     Your Name <email@example.com>
 */

// اگر WP_UNINSTALL_PLUGIN مستقل (constant) سیٹ نہیں ہے، تو اسکرپٹ کو روک دیں
// If uninstall is not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// --- پلگ ان کی سیٹنگز کو حذف کریں ---
$option_name = 'iaam_settings';

// سنگل سائٹ انسٹالیشن کے لیے
delete_option( $option_name );

// ملٹی سائٹ انسٹالیشن کے لیے (اگر آپ کا پلگ ان نیٹ ورک وائیڈ فعال ہو سکتا ہے)
if ( is_multisite() ) {
    delete_site_option( $option_name );

    // اگر آپ نے ہر سائٹ کے لیے الگ الگ آپشنز محفوظ کیے ہوں تو انہیں بھی لوپ کے ذریعے حذف کریں
    // global $wpdb;
    // $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    // $original_blog_id = get_current_blog_id();
    // foreach ( $blog_ids as $blog_id ) {
    //     switch_to_blog( $blog_id );
    //     delete_option( $option_name );
    // }
    // switch_to_blog( $original_blog_id );
}


// --- کسٹم ڈیٹا بیس ٹیبلز کو حذف کریں ---
global $wpdb;

// مطلوبہ الفاظ کی سبسکرپشنز والی ٹیبل کا نام
$keyword_subscriptions_table_name = $wpdb->prefix . 'iaam_keyword_subscriptions';

// SQL کمانڈ ٹیبل کو حذف کرنے کے لیے
$sql_drop_keyword_table = "DROP TABLE IF EXISTS {$keyword_subscriptions_table_name};";

// SQL کمانڈ چلائیں
$wpdb->query( $sql_drop_keyword_table );


// --- دیگر ڈیٹا کو صاف کریں (اگر کوئی ہو) ---
// مثال کے طور پر، اگر آپ نے کوئی کسٹم یوزر رولز یا کیپبیلیٹیز شامل کی ہوں
// یا کوئی ٹرانزینٹس (transients) سیٹ کیے ہوں

// مثال کے طور پر ٹرانزینٹ کو حذف کرنا:
// delete_transient( 'iaam_some_cached_data' );

// پلگ ان کے ایکٹیویشن پر بنائے گئے کسی بھی فولڈر یا فائل کو حذف کرنے کی منطق (اگر ضرورت ہو)
// عام طور پر اس کی سفارش نہیں کی جاتی جب تک کہ بالکل ضروری نہ ہو اور احتیاط سے کیا جائے۔

?>
