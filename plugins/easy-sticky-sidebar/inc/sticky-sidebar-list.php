<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easy_Sticky_Sidebar_List  extends WP_List_Table   {

	/**
	 * Constructor.
	 */
	public function __construct() {	
        add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 20, 3 );
        parent::__construct();
	}

    /**
	 * set screen option $value.
     * @since  1.0.1
	 */
	public static function set_screen( $status, $option, $value ) {
        return $value;
    }

    /**
	 * add options for screen setting.
     * @since  1.0.1
	 */
	public function screen_option() {
        add_screen_option( 'per_page', [
            'label' => __('Sidebar Per Page', 'easy-sticky-sidebar'),
            'default' => 15,
            'option' => 'sidebar_per_page'
        ] );
    }

    /**
     * Add table nav
     * @since  1.4.0
     */
    function extra_tablenav( $which ) {
        $disable_button = 'disabled';

        global $wpdb;
        $cta = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->sticky_cta");
        if ( $cta < 3 ) {
            return printf('<a style="margin-right: 10px" class="btn-add-new button-primary" href="%s">%s</a>', admin_url( 'admin.php?page=add-easy-sticky-sidebar'), __('Add New CTA', 'easy-sticky-sidebar'));
        }
        
        printf('<a style="margin-right: 10px" data-toggle="tooltip" title="Upgrade to WP CTA Pro"class="btn-add-new button-primary" href="#" disabled>%s</a>', __('Add New CTA', 'wordpress-cta-pro'));            
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        $columns = array(
            'id'        => __('#ID', 'easy-sticky-sidebar'),
            'name'      => __('Name', 'easy-sticky-sidebar'),
            'display'       => __('Display', 'easy-sticky-sidebar'),
            'template'        => __('Template', 'easy-sticky-sidebar'),
            'position'        => __('Position', 'easy-sticky-sidebar'),
            'action'    => __('Action', 'easy-sticky-sidebar'),
        );

        return $columns;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $sidebar, $column_name ) {

        $templates = [
            'sticky-cta' => __('Open Sliding CTA', 'easy-sticky-sidebar'),
            'tab-cta' => __('Tab CTA', 'easy-sticky-sidebar')
        ];

        switch( $column_name ) {
            case 'id':
                return sprintf('<a class="dashicons dashicons-edit" href="%s"></a>', admin_url('admin.php?page=edit-easy-sticky-sidebar&id='.$sidebar->id));

            case 'display':
                return ucfirst($sidebar->SSuprydp_development);

            case 'position':
                return ucfirst($sidebar->SSuprydp_cta_position);

            case 'template':
                return $templates[$sidebar->sidebar_template];

            default:
                return print_r( $sidebar, true ) ;
        }
    }

    /**
     * name column 
     * @since 1.0.1
     */
    function column_name( $sidebar ) {
        return sprintf(
            '<input class="sticky-sidebar-name-input" type="text" value="%s" placeholder="%s" data-sticky="%d"><i class="dashicons dashicons-edit"></i>', 
            esc_attr($sidebar->sidebar_name), __('Type sidebar name here', 'easy-sticky-sidebar'), $sidebar->id
        );
    } 

    /**
     * Template Column 
     * @since 1.3.5
     */
    function column_template( $sidebar ) {
        $templates = easy_sticky_sidebar_templates();
        return empty($templates[$sidebar->sidebar_template]) ? '' : $templates[$sidebar->sidebar_template];
    } 

    /**
     * action column 
     * @since 1.0.1
     */
    function column_action( $sidebar ) {
        $permalink = add_query_arg([
            'id' => $sidebar->id,
            '_nonce' => wp_create_nonce('nonce_cta_action_' . $sidebar->id),
        ], admin_url('admin.php?page=easy-sticky-sidebars'));

        $actions[] = sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=edit-easy-sticky-sidebar&id='.$sidebar->id),  __('Edit', 'wordpress-cta'));
        $actions[] = sprintf('<a class="cta-delete" href="%s">%s</a>', add_query_arg('action', 'delete', $permalink), __('Delete', 'wordpress-cta'));
        return implode(' | ', $actions);
    } 

    /**
     * Prepare the items for the table to process
     * @return Void
     */
    public function prepare_items() {
        global $wpdb;

        $per_page = $this->get_items_per_page( 'sidebar_per_page', 15 );

        $sidebars = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sticky_cta ORDER BY id LIMIT 0, 3");
        $total_sidebar = $wpdb->num_rows;        

        $this->items = array_map(function($sidebar) {
            return new WP_Sticky_CTA_Data($sidebar);
        }, $sidebars);
    
        $this->set_pagination_args( array(
            'total_items' => $total_sidebar,
            'per_page'    => $per_page
        ) );

        $this->_column_headers = array($this->get_columns());
    }


    /**
	 * admin page for form entries
     * @since  1.0.1
	 */
    public function output() {		
		$this->prepare_items(); ?>
        <div class="wrap wrap-easy-sticky-sidebar">
		    <?php get_easy_sticky_sidebar_header() ?>
            
            <div class="easy-sticky-sidebar-container">            
                <hr class="wp-header-end">
                <form method="post"><?php $this->display(); ?></form>

                <?php if (!has_wordpress_cta_pro()) : ?>                    
                <div class="wordpress-cta-advertisement"><a href="https://wordpressctapro.com/" target="_blank"><img src="<?php echo EASY_STICKY_SIDEBAR_PLUGIN_URL; ?>/assets/img/ads.png" /></a></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

}