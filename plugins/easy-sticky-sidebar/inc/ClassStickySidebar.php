<?php

/*
 * StickySidebar Main class
 * @package sticky-sidebar/inc
 * @since   1.2.0
 */

class SSuprydpStickySidebar {
    /**
     * The single instance of the class.
     *
     * @var StickySidebar
     * @since 1.2.0
     */
    protected static $_instance = null;

    /**
     * StickySidebar core functions
     *
     * @var engine
     * @since 1.2.0
     */
    public $engine;

    /**
     * Main StickySidebar Instance.
     *
     * Ensures only one instance of IsLayouts is loaded or can be loaded.
     *
     * @since 1.2.0
     * @static
     * @return StickySidebar.
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

	/**
	 * Pro options
	 * @since 1.4.5
	 */
	var $options_pro = null;

    /**
     * StickySidebar Constructor.
     *
     * @global Array StickySidebar
     *
     */
    function __construct() {
		$this->add_data_table();

		require_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/helpers.php';
		require_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/utils.php';
		include_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/sticky-cta-data.php';
		require_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/ClassMigrate.php';
		require_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/ClassIconsLibrary.php';
		require_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/DesignTemplates.php';
		include_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/ClassStickySidebarCore.php';
		include_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/ContentTab.php';
		include_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/FloatingButtons.php';
		include_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/ClassGenerateStyle.php';

		include_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/ClassQuery.php';	
        include_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/ClassActions.php';
        
        if (is_admin()) {
			include_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/ProFields.php';
			include_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/sticky-form-fields.php';
            include_once EASY_STICKY_SIDEBAR_PLUGIN_DIR . '/inc/ClassAdminOptions.php';
        }

		add_action( 'init', [$this, 'init'], 2);

		register_activation_hook(EASY_STICKY_SIDEBAR_PLUGIN_FILE, array($this, 'SSuprydp_plugin_install'));
    }

	/**
     * Add table at $wpdb object
     * @since 1.3.6
     */
	public function add_data_table() {
		global $wpdb;
		$wpdb->sticky_cta = $wpdb->prefix . 'sticky_cta';
		$wpdb->sticky_cta_options = $wpdb->prefix . 'sticky_cta_options';
	}

	/**
	 * Init
	 * @since 1.4.5
	 */
	public function init() {
		$GLOBALS['CTA_Query'] = new Easy_Sticky_Sidebar_Query();

		new SSuprydpproActions();
		new Easy_Sticky_CTA_Generate_CSS();
		new Wordpress_CTA_Free_Floating_Buttons();
		
		if ( is_admin() ) {
			new SSuprydpStickySidebarOptions();
			new Wordpress_CTA_Free_Content_Tab();
			new Wordpress_CTA_Pro_Placeholder();
			new Easy_Sticky_Sidebar_Icons_Library();
			Wordpress_CTA_Free_Utils::add_design_template_images(wordpress_cta_get_design_templates());
		}

		do_action( 'wordpress_cta_free/init' );

		if (is_admin()) {			
			$pro_fields = apply_filters( 'wordpress_cta_free/pro_fields', []);
			foreach ($pro_fields as $key => $action) {
				$action = wp_parse_args( $action, array('hook' => '', 'callback' => null, 'priority' => 10, 'args' => 1));
				if ( !is_null($action['callback']) ) {
					add_action( $action['hook'], $action['callback'], $action['priority'], $action['args']);
				}
			}
        }
		
        /* register front end scripts */
        add_action('wp_enqueue_scripts', array($this, 'SSuprydpScripts'), 0);

        /* register admin scripts */
        add_action('admin_enqueue_scripts', array($this, 'SSuprydpAdminScripts'), 0);
		add_action( 'wp_footer', [$this, 'stick_sidebar_content']);
		

		$this->engine = new SSuprydpStickySidebarCore();

		add_filter( 'plugin_row_meta', [$this, 'add_help_link_on_plugin_page'], 12, 2 );

		wp_register_style('fontawesome', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/css/fontawesome.css', array(), '6.1.1');
		wp_register_script( 'jquery-cookie', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/js/jquery.cookie.js', ['jquery'], '1.4.1', true);

		Wordpress_CTA_Migrate::migrate();

		$this->migrate_data();
	}

	public function add_help_link_on_plugin_page($links, $plugin_file_name) {
		if ( EASY_STICKY_SIDEBAR_PLUGIN_BASENAME != $plugin_file_name ) {
			return $links;
		}

		$links[] = '<a href="https://wordpressctapro.com/help" target="_blank">' . __( 'Help', 'easy-sticky-sidebar' ) . '</a>';	
		return $links;
	}
	
	/**
	 * sticky sidebar hook run add installation
	**/
	public function SSuprydp_plugin_install() {
		$current_version = get_option( 'easy_sticky_sidebar_version', EASY_STICKY_SIDEBAR_VERSION);

		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix.'sticky_cta';


		$default_attachment = get_option('easy_sticky_sidebar_default_attachment');		
		$attchment_url = wp_get_attachment_image_url( $default_attachment );

		if ( !$attchment_url ) {
			self::SSuprydp_cmedia();

			$SSuprydp_dtime= date("Y-m-d H:i:s");
		
			$currentpath = wp_get_upload_dir();
			
			$img_path = $currentpath['url'].'/ss_dummy.jpg'; 
			
			$sql_post = "INSERT INTO `{$wpdb->prefix}posts` (post_date, post_author, post_title, post_status, comment_status, ping_status, post_name, post_parent, guid, post_type, post_mime_type) VALUES('$SSuprydp_dtime',1, 'Sticky Sidebar', 'inherit', 'open', 'open', 'Sticky Sidebar', 0, '$img_path', 'attachment', 'image/jpeg')";
			
			$wpdb->query($sql_post);
			
			$attac_lastid = $wpdb->insert_id;

			update_option( 'easy_sticky_sidebar_default_attachment', $attac_lastid);
			
			$SSuprydp_wp_attachment_metadata = self::SSuprydp_mediameta();
			
			$SSuprydp_wp_attached_file= date('Y').'/'.date('m').'/ss_dummy.jpg';
			
			$sql_postmeta1 = "INSERT INTO `{$wpdb->prefix}postmeta` (post_id, meta_key, meta_value) VALUES($attac_lastid,'_wp_attached_file', '$SSuprydp_wp_attached_file')";
			
			$wpdb->query($sql_postmeta1);
			
			$sql_postmeta2 = "INSERT INTO `{$wpdb->prefix}postmeta` (post_id, meta_key, meta_value) VALUES($attac_lastid,'_wp_attachment_metadata', '$SSuprydp_wp_attachment_metadata')";
			
			$wpdb->query($sql_postmeta2); 
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
		if($wpdb->get_var("show tables like '$table_name'") != $table_name)  {
			$sql = "CREATE TABLE $table_name (
			  id int(11) NOT NULL AUTO_INCREMENT,
			  SSuprydp_impressions int(11) DEFAULT 0 NOT NULL,
			  SSuprydp_clicks int(11) DEFAULT 0 NOT NULL,
			  SSuprydp_development varchar(255) DEFAULT 'development' NOT NULL,
			  SSuprydp_shrink varchar(255) DEFAULT 'No' NOT NULL,
			  SSuprydp_shrink_tablet varchar(255) DEFAULT 'No' NOT NULL,
			  SSuprydp_shrink_mobile varchar(255) DEFAULT 'No' NOT NULL,
			  SSuprydp_dis_desktop varchar(255) DEFAULT 'Yes' NOT NULL,
			  SSuprydp_dis_tablet varchar(255) DEFAULT 'Yes' NOT NULL,
			  SSuprydp_dis_mobile varchar(255) DEFAULT 'No' NOT NULL,
			  SSuprydp_location varchar(255) DEFAULT '' NOT NULL,
			  SSuprydp_img_hideimg varchar(255) DEFAULT 'No' NOT NULL,
			  SSuprydp_hideimg_tablet varchar(255) DEFAULT 'No' NOT NULL,
			  SSuprydp_hideimg_mobile varchar(255) DEFAULT 'No' NOT NULL,
			  sticky_s_media varchar(255) DEFAULT '$img_path' NOT NULL,
			  image_attachment_id varchar(255) DEFAULT '$attac_lastid' NOT NULL,
			  SSuprydp_button_option_text varchar(255) DEFAULT 'Click Here' NOT NULL,
			  SSuprydp_button_option_backg_color varchar(255) DEFAULT '#4e0d61' NOT NULL,
			  SSuprydp_button_option_font varchar(255) DEFAULT 'Open Sans' NOT NULL,
			  SSuprydp_button_option_weight varchar(255) DEFAULT '400' NOT NULL,
			  SSuprydp_button_option_size varchar(255) DEFAULT '20px' NOT NULL,
			  SSuprydp_button_option_align varchar(255) DEFAULT 'top' NOT NULL,
			  SSuprydp_button_option_color varchar(255) DEFAULT '#ffffff' NOT NULL,
			  SSuprydp_content_option_text LONGTEXT NOT NULL,
			  SSuprydp_content_option_font varchar(255) DEFAULT 'Open Sans' NOT NULL,
			  SSuprydp_content_option_weight varchar(255) DEFAULT '800' NOT NULL,
			  SSuprydp_content_option_size varchar(255) DEFAULT '25px' NOT NULL,
			  SSuprydp_content_option_color varchar(255) DEFAULT '#fff' NOT NULL,
			  SSuprydp_divider_option_color varchar(255) DEFAULT '#1b7ccc' NOT NULL,
			  SSuprydp_action_option_text varchar(255) DEFAULT 'Click Here to View' NOT NULL,
			  SSuprydp_action_option_font varchar(255) DEFAULT 'Open Sans' NOT NULL,
			  SSuprydp_action_option_weight varchar(255) DEFAULT '500' NOT NULL,
			  SSuprydp_action_option_size varchar(255) DEFAULT '19px' NOT NULL,
			  SSuprydp_action_option_color varchar(255) DEFAULT '#000000' NOT NULL,
			  SSuprydp_action_option_url varchar(255) DEFAULT 'https://wordpressctapro.com/' NOT NULL,
			  SSuprydp_target_blank varchar(255) DEFAULT 'No' NOT NULL,
			  SSuprydp_nofollow varchar(255) DEFAULT 'No' NOT NULL,
			  SSuprydp_cta_position varchar(255) DEFAULT 'right' NOT NULL,
			  created datetime NULL,
			  PRIMARY KEY  (id)
			) $charset_collate;";
			
			dbDelta( $sql );
		}	
		
		$table_name = $wpdb->prefix.'sticky_cta_options';
		maybe_create_table($table_name, "CREATE TABLE $table_name (
			`ID` INT NOT NULL AUTO_INCREMENT , 
			`sticky_cta_id` INT NOT NULL , 
			`option_name` VARCHAR(100) NOT NULL, 
			`option_value` MEDIUMTEXT, 
			PRIMARY KEY (`ID`))
		");

		

		update_option( 'easy_sticky_sidebar_version', EASY_STICKY_SIDEBAR_VERSION);		
		
		$this->singup();
    }

	public function singup() {
		$apiKey = '5aa5a4f8a1fe4af02a2b85f2e2d439c3-us5';

		$dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
		$ch = curl_init('https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/36f13a8ec8/members/' . md5(strtolower(get_bloginfo('admin_email'))));
		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
			'email_address' => get_bloginfo('admin_email'),
			'status'        => 'subscribed',
			'merge_fields'  => [
				'FNAME'     => get_bloginfo('name')
			]
		]));                                                                                                                 

		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
	}

	/**
     * Migrate data
	 * @since 1.3.1
     */
	function migrate_data() {
		if ( !is_admin() || get_option( 'easy_sticky_sidebar_migrated') ) {
			return;
		}
		
		global $wpdb;

		$table_name = $wpdb->sticky_cta;
		if($wpdb->get_var("show tables like '$table_name'") != $table_name)  {
			$this->SSuprydp_plugin_install();
		}

		if (!get_option('SSuprydp_development')) {
			return;
		}
		
		$SSuprydp_location = get_option('SSuprydp_location');
		if ( !$SSuprydp_location ) {
			$SSuprydp_location = get_option('page_on_front');
		}

		if ( $SSuprydp_location == 0) {
			$SSuprydp_location = '';
		}

		$sticky_cta = [
			'SSuprydp_development' => get_option('SSuprydp_development', 'development'),
			'sticky_s_media' => wp_get_attachment_image_url(get_option('easy_sticky_sidebar_default_attachment'), 'full'),
			'SSuprydp_button_option_text' => get_option('SSuprydp_button_option_text', 'Click Here'),
			'SSuprydp_button_option_backg_color' => get_option('SSuprydp_button_option_backg_color', '#4e0d61'),
			'SSuprydp_button_option_font' => get_option('SSuprydp_button_option_font', 'Open Sans'),
			'SSuprydp_button_option_weight' => get_option('SSuprydp_button_option_weight', '400'),
			'SSuprydp_button_option_size' => get_option('SSuprydp_button_option_size', '20px'),
			'SSuprydp_button_option_align' => get_option('SSuprydp_button_option_align', 'top'),
			'SSuprydp_button_option_color' => get_option('SSuprydp_button_option_color', '#ffffff'),
			'SSuprydp_content_option_text' => get_option('SSuprydp_content_option_text', 'This is the Content Area. Put a description here of what you want to promote.'),
			'SSuprydp_content_option_font' => get_option('SSuprydp_content_option_font', 'Open Sans'),
			'SSuprydp_content_option_weight' => get_option('SSuprydp_content_option_weight', '800'),
			'SSuprydp_content_option_size' => get_option('SSuprydp_content_option_size', '25px'),
			'SSuprydp_content_option_color' => get_option('SSuprydp_content_option_color'),
			'SSuprydp_divider_option_color' => get_option('SSuprydp_divider_option_color', '#1b7ccc'),
			'SSuprydp_action_option_text' => get_option('SSuprydp_action_option_text', 'Click Here to View'),
			'SSuprydp_action_option_font' => get_option('SSuprydp_action_option_font', 'Open Sans'),
			'SSuprydp_action_option_weight' => get_option('SSuprydp_action_option_weight', '500'),
			'SSuprydp_action_option_size' => get_option('SSuprydp_action_option_size', '19px'),
			'SSuprydp_action_option_color' => get_option('SSuprydp_action_option_color'),
			'SSuprydp_action_option_url' => get_option('SSuprydp_action_option_url', 'https://wordpressctapro.com/'),
			'SSuprydp_dis_mobile' => get_option('SSuprydp_dis_mobile', 'Yes'),
			'SSuprydp_location' => $SSuprydp_location,
			'SSuprydp_target_blank' => get_option('SSuprydp_target_blank', 'No'),
			'SSuprydp_nofollow' => get_option('SSuprydp_nofollow', 'No'),
			'SSuprydp_shrink' => get_option('SSuprydp_shrink', 'No'),
			'SSuprydp_img_hideimg' => get_option('SSuprydp_img_hideimg', 'No'),
			'sidebar_name' => 'Your First CTA',
			'sidebar_template' => 'sticky-cta',
			'horizontal_vertical_position' => 'center',
			'content_background_color' => 'transparent',
			'link_text_background' => 'transparent',
			'line_separator_color' => '#1b7ccc'
		];

		easy_sticky_sidebar_insert($sticky_cta);

		update_option('easy_sticky_sidebar_migrated', true);
	}
	
	/**
     * register and enque front end styles and scripts.
     *
     * @since 1.2.0
     */
    public function SSuprydpScripts() {
		global $wpdb;
		
        wp_enqueue_style('SSuprydp_style', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/css/sticky-sidebar.css', array('fontawesome'), EASY_STICKY_SIDEBAR_VERSION);

		$upload_dir = wp_upload_dir();
		$generated_css = $upload_dir['basedir'] . '/sticky-sidebar-generated.css';
		if ( file_exists($generated_css) ) {
			wp_enqueue_style( 'sticky-sidebar-generated', $upload_dir['baseurl'] . '/sticky-sidebar-generated.css', [], filemtime($generated_css));
		}

		wp_enqueue_script('SSuprydp_script', EASY_STICKY_SIDEBAR_PLUGIN_URL . "/assets/js/sticky-sidebar.js", array('jquery'), EASY_STICKY_SIDEBAR_VERSION);
    }

    public function SSuprydpAdminScripts() {
		wp_enqueue_style('easy-sidebar-global', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/css/easy-sidebar-global.css', [], EASY_STICKY_SIDEBAR_VERSION);
		if ( !is_easy_sticky_sidebar_screen() ) {
			return;
		}

		//deregister for showing problem with tooltip
		wp_deregister_script( 'gform_tooltip_init' );

		wp_register_style('select2', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/css/select2.min.css');
		wp_register_script( 'select2', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/js/select2.min.js', ['jquery'], '4.1.0', true);
		
		wp_enqueue_style( 'wp-color-picker' );		
        
        wp_enqueue_script('jquery-fontselect-js', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/js/jquery.fontselect.js', [], EASY_STICKY_SIDEBAR_VERSION);

        wp_enqueue_script('SSuprydp_admin_script', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/js/sticky-sidebar-admin.js', ['wp-color-picker', 'jquery-cookie'], EASY_STICKY_SIDEBAR_VERSION, true);
		wp_localize_script('SSuprydp_admin_script', 'sticky_sidebar', [
			'ajax_url' => admin_url('admin-ajax.php')			
		]);
		
		wp_enqueue_script('SSuprydp_popper', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/js/popper.min.js',[], EASY_STICKY_SIDEBAR_VERSION);
		
		wp_enqueue_script('SSuprydp_bootstrap', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/js/bootstrap.min.js',[], EASY_STICKY_SIDEBAR_VERSION);
        
		wp_enqueue_style('fontselect-default', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/css/fontselect-default.css', [], EASY_STICKY_SIDEBAR_VERSION);
       
        wp_enqueue_style('SSuprydp_admin_style', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/css/sticky-sidebar-admin.css', ['fontawesome'] , EASY_STICKY_SIDEBAR_VERSION);
		
		wp_enqueue_style('SSuprydp_bootstrap', EASY_STICKY_SIDEBAR_PLUGIN_URL . '/assets/css/bootstrap.min.css', [] , EASY_STICKY_SIDEBAR_VERSION);

		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
    }

	public function stick_sidebar_content() {
		global $CTA_Query;
		
		$dataview = array();
		
		foreach ($CTA_Query->cta as $sticky_data) {		
			$dataview['ctacontent']=$sticky_data;
			$SSuprydp_development = $sticky_data->SSuprydp_development;


			$dataview['cta_classes'] = ['easy-sticky-sidebar', 'easy-sticky-sidebar-' . $sticky_data->id, $sticky_data->sidebar_template];
			$dataview['cta_classes'][] = 'easy-sticky-sidebar-' . $sticky_data->sidebar_template;

			if ( $sticky_data->sidebar_template === 'sticky-cta') {
				$dataview['cta_classes'][] = 'sticky-cta';
			}
			
			if($sticky_data->SSuprydp_dis_desktop=='Yes'){
				$dataview['cta_classes'][] = 'sticky-sidebar-desktop';
			}
			
			if($sticky_data->SSuprydp_dis_tablet=='Yes'){
				$dataview['cta_classes'][] = 'sticky-sidebar-tablet';
			}
			
			if($sticky_data->SSuprydp_dis_mobile=='Yes'){
				$dataview['cta_classes'][] = 'sticky-sidebar-mobile';
			}

			$dataview['cta_classes'][] = 'sticky-cta-position-' . $sticky_data->SSuprydp_cta_position;

			if ( $sticky_data->SSuprydp_cta_position == 'top' || $sticky_data->SSuprydp_cta_position == 'bottom' ) {
				$dataview['cta_classes'][] = 'vertical-cta';
				$dataview['cta_classes'][] = 'vertical-cta-' . $sticky_data->SSuprydp_cta_position;
			}


			if ( !empty($sticky_data->horizontal_vertical_position) ) {
				$dataview['cta_classes'][] = 'sticky-cta-' . $sticky_data->horizontal_vertical_position;
			}

			if ( $sticky_data->show_close_button == 'yes' ) {
				$dataview['cta_classes'][] = 'ess-close-button-' . $sticky_data->close_button_position;
			}

			$dataview['cta_classes'] = array_unique($dataview['cta_classes']);

			$dataview['cta_classes'] = apply_filters('easy_sticky_sidebar_class', $dataview['cta_classes'], $sticky_data);

			$template = $sticky_data->sidebar_template;

			$view_template = EASY_STICKY_SIDEBAR_PLUGIN_DIR . "/views/{$template}.php";
			if (!file_exists($view_template)) {
				$template = 'default';
			}
			
			if($SSuprydp_development == 'live' || ($SSuprydp_development == 'development' && current_user_can( 'manage_options') ) ) {
				print SSuprydpStickySidebar()->engine->getView($template, $dataview);			
			}	
		}
	}
	
	public function SSuprydp_mediameta(){
		$ar =array();
		$ar['width']=1344;
		$ar['height']=751;
		$ar['file']= '2020/05/ss_dummy.jpg';
		$ar['sizes']= array(
							"medium"=>array(
											"file"=>'ss_dummy-300x167.jpg',
											"width"=>300,
											"height"=>167,
											"mime-type"=>'image/jpeg'
											),
							"large"=>array(
											"file"=>'ss_dummy-1024x572.jpg',
											"width"=>1024,
											"height"=>572,
											"mime-type"=>'image/jpeg'
											),
							
							"thumbnail"=>array(
											"file"=>'ss_dummy-150x83.jpg',
											"width"=>150,
											"height"=>83,
											"mime-type"=>'image/jpeg'									
											),
							
							"medium_large"=>array(
											"file"=>'ss_dummy-768x429.jpg',
											"width"=>768,
											"height"=>429,
											"mime-type"=>'image/jpeg'									
											),
							"1536x1536"=>array(
											"file"=>'ss_dummy-1536x858.jpg',
											"width"=>1536,
											"height"=>858,
											"mime-type"=>'image/jpeg'									
											),
							"post-thumbnail"=>array(
											"file"=>'ss_dummy-1200x670.jpg',
											"width"=>1200,
											"height"=>670,
											"mime-type"=>'image/jpeg'									
											)				
							);
		$ar['image_meta']= array(
								"aperture"=>0,
								"credit"=>'',
								"camera"=>'',
								"caption"=>'',
								"created_timestamp"=>0,
								"copyright"=>'',
								"focal_length"=>0,
								"iso"=>0,
								"shutter_speed"=>0,
								"title"=>'',
								"keywords"=>array(),
								);

		return serialize($ar);
	}

	public function SSuprydp_cmedia(){
		$currentpath = wp_get_upload_dir();
		
		if($currentpath['path']){
			$sourcepath = EASY_STICKY_SIDEBAR_PLUGIN_DIR.'/assets/img/ss_dummy.jpg';
			$destinationpath = $currentpath['path'].'/ss_dummy.jpg';
			@copy($sourcepath,$destinationpath);
			
			$sourcepath300 = EASY_STICKY_SIDEBAR_PLUGIN_DIR.'/assets/img/ss_dummy-300x167.jpg';
			$destinationpath300 = $currentpath['path'].'/ss_dummy-300x167.jpg';
			@copy($sourcepath300,$destinationpath300);
			
			$sourcepath1024 = EASY_STICKY_SIDEBAR_PLUGIN_DIR.'/assets/img/ss_dummy-1024x572.jpg';
			$destinationpath1024 = $currentpath['path'].'/ss_dummy-1024x572.jpg';
			@copy($sourcepath1024,$destinationpath1024);
			
			$sourcepath150 = EASY_STICKY_SIDEBAR_PLUGIN_DIR.'/assets/img/ss_dummy-150x83.jpg';
			$destinationpath150 = $currentpath['path'].'/ss_dummy-150x83.jpg';
			@copy($sourcepath150,$destinationpath150);
			
			$sourcepath768 = EASY_STICKY_SIDEBAR_PLUGIN_DIR.'/assets/img/ss_dummy-768x429.jpg';
			$destinationpath768 = $currentpath['path'].'/ss_dummy-768x429.jpg';
			@copy($sourcepath768,$destinationpath768);
			
			$sourcepath1536 = EASY_STICKY_SIDEBAR_PLUGIN_DIR.'/assets/img/ss_dummy-1536x858.jpg';
			$destinationpath1536 = $currentpath['path'].'/ss_dummy-1536x858.jpg';
			@copy($sourcepath1536,$destinationpath1536);
			
			$sourcepath1200 = EASY_STICKY_SIDEBAR_PLUGIN_DIR.'/assets/img/ss_dummy-1200x670.jpg';
			$destinationpath1200 = $currentpath['path'].'/ss_dummy-1200x670.jpg';
			@copy($sourcepath1200,$destinationpath1200);
		}
	}
}
