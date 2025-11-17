<?php
/**
 * Helper functions for the iOS & Android App Management plugin.
 *
 * @package    iOS_Android_App_Management
 * @subpackage iOS_Android_App_Management/includes
 * @author     Your Name <email@example.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Example Helper Function (can be removed or replaced)
 *
 * Sanitizes a multi-dimensional array.
 *
 * @since 1.0.0
 * @param array $array The array to sanitize.
 * @param array $sanitize_rules Associative array where keys match array keys and values are sanitize functions (e.g., 'sanitize_text_field').
 * @return array The sanitized array.
 */
if ( ! function_exists( 'iaam_sanitize_array_recursive' ) ) {
    function iaam_sanitize_array_recursive( $array_to_sanitize, $default_sanitize_callback = 'sanitize_text_field' ) {
        if ( ! is_array( $array_to_sanitize ) ) {
            // If it's not an array, apply the default sanitization to it directly
            if ( function_exists( $default_sanitize_callback ) ) {
                return call_user_func( $default_sanitize_callback, $array_to_sanitize );
            }
            return $array_to_sanitize; // Or handle error/return as is if no valid callback
        }

        $sanitized_array = array();
        foreach ( $array_to_sanitize as $key => $value ) {
            $sanitized_key = sanitize_key( $key ); // Sanitize the key itself

            if ( is_array( $value ) ) {
                $sanitized_array[ $sanitized_key ] = iaam_sanitize_array_recursive( $value, $default_sanitize_callback );
            } else {
                // Determine the sanitization type based on expected data or use a default
                // For simplicity, using a default callback here.
                // More complex logic could be added to choose callback based on key or data type.
                if ( is_string( $value ) ) {
                     if ( strpos( $key, 'url' ) !== false || strpos( $key, 'link' ) !== false ) {
                        $sanitized_array[ $sanitized_key ] = esc_url_raw( $value );
                    } elseif ( strpos( $key, 'email' ) !== false ) {
                        $sanitized_array[ $sanitized_key ] = sanitize_email( $value );
                    } elseif ( strpos( $key, 'message' ) !== false || strpos( $key, 'key' ) !== false && strlen($value) > 100 ) { // Assuming long keys might be multi-line
                        $sanitized_array[ $sanitized_key ] = sanitize_textarea_field( $value );
                    }
                    else {
                        $sanitized_array[ $sanitized_key ] = sanitize_text_field( $value );
                    }
                } elseif ( is_int( $value ) ) {
                    $sanitized_array[ $sanitized_key ] = intval( $value );
                } elseif ( is_float( $value ) ) {
                    $sanitized_array[ $sanitized_key ] = floatval( $value );
                } elseif ( is_bool( $value ) ) {
                    $sanitized_array[ $sanitized_key ] = (bool) $value; // Ensure it's a boolean
                }
                else {
                    // For other types, or if a specific callback is preferred
                    if ( function_exists( $default_sanitize_callback ) ) {
                        $sanitized_array[ $sanitized_key ] = call_user_func( $default_sanitize_callback, $value );
                    } else {
                        $sanitized_array[ $sanitized_key ] = $value; // Fallback
                    }
                }
            }
        }
        return $sanitized_array;
    }
}

/**
 * Helper function to render a toggle switch.
 *
 * @param string $name The name attribute for the input field.
 * @param bool   $checked Whether the switch should be checked by default.
 * @param string $value The value attribute for the input field (default '1').
 * @param string $label_text Optional label text to display next to the switch.
 * @param string $description Optional description text.
 */
if ( ! function_exists( 'iaam_render_toggle_switch' ) ) {
    function iaam_render_toggle_switch( $name, $current_value, $value = '1', $label_text = '', $description = '' ) {
        ?>
        <label class="iaam-toggle-switch" for="<?php echo esc_attr( str_replace(array('[', ']'), '_', $name) ); // Create a valid ID ?>">
            <input type="checkbox"
                   id="<?php echo esc_attr( str_replace(array('[', ']'), '_', $name) ); ?>"
                   name="<?php echo esc_attr( $name ); ?>"
                   value="<?php echo esc_attr( $value ); ?>"
                <?php checked( (bool) $current_value ); ?>>
            <span class="iaam-toggle-slider"></span>
            <?php if ( ! empty( $label_text ) ) : ?>
                <span class="iaam-toggle-label-text"><?php echo esc_html( $label_text ); ?></span>
            <?php endif; ?>
        </label>
        <?php if ( ! empty( $description ) ) : ?>
            <p class="description"><?php echo esc_html( $description ); ?></p>
        <?php endif; ?>
        <?php
    }
}

// آپ یہاں مزید مددگار فنکشنز شامل کر سکتے ہیں

?>
