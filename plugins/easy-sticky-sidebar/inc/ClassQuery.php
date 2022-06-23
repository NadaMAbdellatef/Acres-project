<?php

/**
 * Easy_Sticky_Sidebar_Query
 * @package sticky-sidebar
 * @since   1.3.7
 */

class Easy_Sticky_Sidebar_Query {

	/**
     * Fonts
	 * @since 1.3.7
     */
	var $fonts = [];

	/**
     * Fonts
	 * @since 1.3.7
     */
	var $cta = [];

    /**
     * Easy_Sticky_Sidebar_Query Constructor
	 * @since 1.3.7
     */
    function __construct() {
		if ( is_admin(  ) ) {
			return;
		}
		
		add_action( 'wp', [$this, 'get_cta']);
		add_action('wp_enqueue_scripts', array($this, 'easy_sticky_sidebar_google_fonts'));		
    }

	public function easy_sticky_sidebar_google_fonts() {
		global $CTA_Query;
		if (!is_array($CTA_Query->fonts)) {
			return;
		}

		wp_enqueue_style('easy-sticky-sidebar-font', 'https://fonts.googleapis.com/css?family='.implode('|', $CTA_Query->fonts));
	}

	/**
	 * Get CTA Items
	 * @since 1.3.7
     */
	public function get_cta() {
		global $wpdb;

		$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->sticky_cta WHERE SSuprydp_location = %d OR SSuprydp_location = '' ORDER BY id ASC LIMIT 0, 3", get_the_ID()));		
		$results = apply_filters('easy_sticky_sidebar_query', $results);

		//field keys for fonts
		$font_fields = apply_filters( 'easy_sticky_sidebar_font_fields', ['default_font_family', 'SSuprydp_button_option_font', 'SSuprydp_content_option_font', 'SSuprydp_action_option_font']);

		$fonts = [];		
		foreach ($results as &$item) {
			if ( !is_a($item, 'WP_Sticky_CTA_Data') ) {
				$item = new WP_Sticky_CTA_Data($item);
			}

			foreach ((array) $font_fields as $field_key) {
				array_push($fonts, $item->$field_key);	
			}
		}

		$this->cta = $results;
		$this->fonts = array_unique(array_filter($fonts));

		$GLOBALS['CTA_Query'] = $this;
	}
}

