<?php

/*
 * StickySidebar Actions
 * @package wp-dynamic-shortcodes/inc
 * @since   1.2.0
 */

class SSuprydpproActions {

    /**
     * StickySidebar Constructor.
     */
    function __construct() {
        foreach ($this->AjaxActions() as $key => $action) {
            add_action("wp_ajax_{$action['name']}", [$this, $action['callback']]);
            add_action("wp_ajax_nopriv_{$action['name']}", [$this, $action['callback']]);
        }

		add_action( 'wp_ajax_update_cta_status', [$this, 'update_cta_status']);
        add_action( 'wp_ajax_nopriv_update_cta_status', [$this, 'update_cta_status']);

        add_action( 'wp_ajax_change_sticky_sidebar_name', [$this, 'change_sticky_sidebar_name'] );
        add_action( 'wp_ajax_nopriv_change_sticky_sidebar_name', [$this, 'change_sticky_sidebar_name'] );
        
        add_action('easy_sticky_sidebar_after_save', [$this, 'redirect_after_creating_new_sidebar'], 10, 3);        
    }

	public function update_cta_status() {
        global $wpdb;

        $post_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $sticky_id = $post_data['sticky_id'];

        $sticky = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->sticky_cta WHERE id = %d", $sticky_id));
        if ( !$sticky ) {
            wp_send_json( ['success' => false, 'error' => __('Sticky item not exists.', 'wordpress-cta-pro')]);
        }

        $wpdb->update(
            $wpdb->sticky_cta, 
            array( 'SSuprydp_development' => $post_data['status'] ), 
            array( 'id' => $sticky_id),
            array( '%s'),
            array( '%d' )
        );

        wp_send_json( ['success' => true] );
    }

	public function change_sticky_sidebar_name() {
		global $wpdb;

        $post_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$sticky_id = $post_data['sticky'];

		$exists = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->sticky_cta_options WHERE option_name = 'sidebar_name' AND sticky_cta_id = %d", $sticky_id));

        $data_format = array( '%d', '%s', '%s');

		$data = ['sticky_cta_id' => $sticky_id, 'option_name' => 'sidebar_name', 'option_value' => $post_data['name']];
		if ( $exists ) {
			$data['ID'] = $exists;
            array_push($data_format, '%d');
		}
		
		$wpdb->replace($wpdb->sticky_cta_options, $data, $data_format);
        die('');
    }

    /*
     * SSuprydpStickySidebar ajax handlers
     *
     * @return Array
     */

    private function AjaxActions() {
        return [
            ['name' => 'process_pages', 'callback' => 'processPages'],
            ['name' => 'ajax_check', 'callback' => 'ajaxCheck'],
            ['name' => 'validate_data', 'callback' => 'validateData'],
        ];
    }

    function content_filter($tags, $context) {
        $tags['iframe'] = array(
            'src'               => true,
            'height'            => true,
            'width'             => true,
            'allow'             => true,
            'frameborder'       => true,
            'allowfullscreen'   => true,
        );
    
        return $tags;        
    }

    /**
     *
     * @global type $wpdb
     * @return JSON
     */
    public function processPages() {
        global $wpdb;

        if(!isset($_POST)){
            wp_send_json(['status' => 'failed', 'message' => 'Data missing']);
        }

        $postdata = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        add_filter( 'wp_kses_allowed_html', [$this, 'content_filter'], 10, 2 );
        $postdata['SSuprydp_content_option_text'] = wp_kses_stripslashes(wp_kses_post($_POST['SSuprydp_content_option_text'], wp_kses_allowed_html()));
        remove_filter('wp_kses_allowed_html', [$this, 'content_filter'], 2 );

        $postdata['SSuprydp_content_option_text'] = apply_filters('wordpress_cta_free/cta_content', $postdata['SSuprydp_content_option_text'], $postdata);

        $switch_fields = apply_filters('wordpress_cta_free/swtich_inputs', ['SSuprydp_target_blank', 'SSuprydp_nofollow', 'SSuprydp_shrink', 'SSuprydp_shrink_tablet', 'SSuprydp_shrink_mobile', 'SSuprydp_dis_desktop', 'SSuprydp_dis_tablet', 'SSuprydp_dis_mobile', 'SSuprydp_img_hideimg', 'SSuprydp_hideimg_tablet', 'SSuprydp_hideimg_mobile']);

        while ($switch = current($switch_fields)) {
            next($switch_fields);
            if ( !isset($postdata[$switch]) ) {
                $postdata[$switch] = 'No';
            }
        }

        if (is_array($postdata)) {
            $postdata['created'] = get_the_date();

            $sticky_id = $postdata['sticky_id'];
            unset($postdata['sticky_id']);

            $postdata['id'] = $sticky_id;
            easy_sticky_sidebar_insert($postdata);
            
            wp_send_json(['status' => 'success', 'message' => 'Saved']);
        }

        wp_send_json(['status' => 'failed', 'message' => 'Data missing']);
    }

    public function redirect_after_creating_new_sidebar($postdata, $sticky_id, $new) {
        if ( $new && wp_doing_ajax() ) {
            wp_send_json(['status' => 'success', 'message' => 'Saved', 'redirect' => add_query_arg( ['page' => 'edit-easy-sticky-sidebar', 'id' => $sticky_id], admin_url('admin.php') )]);
        }
    }

    /**
     * actions init ajaxCheck
     */
    public function ajaxCheck(){
        global $wpdb;

        $table_name = $wpdb->prefix.'sticky_cta';

        if(isset($_POST)){
            $postdata = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);;

            if (is_array($postdata)) {

                if(isset($postdata['sticky_id']) && $postdata['sticky_id']>0 ){
                    $id = $postdata['sticky_id'];                    

                    $wpdb->update( $table_name, [$postdata['fildname']=>$postdata['fieldval']], array('id'=>$id));

                }
                echo 'Success';
               exit;

            } else {
               echo 'failed';
               exit;
            }
        }
    }

	/**
     * validate data
     * @param array $postdata
     * @return array
     */
    public function validateData() {

		$postdata = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $return = ['errors' => null, 'where' => null];
        $button_text = SSuprydpStickySidebar()->engine->getValue('SSuprydp_button_option_text', $postdata, false);
		
	    if (!$button_text) {
			$return['page_name'] = __("Please enter button text");
			wp_send_json(['status' => 'failed', 'errors' => $return]);
        }
        else{
			$response['status'] = 'success';
            $response['content'] = 'Fields are added successfully.';
            wp_send_json($response);
		}
    }


}
