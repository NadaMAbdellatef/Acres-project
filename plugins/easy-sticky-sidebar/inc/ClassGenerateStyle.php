<?php

/**
 * Easy_Sticky_CTA_Generate_CSS
 * @package sticky-sidebar
 * @since   1.3.6
 */
class Easy_Sticky_CTA_Generate_CSS {

    function __construct() {
        $this->generate_css_file();
        add_action('easy_sticky_sidebar_after_save', [$this, 'generate_style'], 2);
    }

    public function generate_css_file() {
        $upload_dir = wp_get_upload_dir();
        $css_file = $upload_dir['basedir'] . '/sticky-sidebar-generated.css';
        if ( !file_exists($css_file)) {
            $this->generate_style();
        }
    }

    public function generate_style() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM $wpdb->sticky_cta WHERE SSuprydp_development != 'off' ORDER BY id");

        ob_start();
        foreach ($results as $item) {
            $this->item = new WP_Sticky_CTA_Data($item);
            $this->generate_wrapper_style($this->item);
            $this->template_style();
            do_action('easy_sticky_sidebar_generate_css', $this->item, $this);
        }

        do_action('easy_sticky_sidebar_after_generate_css');

        $styles = ob_get_clean();

        file_put_contents(wp_upload_dir()['basedir'] . '/sticky-sidebar-generated.css', $styles);
    }

    public static function get_font_style($font) {
        @list($font_family, $font_style) = explode(':', str_replace('+', ' ', $font));
        if ( $font_family ) {
            printf("\tfont-family: '%s';\n", $font_family);
        }

        if ( absint($font_style) > 0 ) {
            printf("\tfont-weight: %s;\n", absint($font_style)); 
		}

		if ( strpos($font_style, 'italic') !== false) {
            print("\tfont-style: italic;\n"); 
		}
    }

    public function generate_wrapper_style($sticky_cta) {
        $disable_position = apply_filters( 'easy_sticky_sidebar/disable_position_css', ['banner']);
        if ( in_array($sticky_cta->sidebar_template, $disable_position)) {
            return;
        }

        //$disable_position1 = apply_filters( 'easy_sticky_sidebar/disable_position1_css', ['tab-cta']);
        $disable_position2 = apply_filters( 'easy_sticky_sidebar/disable_position2_css', []);

        $wrapper_selector = sprintf("#easy-sticky-sidebar-%d", $sticky_cta->id);

        $styles = '';

        // if (!in_array($sticky_cta->sidebar_template, $disable_position1) ) {
        //     if ( $position_distance = intval($sticky_cta->position_distance ) ) {
        //         $styles .= sprintf("\t--position_distance: %dpx;\n", $position_distance); 
        //     }
        // }

        if (!in_array($sticky_cta->sidebar_template, $disable_position2) ) {
            $unit = empty($sticky_cta->position2_distance_unit) ? 'px' : $sticky_cta->position2_distance_unit;
            if ( $position2_distance = intval($sticky_cta->position2_distance ) ) {
                $styles .= sprintf("\t--position2_distance: %d%s;\n", $position2_distance, $unit); 
            }
        }


        if ( !empty($styles) ) {
            printf("%s {%s}\n\n", $wrapper_selector, $styles);
        }
    }

    public function generate_button_style() {
        $sticky_cta = $this->item;

        if ( !empty($this->item->SSuprydp_button_option_color) ) {
            printf("\tcolor: %s;\n", $this->item->SSuprydp_button_option_color); 
        }

		self::get_font_style($this->item->SSuprydp_button_option_font);

        $font_size = absint( $this->item->SSuprydp_button_option_size);
        if (  $font_size > 0 ) {
            printf("\tfont-size: %spx;\n", $font_size);
        }

        printf("\ttext-align: %s;\n", $this->item->SSuprydp_button_option_align); 

        if ( !empty($this->item->SSuprydp_button_option_backg_color) ) {
            printf("\tbackground-color: %s;\n", $this->item->SSuprydp_button_option_backg_color); 
        }

        Wordpress_CTA_Free_Utils::get_dimensions_output($sticky_cta->button_padding, 'padding-%');

        do_action( 'easy_sticky_sidebar_generate_button_style', $this->item);
    }

    public function sidebar_image_style() {
        if ( !empty($this->item->sticky_s_media) ) {
            printf("\tbackground-image: url(%s);\n", $this->item->sticky_s_media); 
        }

        if ( absint($this->item->cta_image_height) > 0) {
            printf("\theight: %dpx;\n", $this->item->cta_image_height); 
        }

        do_action( 'easy_sticky_sidebar_generate_image_style', $this->item);
    }

    public function content_style() {
        if ( !empty($this->item->SSuprydp_content_option_color) ) {
            printf("\tcolor: %s;\n", $this->item->SSuprydp_content_option_color); 
        }

        self::get_font_style($this->item->SSuprydp_content_option_font);

        $font_size = absint($this->item->SSuprydp_content_option_size);
        if ( $font_size > 0) {
            printf("\tfont-size: %spx;\n", $font_size);
        }

        if ( !empty($this->item->content_background_color) ) {
            printf("background-color: %s;\n", $this->item->content_background_color);
        }

        Wordpress_CTA_Free_Utils::get_dimensions_output($this->item->content_padding, 'padding-%');

        do_action( 'easy_sticky_sidebar_generate_content_style', $this->item);
    }

    public function call_to_action_style() {
        if ( !empty($this->item->SSuprydp_action_option_color) ) {
            printf("\tcolor: %s;\n", $this->item->SSuprydp_action_option_color); 
        }

        self::get_font_style($this->item->SSuprydp_action_option_font);

        $font_size = absint($this->item->SSuprydp_action_option_size);
        if ( $font_size > 0) {
            printf("\tfont-size: %spx;\n", $font_size);
        }    
        
        if ( !empty($this->item->link_text_background) ) {
            printf("background-color: %s;\n", $this->item->link_text_background);
        }

        Wordpress_CTA_Free_Utils::get_dimensions_output($this->item->call_to_action_padding, 'padding-%');

        do_action( 'easy_sticky_sidebar_generate_call_to_action_style', $this->item);
    }
    
    function template_style() {
        if ( !in_array($this->item->sidebar_template, ['sticky-cta', 'tab-cta', 'html'])) {
            return;
        }

        $sticky_class = sprintf("#easy-sticky-sidebar-%d.easy-sticky-sidebar", $this->item->id);

        printf("%s {\n", $sticky_class);
            if ( $this->item->enable_cta_width == 'yes' && absint($this->item->cta_width) > 0) {
                $unit = empty($this->item->cta_width_unit) ? 'px' : $this->item->cta_width_unit;
                printf("\t--width: %d%s;\n", $this->item->cta_width, $unit); 
            }
            
            do_action( 'easy_sticky_sidebar_wrapper_style', $this->item);
        echo "}\n\n";

        echo '@media screen and (min-width: 768px) and (max-width: 1024px){';
            printf("%s {\n", $sticky_class);
                if ( $this->item->enable_cta_width == 'yes' && absint($this->item->cta_tablet_width) > 0) {
                    $unit = empty($this->item->cta_tablet_width_unit) ? 'px' : $this->item->cta_tablet_width_unit;
                    printf("\t--width: %d%s;\n", $this->item->cta_tablet_width, $unit); 
                }

                do_action( 'easy_sticky_sidebar_wrapper_style_tablet', $this->item);
            echo "}\n\n";

        echo '}';
        
        echo '@media screen and (max-width: 767px){';
            printf("%s {\n", $sticky_class);
                if ( $this->item->enable_cta_width == 'yes' && absint($this->item->cta_mobile_width) > 0) {
                    $unit = empty($this->item->cta_mobile_width_unit) ? 'px' : $this->item->cta_mobile_width_unit;
                    printf("\t--width: %d%s;\n", $this->item->cta_mobile_width, $unit); 
                }
                
                do_action( 'easy_sticky_sidebar_wrapper_style_mobile', $this->item);

            echo "}\n\n";

        echo '}';

        printf("%s .sticky-sidebar-button {\n", $sticky_class);
            $this->generate_button_style();
        echo "}\n\n";

        printf("%s .sticky-sidebar-image {\n", $sticky_class);
            $this->sidebar_image_style();
        echo "}\n\n";

        printf("%s .sticky-sidebar-text {\n", $sticky_class);
            $this->content_style();
        echo "}\n\n";

        printf("%s .sticky-sidebar-content hr {\n", $sticky_class);
            if ( !empty($this->item->line_separator_color) ) {
                printf("\tbackground-color: %s;\n", $this->item->line_separator_color); 
            }
        echo "}\n\n";

        printf("%s .sticky-sidebar-call-to-action {\n", $sticky_class);
            $this->call_to_action_style();
        echo "}\n\n";
    }

}