<?php
/*
 * Wordpress_CTA_Pro_Content
 * @package sticky-sidebar/inc
 * @since 1.4.5
 */

class Wordpress_CTA_Pro_Placeholder {
    public function __construct() {
        add_filter('wordpress_cta_free/pro_fields', [$this, 'register_placeholder'], 1);
    }

    public function register_placeholder($elements) {
        $elements['show_statistics'] = array('hook' => 'easy_sticky_sidebar_before_tab', 'callback' => [$this, 'show_statistics']);
        
		$elements['cta_location'] = array('hook' => 'easy_sticky_sidebar_form_cta_location', 'callback' => [$this, 'cta_location']);
        
        $elements['html_cta_disable_collapse'] = array('hook' => 'easy_sticky_sidebar_cta_scroll_options', 'callback' => [$this, 'disable_collapse']);

        $elements['cta_width'] = array('hook' => 'easy_sticky_sidebar_cta_adjustment', 'callback' => [$this, 'cta_width']);

        $elements['hide_image'] = array('hook' => 'easy_sticky_sidebar_cta_image', 'callback' => [$this, 'hide_image'], 'priority' => 3);

        $elements['letter_spacing'] = array('hook' => 'easy_sticky_sidebar_button_options', 'callback' => [$this, 'button_letter_spacing'], 'priority' => 32);
        $elements['button_round'] = array('hook' => 'easy_sticky_sidebar_button_options', 'callback' => [$this, 'button_border_round'], 'priority' => 50);
        
        $elements['content_letter_spacing'] = array('hook' => 'easy_sticky_sidebar_content_option', 'callback' => [$this, 'content_letter_spacing'], 'priority' => 12);
        $elements['content_padding'] = array('hook' => 'easy_sticky_sidebar_content_option', 'callback' => [$this, 'content_padding'], 'priority' => 25);
        
        $elements['line_separator_thickness'] = array('hook' => 'easy_sticky_sidebar_line_separator', 'callback' => [$this, 'line_separator_thickness'], 'priority' => 5);
        
        $elements['call_to_action_show_hide'] = array('hook' => 'easy_sticky_sidebar_call_to_action', 'callback' => [$this, 'call_to_action_show_hide'], 'priority' => 1);
        $elements['call_to_action_letter_spacing'] = array('hook' => 'easy_sticky_sidebar_call_to_action', 'callback' => [$this, 'call_to_action_letter_spacing'], 'priority' => 16);
        $elements['call_to_action_padding'] = array('hook' => 'easy_sticky_sidebar_call_to_action', 'callback' => [$this, 'call_to_action_padding'], 'priority' => 25);
        $elements['call_to_action_link_or_button'] = array('hook' => 'easy_sticky_sidebar_call_to_action', 'callback' => [$this, 'call_to_action_link_or_button'], 'priority' => 21);

        $elements['show_close_button'] = array('hook' => 'easy_sticky_sidebar_close_button_options', 'callback' => [$this, 'close_button_option'], 'priority' => 5);
		
        return $elements;
    }

	/**
	 * Show statistics
	 * @since 1.4.5
	 */
    public function show_statistics($stickycta) { ?>
        <h2 class="wordpress-cta-heading"><?php _e('CTA Stats', 'easy-sticky-sidebar') ?></h2>
        <div class="wordpress-cta-pro-features">
            <?php get_wordpress_cta_pro_block(); ?>
            <ul class="wordpress-cta-pro-stats">
                <li>
                    <i class="dashicons dashicons-info" data-toggle="tooltip" title="Impressions are the number of times your CTA is displayed, no matter if it was clicked or not."></i>
                    <h4 class="stats-label"><?php _e('Impressions', 'wordpress-cta-pro') ?></h4>
                    <span class="result">4544</span>
                </li>

                <li>
                    <i class="dashicons dashicons-info" data-toggle="tooltip" title="Number of times your CTA was clicked."></i>
                    <h4 class="stats-label"><?php _e('Clicks', 'wordpress-cta-pro') ?></h4>
                    <span class="result">654</span>
                </li>

                <li>
                    <i class="dashicons dashicons-info" data-toggle="tooltip" title="Clickthrough rate (CTR) is the number of clicks that your CTA receives divided by the number of times your CTA is shown (impressions)."></i>
                    <h4 class="stats-label"><?php _e('CTR', 'wordpress-cta-pro') ?></h4>
                    <span class="result">78%</span>
                </li>
            </ul>
        </div>
		<?php	
    }

	/**
	 * Add page location
	 * @since 1.4.5
	 */
	function cta_location() {?>
		<div class="wordpress-cta-pro-features">
			<?php get_wordpress_cta_pro_block(); ?>
			<div class="SSuprydp_field_wrap location-field-wrapper">
				<label>Include</label>
				<ul class="location-field-container" id="cta-locations" data-btn-add="#btn-add-location" data-name="locations"></ul>
				<a class="button-primary button-large" id="btn-add-location"><?php _e('Add condition', 'easy-sticky-sidebar') ?></a>
			</div>
			
			<div class="gap-10"></div>

			<div class="SSuprydp_field_wrap location-field-wrapper">
				<label>Exclude</label>
				<ul class="location-field-container" id="cta-exclude-locations" data-btn-add="#btn-add-exclude-location" data-name="exclude_locations"></ul>
				<a class="button-primary button-large" id="btn-add-exclude-location"><?php _e('Add condition', 'easy-sticky-sidebar') ?> </a>
			</div>
		</div>
		<?php
	}

    /**
     * Add option for prevent collapse on scroll
     * @since 1.4.5
     */
    function disable_collapse($stickycta) { ?>
        <div class="SSuprydp_field_wrap keep_html_cta_open-option wordpress-cta-pro-features">
			<?php get_wordpress_cta_pro_block(); ?>
			<h4 class="heading"><?php _e('Disable Collapse (Keep CTA open after scroll)', 'easy-sticky-sidebar'); ?></h4>
            <label class="SSuprydp_switch"><input type="checkbox"> </label>
        </div><!-- end wrap -->
        <?php
    }

    /**
	 * Add CTA width field
	 * @since 1.4.5
	 */
    public function cta_width($stickycta) {?>
	
		<div class="wordpress-cta-pro-features">
			<?php get_wordpress_cta_pro_block(); ?>
            <div class="SSuprydp_field_wrap" style="margin-top:20px">
				<h4 class="heading"><?php _e('Enable CTA Width', 'wordpress-cta-pro') ?></h4>
                <label class="SSuprydp_switch has-label" style="margin-bottom: 0">
                    <input type="checkbox">
                </label>
            </div>

            <div id="ess-cta-width">	
                <div class="SSuprydp_field_wrap">
                    <label><?php _e('CTA Width', 'wordpress-cta-pro') ?></label>
                    <input style="width: 50px;text-align:right" type="number">
                    <?php get_easy_sticky_sidebar_unit_input(''); ?>
                </div>

                <div class="SSuprydp_field_wrap">
                    <label><?php _e('CTA Tablet Width', 'wordpress-cta-pro') ?></label>
                    <input style="width: 50px;text-align:right" type="number">
                    <?php get_easy_sticky_sidebar_unit_input(''); ?>
                </div>

                <div class="SSuprydp_field_wrap">
                    <label><?php _e('CTA Mobile Width', 'wordpress-cta-pro') ?></label>
                    <input style="width: 50px;text-align:right" type="number">
                    <?php get_easy_sticky_sidebar_unit_input(''); ?>
                </div>
            </div>
        </div>
		<?php	
    }

    /**
	 * CTA field for Hide or Show image
	 * @since 1.4.5
	 */
	function hide_image() { ?>
		<div class="wordpress-cta-pro-features">
			<?php get_wordpress_cta_pro_block(); ?>
			<div class="SSuprydp_field_wrap wordpress-cta-pro-element">
				<label class="SSuprydp_switch has-label">
					<input type="checkbox" class="checkbox-hide-show"> 
					<?php _e('Hide / Show Image', 'easy-sticky-sidebar') ?>
				</label>
			</div>
			<?php

			?>
			<div class="SSuprydp_field_wrap wordpress-cta-pro-element">
				<label><?php _e('Image Height', 'easy-sticky-sidebar') ?></label>
				<input style="width: 50px;text-align:right" type="number"> px
			</div>
		</div>
		<?php
	}

    /**
	 * CTA button letter spacing
	 * @since 1.4.5
	 */
	function button_letter_spacing() { ?>
		<div class="SSuprydp_field_wrap sticky-sidebar-button_letter_spacing">
			<label><?php _e("Letter Spacing", "easy-sticky-sidebar"); ?></label>
			<div class="wordpress-cta-pro-feature-lock-inline-container">
				<input type="number" style="width: 50px"> px
				<?php Wordpress_CTA_Free_Utils::get_inline_lock() ?>
			</div>
		</div>
		<?php
	}

    /**
	 * CTA button letter spacing
	 * @since 1.4.5
	 */
	function button_border_round() {?>
		<div class="SSuprydp_field_wrap sticky-sidebar-button_radius">
			<label><?php _e("Button Corners (border radius)", "wordpress-cta-pro"); ?></label>
			<div class="wordpress-cta-pro-feature-lock-inline-container">
				<input type="number" style="width: 50px"> px
				<?php Wordpress_CTA_Free_Utils::get_inline_lock() ?>
			</div>
		</div>
		<?php
	}

	/**
	 * CTA content padding
	 * @since 1.4.5
	 */
	function content_padding($stickycta) {?>
		<div class="SSuprydp_field_wrap">
			<label><?php _e("Padding", "easy-sticky-sidebar"); ?></label>
			<div class="wordpress-cta-pro-feature-lock-inline-container">
				<?php Wordpress_CTA_Free_Utils::get_dimensions_field(''); ?>
				<?php Wordpress_CTA_Free_Utils::get_inline_lock(['top' => '-2px', 'bottom' => 'auto']) ?>
			</div>
		</div>
		<?php
	}

    /**
	 * CTA content letter spacing
	 * @since 1.4.5
	 */
	function content_letter_spacing() {?>
		<div class="SSuprydp_field_wrap">
			<label><?php _e("Letter Spacing", "easy-sticky-sidebar"); ?></label>
			<div class="wordpress-cta-pro-feature-lock-inline-container">
				<input type="number" style="width: 50px"> px
				<?php Wordpress_CTA_Free_Utils::get_inline_lock() ?>
			</div>
		</div>
		<?php
	}

    /**
	 * CTA call to action letter spacing
	 * @since 1.4.5
	 */
	function line_separator_thickness() {?>
		<div class="SSuprydp_field_wrap">
			<label><?php _e("Line Thickness", "wordpress-cta-pro"); ?></label>
			<div class="wordpress-cta-pro-feature-lock-inline-container">
				<input type="number" style="width: 50px"> px
				<?php Wordpress_CTA_Free_Utils::get_inline_lock() ?>
			</div>
		</div>
		<?php
	}

    /**
	 * CTA call to action show/hide field
	 * @since 1.4.5
	 */
	function call_to_action_show_hide() { ?>
		<div class="SSuprydp_field_wrap wordpress-cta-pro-feature-lock-inline-container">
			<h3 class="heading" style="margin-top:0; margin-bottom: 5px"><?php _e('Display Link Text', 'easy-sticky-sidebar') ?></h3>
			<label class="SSuprydp_switch">
				<input type="checkbox" class="checkbox-hide-show"> 
			</label>
			<?php Wordpress_CTA_Free_Utils::get_inline_lock() ?>
		</div>
		<?php
	}

	/**
	 * Call to action paddding
	 * @since 1.4.5
	 */
	function call_to_action_padding($stickycta) { ?>
		<div class="SSuprydp_field_wrap">
			<label><?php _e("Padding", "easy-sticky-sidebar"); ?></label>
			<div class="wordpress-cta-pro-feature-lock-inline-container">
				<?php Wordpress_CTA_Free_Utils::get_dimensions_field(''); ?>
				<?php Wordpress_CTA_Free_Utils::get_inline_lock(['top' => '-2px', 'bottom' => 'auto']) ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Call to action paddding
	 * @since 1.4.5
	 */
	function call_to_action_link_or_button($stickycta) { ?>
		<div class="SSuprydp_field_wrap call-to-action-button wordpress-cta-pro-feature-lock-inline-container">
			<label class="SSuprydp_switch has-label">
				<input type="checkbox"><?php _e('Button', 'wordpress-cta-pro') ?>
			</label>
			<?php Wordpress_CTA_Free_Utils::get_inline_lock() ?>
		</div>
		<?php
	}

    /**
	 * CTA call to action letter spacing
	 * @since 1.4.5
	 */
	function call_to_action_letter_spacing() {?>
		<div class="SSuprydp_field_wrap call-to-action-letter-spacing">
			<label><?php _e("Letter Spacing", "easy-sticky-sidebar"); ?></label>
			<div class="wordpress-cta-pro-feature-lock-inline-container">
				<input type="number" style="width: 50px"> px
				<?php Wordpress_CTA_Free_Utils::get_inline_lock() ?>
			</div>
		</div>
		<?php
	}

    /**
	 * CTA close button option - show/hide
	 * @since 1.4.5
	 */
	function close_button_option() { ?>
		<div class="wordpress-cta-pro-features">
			<?php get_wordpress_cta_pro_block(); ?>
			<div class="SSuprydp_field_wrap wordpress-cta-pro-element">
				<label class="SSuprydp_switch has-label">
					<input type="checkbox"><?php _e('Show/Hide Close Button', 'wordpress-cta-pro') ?>
				</label>
			</div>
		
			<div class="SSuprydp_field_wrap wordpress-cta-pro-element">
				<label><?php _e("Color", "wordpress-cta-pro"); ?></label>
				<input type="text" class="sticky-sidebar-colorpicker" />
			</div>
		
			<div class="SSuprydp_field_wrap wordpress-cta-pro-element">
				<label><?php _e("Position", "sticky-sidebar"); ?></label>
				<select>
					<option value="start">Top / Left</option>
					<option value="end">Bottom / Right</option>
				</select>
			</div>
		
			<div class="SSuprydp_field_wrap close-button-edge wordpress-cta-pro-element">
				<label class="SSuprydp_switch has-label">
					<input type="checkbox" class="checkbox-switch checkbox-inside-outside"><?php _e('Inside/Outside', 'wordpress-cta-pro') ?>
				</label>
			</div>
		</div>
		<?php
	}
}