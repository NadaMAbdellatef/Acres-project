<?php
/**
 * Helper functions 
 * @since 1.4.5
 */
class Wordpress_CTA_Free_Utils {   
    /**
	 * Add design templates image
	 * @since 1.4.5
	 * @return void
	 */
	public static function add_design_template_images($styles) {
		$design_template_images = get_option('wordpress_cta_design_template_images', []);
        if ( !is_array($design_template_images)) {
            $design_template_images = [];
        }

        foreach ($styles as $key => $style) {
            $key = sanitize_title( $key );
            if ( wp_get_attachment_image_url(@$design_template_images[$key]) ) {
                continue;
            }

            if ( !isset($style['default_image']) || !file_exists( $style['default_image'] ) ) {
                continue;
            }

            $filename = basename($style['default_image']);
            $upload = wp_upload_bits( $filename, null, file_get_contents($style['default_image']));

            if ( $upload['error'] ) {
                return;
            }

            $attach_id = wp_insert_attachment([
                'guid' => $upload['url'],
                'post_mime_type' => $upload['type'],
                'post_title' => sanitize_file_name( $filename ),
                'post_content' => '',
                'post_status' => 'inherit'
            ], $upload['file']);

            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
            $update = wp_update_attachment_metadata( $attach_id, $attach_data );

            $design_template_images[$key] = $attach_id;
        }

        update_option('wordpress_cta_design_template_images', $design_template_images);
	}

    /**
	 * Check is pro tab
	 * @since 1.4.5
	 * @return void
	 */
	public static function pro_tab_class($action) {
        if ( has_wordpress_cta_pro() ) {
            return '';
        }

        global $wp_filter;
        if ( !isset($wp_filter[$action]) ) {
            return null;
        }

        $is_pro = true;

        $hooks_callbacks = $wp_filter[$action]->callbacks;

        foreach ($hooks_callbacks as $key => $callbacks) {
            foreach ($callbacks as $callback) {    
                if ( isset($callback['function'][0]) ) {
                    $object = $callback['function'][0];                    

                    if( !is_a($object, 'Wordpress_CTA_Pro_Placeholder') ) {
                        $is_pro = false;
                    }
                }
            }
        }

        if ( $is_pro ) {
            return 'wordpress-cta-pro-tab';
        }

        return null;
    }

    /**
	 * Get inline popup
	 * @since 1.4.5
	 * @return html
	 */
	public static function get_inline_lock($styles = []) {
        if ( !is_array($styles) ) {
            $styles = [];
        }

        $style = [];
        foreach ($styles as $key => $value) {
            $style[] = sprintf('%s: %s', $key, $value);
        } ?>
        <div class="wordpress-cta-pro-feature-lock-inline" style="<?php echo esc_attr(implode(';', $style)) ?>">
            <a class="button btn-wordpress-cta-primary" href="https://wordpressctapro.com/pricing/" target="_blank"><?php _e('Upgrade now', 'easy-sticky-sidebar') ?></a>
            <a href="https://wordpressctapro.com/" target="_blank"><?php _e('Learn more', 'easy-sticky-sidebar') ?></a>
        </div>
        <?php
    }

    /**
	 * Get dimensions CSS output
	 * @since 1.4.5
	 * @return array
	 */
    public static function get_dimensions_output($values, $dimension_text = '', $prefix = '') {
        $dimensions = self::get_dimensions_values($values);
        if ( $dimensions->empty === true ) {
            return;
        }

        $unit = $dimensions->unit;
        unset($dimensions->unit, $dimensions->empty);        
        if ( empty($dimensions) ) {
            return;
        }

        foreach ($dimensions as $key => $value) {
            $dimension = str_replace('%', $key, $dimension_text);
            if ( empty($dimension) ) {
                continue;
            }

            printf("\t%s%s: %s%s;", $prefix, $dimension, $value, $unit);            
        }
    }

    /**
	 * Get dimensions values
	 * @since 1.4.5
	 * @return array
	 */
    public static function get_dimensions_values($values) {
        $values = wp_parse_args( $values, array('top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'unit' => 'px', 'empty' => false));

        $sanitize = $values;
        unset($sanitize['unit']);

        foreach ($sanitize as $key => $value) {
            $v = trim($value);
            if ( strlen($v) === 0 ) {
                unset($sanitize[$key]);
            }
        }        

        if ( empty($sanitize) ) {
            $values['empty'] = true;
        }

        return (object) $values;
    }

    /**
	 * Get padding field
	 * @since 1.4.5
	 * @return html
	 */
	public static function get_dimensions_field($name, $values = []) {
        $values = self::get_dimensions_values($values);
        $names = array('top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'unit' => '');

        $name = trim(esc_attr($name));
        if ( !empty($name) ) {
            foreach ($names as $field_key => $field_value ) {
                $names[$field_key] = sprintf('%s[%s]', $name, $field_key);
            }
        } ?>
        <ul class="wordpress-cta-dimension-field">
            <li>
                <input type="number" name="<?php echo esc_attr($names['top']) ?>" value="<?php echo esc_attr($values->top) ?>" min="0">
                <span><?php _e('Top', 'easy-sticky-sidebar') ?></span>                
            </li>

            <li>
                <input type="number" name="<?php echo esc_attr($names['right']) ?>" value="<?php echo esc_attr($values->right) ?>" min="0">
                <span><?php _e('Right', 'easy-sticky-sidebar') ?></span>
            </li>

            <li>
                <input type="number" name="<?php echo esc_attr($names['bottom']) ?>" value="<?php echo esc_attr($values->bottom) ?>" min="0">
                <span><?php _e('Bottom', 'easy-sticky-sidebar') ?></span>
            </li>

            <li>
                <input type="number" name="<?php echo esc_attr($names['left']) ?>" value="<?php echo esc_attr($values->left) ?>" min="0">
                <span><?php _e('Left', 'easy-sticky-sidebar') ?></span>
            </li>

            <li class="input-link dashicons dashicons-admin-links"></li>

            <li><?php get_easy_sticky_sidebar_unit_input($names['unit'], $values->unit); ?></li>
        </ul>
        <?php
    }
}
