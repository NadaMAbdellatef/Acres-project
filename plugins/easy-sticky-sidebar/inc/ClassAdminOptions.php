<?php
class SSuprydpStickySidebarOptions {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'addSubmenuPages'));
        $this->handle_cta_action();

        add_action( 'admin_footer', [$this, 'pro_feature_popup']);
        add_action( 'admin_footer', [$this, 'load_design_template_popup']);
    }

    
    function handle_cta_action() {
        if ( !isset($_GET['id']) || !isset($_GET['_nonce']) || !isset($_GET['action'] )) {
            return;
        }

        if( !wp_verify_nonce($_GET['_nonce'], 'nonce_cta_action_' . $_GET['id'])) {
            return;
        }

        $action = $_GET['action'];

        global $wpdb;
        if ( 'delete' !== $action ) {
            return;            
        }

        if ( $wpdb->delete($wpdb->sticky_cta, array( 'ID' => $_GET['id'] ), array( '%d' ) ) ) {
            $wpdb->delete($wpdb->sticky_cta_options, array( 'sticky_cta_id' => $_GET['id'] ), array( '%d' ) );
            exit(wp_safe_redirect('/wp-admin/admin.php?page=easy-sticky-sidebars'));
        }
    }
	 
    /**
     * add submenu pages in admin menu
     */
    public function addSubmenuPages() {
        require_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/sticky-sidebar-list.php';

        $sidebars = new Easy_Sticky_Sidebar_List();		
        add_menu_page('WP CTA', 'WP CTA', 'manage_options', 'easy-sticky-sidebars', apply_filters( 'sticky_sidebar_main_menu', [$sidebars, 'output']), 'dashicons-megaphone' );

        $sidebar_list_menu = add_submenu_page('easy-sticky-sidebars', 'WP CTA Dashboard', 'WP CTA Dashboard', 'manage_options', 'easy-sticky-sidebars', apply_filters( 'sticky_sidebar_main_menu', [$sidebars, 'output']));
        add_action( "load-$sidebar_list_menu", [$sidebars, 'screen_option' ] );

        global $wpdb;
        $cta = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->sticky_cta");
        if ( $cta < 3 || has_wordpress_cta_pro()) {
            add_submenu_page('easy-sticky-sidebars', 'Add New', 'Add New', 'manage_options', 'add-easy-sticky-sidebar', [$this, 'add_new_cta_page'] );
        }
        
        $this->export_import = require_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/import-export.php';
		add_submenu_page('easy-sticky-sidebars', __('Import/Export', 'easy-sticky-sidebar'), __('Import/Export', 'easy-sticky-sidebar'), 'manage_options', 'easy-sticky-sidebar-import-export', [$this->export_import, 'output'] );
        do_action( 'easy_sticky_sidebar_admin_submenu');
        add_submenu_page('easy-sticky-sidebars', __('How to use Wordpress CTA', 'easy-sticky-sidebar'), __('How To Use', 'easy-sticky-sidebar'), 'manage_options', 'https://wordpressctapro.com/help/', 499);
        add_submenu_page('easy-sticky-sidebars', 'Edit CTA', 'Edit CTA', 'manage_options', 'edit-easy-sticky-sidebar', [$this, 'SSuprydp_AddFormSetting'], 500);
    }

	/**
	* Display add banner view
	*/
	
	public function add_new_cta_page(){
		$default_attachment = get_option('easy_sticky_sidebar_default_attachment');
		$data = array(
            'sticky_id' => 0,
            'editor_current_tab' => 'sticky-sidebar-template',
			'stickycta' => new WP_Sticky_CTA_Data([
				'sticky_s_media' => wp_get_attachment_image_url( $default_attachment),
				'image_attachment_id' => $default_attachment,
			])
		);
		
		print SSuprydpStickySidebar()->engine->getView('add_pages',$data);
	}

	 
	/**
     * add bulk pages
     */
    public function SSuprydp_AddFormSetting() {
		global $wpdb;

		$record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->sticky_cta WHERE id = %d ORDER BY id ASC", $_GET['id']));		
		if ( !$record ) {
			return include_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/views/sidebar-404.php';
		}

        $stickycta = new WP_Sticky_CTA_Data($record);

		$data['stickycta'] = $stickycta;
		$data['sticky_id'] = $stickycta->id ? $stickycta->id : 0;

        $data['editor_current_tab'] = 'sticky-sidebar-template';
        if ( $stickycta->cta_editor_current_tab && WP_DEBUG ) {
            $data['editor_current_tab'] = $stickycta->cta_editor_current_tab;
        }

        $form_attributes = array(
            'class' => 'SSuprydp_form',
            'data-status' => esc_attr($stickycta->SSuprydp_development),
            'data-template' => esc_attr($stickycta->sidebar_template),
        );

        if ($stickycta->hide_floating_button_text == 'yes') {
            $form_attributes['class'] .= ' hide-floating-button-text';
        }

        $data['form_attributes'] = [];
        foreach ($form_attributes as $attribute => $value) {
            $data['form_attributes'][] = sprintf('%s="%s"', $attribute, esc_attr($value));            
        }

		print SSuprydpStickySidebar()->engine->getView('add_pages', $data);
    }
    
    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        return $input;
    }

    /**
     * Pro feature popup
     * @since 1.4.5
     */
    public function pro_feature_popup() {
		$wordpress_cta_page = strpos(get_current_screen()->id, 'easy-sticky-sidebar');
		if ( $wordpress_cta_page === false ) {
			return;
		} ?>
        <div id="wordpress-cta-pro-feature-popup" class="wordpress-cta-popup">
            <div class="popup-content">
                <?php get_wordpress_cta_pro_block(); ?>
                <span class="close"></span>
            </div>
        </div>
        <?php
    }

    /**
     * Pro feature popup
     * @since 1.0.4
     */
    public function load_design_template_popup() {
        $wordpress_cta_page = strpos(get_current_screen()->id, 'easy-sticky-sidebar');
		if ( $wordpress_cta_page === false ) {
			return;
		} ?>
        <div id="wordpress-cta-popup-load-design" class="wordpress-cta-popup">
            <div class="popup-content">
                <?php _e('Do you want to replace this style?', 'wordpress-cta-pro'); ?>

                <footer>
                    <a class="button btn-wordpress-cta-primary" href="#load-style"><?php _e('Load Styles', 'easy-sticky-sidebar') ?></a>
                    <a class="button btn-wordpress-cta-primary" href="#load-style-content"><?php _e('Load Styles and Content', 'easy-sticky-sidebar') ?></a>
                    <a class="button btn-cancel" href="#"><?php _e('Cancel', 'easy-sticky-sidebar') ?></a>
                </footer>                
                <span class="close"></span>
            </div>
        </div>
        <?php
    }
}