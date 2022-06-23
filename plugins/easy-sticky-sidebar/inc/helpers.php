<?php

/**
 * check if pro available or not
 * @since  1.3.6
 */
function has_wordpress_cta_pro() {
    return function_exists('Wordpress_CTA_Pro') && Wordpress_CTA_Pro()->is_activated;
}

/**
 * check if pro available or not
 * @since  1.4.5
 */
function is_easy_sticky_sidebar_screen() {
    $current_screen_id = get_current_screen()->id;
    $is_free_screen = strpos($current_screen_id, 'easy-sticky-sidebar') !== false;
    $is_pro_screen = strpos($current_screen_id, 'wordpress-cta-pro') !== false;
    return $is_free_screen || $is_pro_screen;
}

/**
 * check if pro available or not
 * @since  1.3.8
 */
function get_easy_sticky_sidebar_header($args = []) {
    $args = wp_parse_args( $args, apply_filters( 'wordpress_cta_header_args', [
        'title' => __('WP CTA', 'easy-sticky-sidebar'),
        'class' => ''
    ]));

    extract($args);

    if (!has_wordpress_cta_pro()) :
        $title = sprintf('<a target="_blank" href="https://wordpressctapro.com/">%s</a>', $title);
    endif; ?>
    <header class="easy-sticky-sidebar-header">
        <div class="easy-sticky-sidebar-container <?php echo $class ?>">
            <h3 class=""><?php echo $title ?></h3>

            <ul class="easy-sidebar-header-navs">
                <?php if (!has_wordpress_cta_pro()) : ?>                    
                <li><a target="_blank" href="https://wordpressctapro.com/">Get more options</a></li>
                <?php endif; ?>

                <li><a target="_blank" href="https://wordpressctapro.com/">Website</a></li>
                <li><a target="_blank" href="https://wordpressctapro.com/demos/">Demos</a></li>
                <li><a target="_blank" href="https://wordpressctapro.com/help">Help</a></li>
                <li><a target="_blank" href="https://www.facebook.com/WordPressCTAPlugin">Facebook</a></li>
                <li><a target="_blank" href="https://www.instagram.com/wordpressctapro/">Instagram</a></li>
            </ul>
        </div>
    </header>
    <?php
}

/**
 * Insert CTA Post
 * @since  1.3.8
 */
function easy_sticky_sidebar_insert($args) {
    if ( is_object($args) ) {
        $args = (array) $args;
    }
    
    if ( !is_array($args)) {
        return false;
    }
    
    $sticky_id = empty($args['id']) ? false : absint($args['id']);
    $is_new = ($sticky_id == false);
    
    $args = (array) apply_filters('easy_sticky_sidebar_args', $args, $sticky_id, $is_new);

    global $wpdb;
    $sticky_cta_columns = $wpdb->get_col("DESC {$wpdb->sticky_cta}", 0);

    $insert_format = array();

    $main_fields = $meta_fields = [];
    foreach ($args as $key => $value) {
        if ( !is_string($key) ) {
            continue;
        }

        if ( in_array($key, $sticky_cta_columns)) {
            $main_fields[$key] = $value;
            $insert_format[$key] = '%s';
            continue;
        }

        $meta_fields[$key] = $value;
    }

    $digit_fields = array('id', 'image_attachment_id', 'SSuprydp_button_option_size', 'SSuprydp_content_option_size', 'SSuprydp_action_option_size');
    foreach ($digit_fields as $dkey) {
        if ( isset($insert_format[$dkey])) {
            $insert_format[$dkey] = '%d';
        }
    }

    if ( $is_new ) {
        $wpdb->insert($wpdb->sticky_cta, $main_fields, $insert_format);
        $sticky_id = $wpdb->insert_id;
    } else {
        $wpdb->update($wpdb->sticky_cta, $main_fields, ['id' => $sticky_id], $insert_format, array( '%d' ));
    }

    if ( !$sticky_id ) {
        return false;
    }

    if ( count($meta_fields) > 0 && $wpdb->get_var("SELECT id FROM $wpdb->sticky_cta WHERE id = $sticky_id") ) {        
        foreach ($meta_fields as $meta_key => $meta_value) {

            $exists = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->sticky_cta_options WHERE option_name = '%s' AND sticky_cta_id = %d", $meta_key, $sticky_id));

            $data_format = array('%d', '%s', '%s');
            $data = ['sticky_cta_id' => $sticky_id, 'option_name' => $meta_key, 'option_value' => maybe_serialize($meta_value)];
            if ( $exists ) {
                $data['ID'] = $exists;
                array_push($data_format, '%d');
            }
            
            $wpdb->replace($wpdb->sticky_cta_options, $data, $data_format);
        }        
    }

    do_action( 'easy_sticky_sidebar_after_save', $args, $sticky_id, $is_new);

    return $sticky_id;
}

/**
 * Get Sticky CTA item
 * @since  1.3.8
 */
function get_easy_sticky_sidebar($sticky_id) {
    global $wpdb;

    $sticky_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->sticky_cta WHERE id = %d", $sticky_id));
    if ( !$sticky_data ) {
        return false;        
    }

    return new WP_Sticky_CTA_Data($sticky_data);
}

/**
 * Returns options of a cta item
 * @since  1.3.5
 */
function easy_sticky_sidebar_templates() {
    return apply_filters('easy_sticky_sidebar_templates', [
        'sticky-cta' => __('Sticky CTA', 'easy-sticky-sidebar'),
        'floating-buttons' =>  __('Floating Buttons', 'easy-sticky-sidebar'),
        'tab-cta' => __('Tab CTA', 'easy-sticky-sidebar'),
        'html' => __('HTML CTA', 'easy-sticky-sidebar'),
        'banner' => __('Banner', 'easy-sticky-sidebar'),
        'gdpr' => __('GDPR / Cookies', 'easy-sticky-sidebar'),
    ]);
}

/**
 * Returns status menu
 * @since  1.3.8
 */
function get_easy_sticky_sidebar_get_status_menu($stickycta) {    
    $statuses = [
        'live'          => __('Live', 'wordpress-cta-pro'),
        'development'   => __('Development', 'wordpress-cta-pro'),
        'off'           => __('Off', 'wordpress-cta-pro'),
    ]; ?>

    <div class="sticky-cta-status-menu" data-id="<?php echo $stickycta->id ?>">
        <input type="hidden" name="SSuprydp_development" value="<?php echo esc_attr($stickycta->SSuprydp_development); ?>">
        <label class="status-<?php echo $stickycta->SSuprydp_development; ?>"><?php echo esc_html($statuses[$stickycta->SSuprydp_development])  ?></label>
        <ul class="statuses">
            <?php foreach ($statuses as $status_key => $status_label) {
                printf('<li data-status="%s">%s</li>', $status_key, $status_label);
            } ?>
        </ul>
    </div>
    <?php
}

/**
 * Unit input
 * @since  1.4.0
 */
function get_easy_sticky_sidebar_unit_input($name, $value = 'px', $class = '') {
    if (empty($value) ) {
        $value = 'px';
    } ?>
    <div class="wpcta-unit-input <?php echo $class ?>">
        <label><input type="radio" name="<?php echo esc_attr($name) ?>" value="px" <?php checked( 'px', $value) ?>><span>px</span></label>
        <label><input type="radio" name="<?php echo esc_attr($name) ?>" value="%" <?php checked( '%', $value) ?>><span>%</span></label>
	</div>
    <?php
}

/**
 * return unique cta name
 * @since  1.3.8
 */

function get_unique_easy_sticky_sidebar_name($name, $number = false) {
    global $wpdb;
    if ( empty($name) ) {
        return $name;
    }

    $next_name = $name . ' - Copy';
    if ( $number ) {
        $next_name = $next_name . ' ' . $number;
    }

    $has_name = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->sticky_cta_options WHERE option_name = 'sidebar_name' AND option_value = '%s' LIMIT 0, 1", $next_name));

    if ( !$has_name ) {
        return $next_name;
    }

    if ( absint($number) == 0 ) {
        $number = 2;
    } else {
        $number++;
    }

    return get_unique_easy_sticky_sidebar_name($name, $number);
}

/**
 * Get easy sticky sidebar tabs
 * @since  1.4.0
 */
function get_easy_sticky_sidebar_cta_tabs() {
    $wordpress_cta_tabs = apply_filters( 'easy_sticky_sidebar_tabs', array(
        'template' => [
            'label' => __("Template Layout", 'easy-sticky-sidebar'),
            'callback' => 'easy_sticky_sidebar_template_tab',
            'priority' => 1
        ],
        'position' => [
            'label' => __("Page Position", 'easy-sticky-sidebar'),
            'callback' => 'easy_sticky_sidebar_position_tab',
            'priority' => 2
        ],
        'location' => [
            'label' => __("Page/Post Location", 'easy-sticky-sidebar'),
            'callback' => 'easy_sticky_sidebar_location_tab',
            'priority' => 3
        ],

        'content' => [
            'label' => __("Content", 'easy-sticky-sidebar'),
            'callback' => 'easy_sticky_sidebar_content_tab_callback',
            'priority' => 4
        ],

        'content-styling' => [
            'label' => __("Styling", 'easy-sticky-sidebar'),
            'callback' => 'easy_sticky_sidebar_styling_tab',
            'priority' => 5
        ],
        'responsive' => [
            'label' => __("Responsive", 'easy-sticky-sidebar'),
            'callback' => 'easy_sticky_sidebar_responsive_tab',
            'priority' => 6
        ],

        'css' => [
            'label' => __("CSS", 'easy-sticky-sidebar'),
            'callback' => 'easy_sticky_sidebar_css_tab',
            'priority' => 7
        ],

        'status' => [
            'label' => __("Live Status", 'easy-sticky-sidebar'),
            'callback' => 'easy_sticky_sidebar_status_tab',
            'priority' => 8
        ]        
    ));

    if ( !is_array($wordpress_cta_tabs) || empty($wordpress_cta_tabs)) {
        return [];
    }

    foreach ($wordpress_cta_tabs as $key => $tab) {
        if ( empty($tab['callback']) ) {
            unset($wordpress_cta_tabs[$key]);
            continue;
        }

        if ( is_string($tab['callback']) && !function_exists($tab['callback'])) {
            unset($wordpress_cta_tabs[$key]);
            continue;
        }

        if (is_array($tab['callback']) && !method_exists($tab['callback'][0], $tab['callback'][1]) ) {
            unset($wordpress_cta_tabs[$key]);
        }
    }

    array_multisort(array_column($wordpress_cta_tabs, 'priority'), SORT_ASC, $wordpress_cta_tabs);

    return $wordpress_cta_tabs;
}

/**
 * Easy sticky sidebar template tab
 * @since  1.4.0
 */
function easy_sticky_sidebar_template_tab($stickycta) {
    $pro_templates = array('html', 'banner', 'gdpr'); ?>
    <h4 class="wordpress-cta-heading"><?php _e("Template Layout", "sticky-sidebar") ?> </h4>
    <p class="wordpress-cta-instruction"><?php _e('Select a template layout for this CTA. Click on the button below to view our demos.', 'easy-sticky-sidebar') ?></p>
    <?php 
	if (!has_wordpress_cta_pro()) {
		echo '<p class="wordpress-cta-instruction text-bold">Get more options with our <a href="https://wordpressctapro.com/" target="_blank">pro version</a>.</p>';
	} ?>
    <div class="SSuprydp_field_wrap">
        <label><?php _e("Template", "easy-sticky-sidebar");?></label>
        <select name="sidebar_template" class="SSuprydp_input">
            <?php
            foreach (easy_sticky_sidebar_templates() as $template => $name) {
                $attribute = selected($template, $stickycta->sidebar_template, false);

                if ( in_array($template, $pro_templates) && !has_wordpress_cta_pro() ) {
                    $name = sprintf('%s (%s)', $name, __('Pro Feature', 'easy-sticky-sidebar'));
                    $attribute .= ' disabled';
                }

                printf('<option value="%s" %s>%s</option>', esc_attr($template), $attribute, esc_html($name));
            } ?>
        </select>

        <div style="margin-top:10px">
            <a class="button btn-wordpress-cta-primary" href="https://wordpressctapro.com/demos/" target="_blank"><?php _e('View Demos', 'easy-sticky-sidebar')?></a>
        </div>
    </div>
    <?php
    do_action('easy_sticky_sidebar_form_after_template', $stickycta, $stickycta->id);
}

/**
 * Easy sticky sidebar position tab
 * @since  1.4.0
 */
function easy_sticky_sidebar_position_tab($stickycta) {
    if (has_action('easy_sticky_sidebar_cta_position')) :
        echo '<h4 class="wordpress-cta-heading">' . __("Page Position", "sticky-sidebar") . '</h4>';
        echo '<p class="wordpress-cta-instruction">' . __('Select where on the page you would like to display your CTA', 'easy-sticky-sidebar') . '</p>';
        if (!has_wordpress_cta_pro()) {
            echo '<p class="wordpress-cta-instruction text-bold">Get more options with our <a href="https://wordpressctapro.com/" target="_blank">pro version</a>.</p>';
        }
        do_action('easy_sticky_sidebar_cta_position', $stickycta, $stickycta->id);
    endif;
}

/**
 * Easy sticky sidebar location tab
 * @since  1.4.0
 */
function easy_sticky_sidebar_location_tab($stickycta) {
    if (has_action('easy_sticky_sidebar_form_cta_location')): 
        do_action('easy_sticky_sidebar_form_cta_location', $stickycta, $stickycta->id); 
    endif;
}


/**
 * Easy sticky sidebar location tab
 * @since  1.4.0
 */
function easy_sticky_sidebar_responsive_tab($stickycta) { ?>
    <h4 class="wordpress-cta-heading"><?php _e("Responsive Setting", "sticky-sidebar");?></h4>
    <p class="wordpress-cta-instruction">Show and hide the cta on different devices.</p>
    <?php
    if (!has_wordpress_cta_pro()) {
        echo '<p class="wordpress-cta-instruction text-bold">Get more options with our <a href="https://wordpressctapro.com/" target="_blank">pro version</a>.</p>';
    } ?>
    <div class="SSuprydp_field_wrap">
        <div class="SSuprydp_yes_btn SSuprydp_sliderview">
            <label class="SSuprydp_switch">
                <input type="checkbox" name="SSuprydp_dis_desktop" value="Yes"
                    <?php checked('Yes', $stickycta->SSuprydp_dis_desktop)?>
                    class="develop_check checkbox-show-hide">
            </label>
            <span class="field_title"><?php _e("Desktop", "sticky-sidebar");?></span>
        </div>

        <div class="SSuprydp_yes_btn SSuprydp_sliderview">
            <label class="SSuprydp_switch">
                <input type="checkbox" name="SSuprydp_dis_tablet" value="Yes"
                    <?php checked('Yes', $stickycta->SSuprydp_dis_tablet)?>
                    class="develop_check checkbox-show-hide">
            </label>
            <span class="field_title"><?php _e("Tablet", "sticky-sidebar");?></span>
        </div>

        <div class="SSuprydp_yes_btn SSuprydp_sliderview">
            <label class="SSuprydp_switch">
                <input type="checkbox" name="SSuprydp_dis_mobile" value="Yes"
                    <?php checked('Yes', $stickycta->SSuprydp_dis_mobile)?>
                    class="develop_check checkbox-show-hide">
            </label>
            <span class="field_title"><?php _e("Mobile", "sticky-sidebar");?></span>
        </div>
    </div>
    <?php
}

/**
 * Easy sticky sidebar css tab
 * @since  1.4.5
 */
function easy_sticky_sidebar_css_tab($stickycta) {?>
    <h4 class="wordpress-cta-heading"><?php _e("Custom CSS", "easy-sticky-sidebar"); ?></h4>

    <p>Example: a { font-size: 16px; }</p>
    <div class="SSuprydp_field_wrap wordpress-cta-pro-features">
        <?php get_wordpress_cta_pro_block(); ?>
        <textarea style="width: 100%" cols="30" rows="10"></textarea>
    </div>		
    <?php
}

/**
 * Easy sticky sidebar location tab
 * @since  1.4.0
 */
function easy_sticky_sidebar_status_tab($stickycta) {?>
    <h4 class="wordpress-cta-heading"><?php _e("Display Settings", "sticky-sidebar");?></h4>
    <p style="margin-bottom: 10px" class="wordpress-cta-instruction"><?php _e('Change the status of your CTA. Live will show to everyone, Development will only show to admins who are logged in and off will not show to anyone.', 'easy-sticky-sidebar') ?></p>
    <?php get_easy_sticky_sidebar_get_status_menu($stickycta); ?>
    <?php
}

/**
 * Easy sticky sidebar content tab
 * @since  1.4.0
 */
function easy_sticky_sidebar_styling_tab($stickycta) {
    echo '<h4 class="wordpress-cta-heading">' . __("Content / Styling", "wp-cta-free") . '</h4>';
    echo '<p class="wordpress-cta-instruction">' . __('Add your content and edit the styles of your CTA.', 'easy-sticky-sidebar') . '</p>';
	if (!has_wordpress_cta_pro()) {
		echo '<p class="wordpress-cta-instruction text-bold">Get more options with our <a href="https://wordpressctapro.com/" target="_blank">pro version</a>.</p>';
	}

    echo '<div class="gap-10"></div>';
    echo '<div class="wordpress-cta-styling-container">';
    do_action( 'easy_sticky_sidebar_styling_options', $stickycta );
    echo '</div>';
}

/**
 * Add section for cta Design templates
 * @since  1.4.5
 */
function easy_sticky_sidebar_design_template_callback($stickycta) {
    if (has_action('easy_sticky_sidebar_design_template')): ?>
    <details class="easy-sticky-sidebar-fieldset  sticky-cta-option <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_design_template'); ?>" id="cta-design-template">
        <summary class="heading"><?php _e("Design Template", "easy-sticky-sidebar");?></summary>
        <?php do_action('easy_sticky_sidebar_design_template', $stickycta);?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_design_template_callback', 1);

/**
 * Add section for cta scroll options
 * @since  1.0.3
 */
function easy_sticky_sidebar_cta_scroll_options_callback($stickycta) {
    if (has_action('easy_sticky_sidebar_cta_scroll_options')): ?>
    <details class="easy-sticky-sidebar-fieldset  sticky-cta-option <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_cta_scroll_options'); ?>" id="cta-scroll-options">
        <summary class="heading"><?php _e("CTA Scroll Options", "easy-sticky-sidebar");?></summary>
        <?php do_action('easy_sticky_sidebar_cta_scroll_options', $stickycta, $stickycta->id);?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_cta_scroll_options_callback', 2);

/**
 * Add option for CTA Display
 * @since  1.0.3
 */
function easy_sticky_sidebar_cta_display_options_callback($stickycta) {
    if (has_action('easy_sticky_sidebar_cta_display_options')): ?>
    <details class="easy-sticky-sidebar-fieldset html-cta-option <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_cta_display_options'); ?>" id="cta-display-options">
        <summary class="heading"><?php _e("CTA Display Options", "easy-sticky-sidebar");?></summary>
        <?php do_action('easy_sticky_sidebar_cta_display_options', $stickycta, $stickycta->id);?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_cta_display_options_callback', 3);

/**
 * Add option for CTA Height
 * @since  1.0.3
 */
function easy_sticky_sidebar_html_cta_height_callback($stickycta) {
    if (has_action('easy_sticky_sidebar_cta_height')): ?>
    <details class="easy-sticky-sidebar-fieldset html-cta-option <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_cta_height'); ?>" id="section-cta-height-options">
        <summary class="heading"><?php _e("CTA Height", "easy-sticky-sidebar");?></summary>
        <?php do_action('easy_sticky_sidebar_cta_height', $stickycta, $stickycta->id);?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_html_cta_height_callback', 4);

/**
 * Easy sticky sidebar CTA Adjustment
 * @since  1.4.0
 */
function easy_sticky_sidebar_cta_adjustment($stickycta) {
    if (has_action('easy_sticky_sidebar_cta_adjustment')): ?>
    <details class="easy-sticky-sidebar-fieldset <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_cta_adjustment'); ?>" id="cta-adjustment-options">
        <summary class="heading"><?php _e("CTA Width", "easy-sticky-sidebar");?></summary>
        <?php do_action('easy_sticky_sidebar_cta_adjustment', $stickycta, $stickycta->id);?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_cta_adjustment', 5);

/**
 * Easy sticky sidebar CTA Image
 * @since  1.4.0
 */
function easy_sticky_sidebar_cta_image($stickycta) {
    if (has_action('easy_sticky_sidebar_cta_image')): ?>
    <details class="easy-sticky-sidebar-fieldset <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_cta_image'); ?>" id="sticky-cta-banner-image">
        <summary class="heading">
            <?php _e("CTA Image Options", "sticky-sidebar");?>
            <div class="easy-sticky-sidebar-guideline">
                <div class="dashicons dashicons-info"></div>
                <div class="guideline-text">
                    <img class="sticky-cta-guideline-img" src="<?php echo EASY_STICKY_SIDEBAR_PLUGIN_URL; ?>/assets/instructions/2.png" alt="">
                    <?php do_action( 'easy_sticky_sidebar/image_options_guideline' ) ?>
                </div>
            </div>
        </summary>
        <?php do_action('easy_sticky_sidebar_cta_image', $stickycta, $stickycta->id);?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_cta_image', 6);

/**
 * Easy sticky sidebar CTA Button options
 * @since  1.4.0
 */
function easy_sticky_sidebar_button_options($stickycta) {
    if (has_action('easy_sticky_sidebar_button_options')): ?>
    <details class="easy-sticky-sidebar-fieldset <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_button_options'); ?>" id="sticky-sidebar-button-options">
        <summary class="heading">
            <h2><?php _e("CTA Button Options", "sticky-sidebar");?> </h2>

            <div class="easy-sticky-sidebar-guideline">
                <div class="dashicons dashicons-info"></div>
                <div class="guideline-text">
                    <img class="sticky-cta-guideline-img" src="<?php echo EASY_STICKY_SIDEBAR_PLUGIN_URL; ?>/assets/instructions/1.png" alt="">
                    <?php do_action( 'easy_sticky_sidebar/button_options_guideline' ) ?>
                </div>
            </div>
        </summary>

        <?php do_action('easy_sticky_sidebar_button_options', $stickycta, $stickycta->id);?>

        <?php if (has_action('easy_sticky_sidebar_button2_options')): ?>
        <div id="sticky-sidebar-button2-options" class="wordpress-cta-gdpr-options">
            <h3>Decline Button Options</h3>
            <?php do_action('easy_sticky_sidebar_button2_options', $stickycta, $stickycta->id);?>
        </div>
        <?php endif;?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_button_options', 7);

/**
 * Easy sticky sidebar CTA content options
 * @since  1.4.0
 */
function easy_sticky_sidebar_line_separator($stickycta) { 
    if (has_action('easy_sticky_sidebar_line_separator')): ?>
    <details class="easy-sticky-sidebar-fieldset <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_line_separator'); ?>" id="cta-line-separator-options">
        <summary class="heading">
            <?php _e("Line Separator Options", "wordpress-cta-pro");?>
            <div class="easy-sticky-sidebar-guideline">
                <div class="dashicons dashicons-info"></div>
                <div class="guideline-text">
                    <img class="sticky-cta-guideline-img" src="<?php echo EASY_STICKY_SIDEBAR_PLUGIN_URL; ?>/assets/instructions/6.png" alt=""> 
                    <?php do_action( 'easy_sticky_sidebar/line_separator_guideline' ) ?>
                </div>
            </div>
        </summary>
        <?php do_action('easy_sticky_sidebar_line_separator', $stickycta, $stickycta->id)?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_line_separator', 9);


/**
 * Easy sticky sidebar CTA call to action
 * @since  1.4.0
 */
function easy_sticky_sidebar_call_to_action($stickycta) { 
    if (has_action('easy_sticky_sidebar_call_to_action')): ?>
    <details class="easy-sticky-sidebar-fieldset <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_call_to_action'); ?>" id="cta-link-text-options">
        <summary class="heading">
            <?php _e("Link Text Options", "sticky-sidebar");?>
            <div class="easy-sticky-sidebar-guideline">
                <div class="dashicons dashicons-info"></div>
                <div class="guideline-text">
                    <img class="sticky-cta-guideline-img" src="<?php echo EASY_STICKY_SIDEBAR_PLUGIN_URL; ?>/assets/instructions/4.png" alt="">
                    <?php do_action( 'easy_sticky_sidebar/link_options_guideline' ) ?>
                </div>
            </div>
        </summary>
        <?php do_action('easy_sticky_sidebar_call_to_action', $stickycta, $stickycta->id)?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_call_to_action', 10);

/**
 * Easy sticky sidebar CTA content options
 * @since  1.4.0
 */
function easy_sticky_sidebar_close_button_options($stickycta) { 
    if (has_action('easy_sticky_sidebar_close_button_options')): ?>
    <details class="easy-sticky-sidebar-fieldset <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_close_button_options'); ?>" id="cta-close-button-options">
        <summary class="heading">
            <?php _e("Close Button Options", "easy-sticky-sidebar");?>
            <div class="easy-sticky-sidebar-guideline">
                <div class="dashicons dashicons-info"></div>
                <div class="guideline-text">
                    <img class="sticky-cta-guideline-img" src="<?php echo EASY_STICKY_SIDEBAR_PLUGIN_URL; ?>/assets/instructions/5.png" alt="">
                    <?php do_action( 'easy_sticky_sidebar/close_button_guideline' ) ?>
                </div>
            </div>
        </summary>
        <?php do_action('easy_sticky_sidebar_close_button_options', $stickycta, $stickycta->id)?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_close_button_options', 11);

/**
 * Get pro featured block
 * @since  1.4.5
 */
function get_wordpress_cta_pro_block($title = '', $description = null) {
    if ( empty($title) ) {
        $title = __('This is a pro feature', 'easy-sticky-sidebar');
    } 

    if ( empty($description) ) {
        //$description = __('Unlock to create unlimited and customizable recurring campaigns.', 'easy-sticky-sidebar');
    } ?>

    <div class="wordpress-cta-pro-block">
        <span class="dashicons dashicons-lock"></span>
        <h4 class="pro-title"><?php echo esc_html($title) ?></h4>

        <div class="pro-description"><?php echo esc_html($description) ?></div>

        <footer>
            <a class="button btn-wordpress-cta-primary" href="https://wordpressctapro.com/pricing/" target="_blank"><?php _e('Upgrade now', 'easy-sticky-sidebar') ?></a>
            <a href="https://wordpressctapro.com/" target="_blank"><?php _e('Learn more', 'easy-sticky-sidebar') ?></a>
        </footer>
    </div>
    <?php
}

/**
 * Add Page load option section
 * @since  1.4.5
 */
function easy_sticky_sidebar_page_load_callback($stickycta) {
    if (has_action('easy_sticky_sidebar_page_load_options')): ?>
    <details class="easy-sticky-sidebar-fieldset sticky-cta-option <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_page_load_options'); ?>" id="cta-page-load-options">
        <summary class="heading"><?php _e("Page Load Options", "easy-sticky-sidebar");?></summary>
        <?php do_action('easy_sticky_sidebar_page_load_options', $stickycta);?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_page_load_callback', 4.2);


/**
 * Easy sticky sidebar content tab
 * @since  1.4.5
 */
function easy_sticky_sidebar_content_tab_callback($stickycta) {
    echo '<h4 class="wordpress-cta-heading">' . __("Content", "wp-cta-free") . '</h4>';
    echo '<p class="wordpress-cta-instruction">' . __('Please enter your text / content', 'easy-sticky-sidebar') . '</p>';
	if (!has_wordpress_cta_pro()) {
		echo '<p class="wordpress-cta-instruction text-bold">Get more options with our <a href="https://wordpressctapro.com/" target="_blank">pro version</a>.</p>';
	}

    echo '<div class="gap-10"></div>';
    echo '<div class="wordpress-cta-content-container">';
    do_action( 'easy_sticky_sidebar_content_tab_options', $stickycta );
    echo '</div>';
}

/**
 * CTA image container
 * @since 1.4.5
 */
function easy_sticky_sidebar_image_container($stickycta) {
    if ( !has_action( 'easy_sticky_sidebar_content_image') ) {
        return;
    } ?>
    <div id="sticky_cta_banner_img" class="SSuprydp_field_wrap banner_img ">
        <?php do_action('easy_sticky_sidebar_content_image', $stickycta) ?>
    </div>
    <?php
}
add_action('easy_sticky_sidebar_content_tab_options', 'easy_sticky_sidebar_image_container', 5 );

/**
 * CTA button text container
 * @since 1.4.5
 */
function easy_sticky_sidebar_button_text_container($stickycta) {if ( !has_action( 'easy_sticky_sidebar_content_button') ) {
        return;
    } ?>
    <div id="sticky-cta-content-button-container">
        <?php do_action('easy_sticky_sidebar_content_button', $stickycta) ?>
        <?php if (has_action('easy_sticky_sidebar_content_button2_options')): ?>
        <div class="button2-content-option wordpress-cta-gdpr-options">
            <hr>
            <h3>Decline Button Options</h3>
            <?php do_action('easy_sticky_sidebar_content_button2_options', $stickycta);?>
        </div>
        <?php endif;?>
    </div>
    <?php
}
add_action('easy_sticky_sidebar_content_tab_options', 'easy_sticky_sidebar_button_text_container', 10 );



/**
 * CTA content text container
 * @since 1.4.5
 */
function easy_sticky_sidebar_content_text_container($stickycta) {
    if ( !has_action( 'easy_sticky_sidebar_content_text') ) {
        return;
    } ?>
    <div id="sticky-cta-content-text-container">
        <?php do_action('easy_sticky_sidebar_content_text', $stickycta) ?>
    </div>
    <?php
}
add_action('easy_sticky_sidebar_content_tab_options', 'easy_sticky_sidebar_content_text_container', 15 );

/**
 * CTA button text container
 * @since 1.4.5
 */
function easy_sticky_sidebar_content_link_option_callback($stickycta) {
    if ( !has_action( 'easy_sticky_sidebar_content_link_options') ) {
        return;
    } ?>
    <div id="sticky-cta-content-link-options-container">
        <?php do_action('easy_sticky_sidebar_content_link_options', $stickycta) ?>
    </div>
    <?php
}
add_action('easy_sticky_sidebar_content_tab_options', 'easy_sticky_sidebar_content_link_option_callback', 20 );


/**
 * Easy sticky sidebar CTA content options
 * @since  1.4.5
 */
function easy_sticky_sidebar_content_styling_option_callback($stickycta) {
    if (has_action('easy_sticky_sidebar_content_option')): ?>
    <details class="easy-sticky-sidebar-fieldset <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_page_load_options'); ?>" id="cta-content-options">
        <summary class="heading">
            <?php _e("CTA Content Options", "easy-sticky-sidebar");?>
            <div class="easy-sticky-sidebar-guideline">
                <div class="dashicons dashicons-info"></div>
                <div class="guideline-text">
                    <img class="sticky-cta-guideline-img" src="<?php echo EASY_STICKY_SIDEBAR_PLUGIN_URL; ?>/assets/instructions/3.png" alt="">
                    <?php do_action( 'easy_sticky_sidebar/content_options_guideline' ) ?>
                </div>
            </div>
        </summary>
        <div id="content-style-options">
            <?php do_action('easy_sticky_sidebar_content_option', $stickycta);?>
        </div>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_content_styling_option_callback', 8);

/**
 * global style
 * @since  1.4.7
 */
function easy_sticky_sidebar_global_style_callback($stickycta) {
    if (has_action('easy_sticky_sidebar_global_styles')): ?>
    <details id="global-style-tab" class="easy-sticky-sidebar-fieldset <?php echo Wordpress_CTA_Free_Utils::pro_tab_class('easy_sticky_sidebar_global_styles'); ?>">
        <summary class="heading"><?php _e("Global Style", "easy-sticky-sidebar");?></summary>
        <div class="gap-5"></div>
        <p class="wordpress-cta-instruction">Set the styles for all the buttons here. If you edit an individual button, that style will override the global style for only that button.</p>
        <?php do_action('easy_sticky_sidebar_global_styles', $stickycta);?>
    </details>
    <?php endif;
}
add_action( 'easy_sticky_sidebar_styling_options', 'easy_sticky_sidebar_global_style_callback', 1.1);