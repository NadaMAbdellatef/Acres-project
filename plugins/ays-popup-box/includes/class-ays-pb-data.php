<?php

class Ays_Pb_Data {

    public static function get_pb_by_id( $id ){
        global $wpdb;

        $ays_pb_table = $wpdb->prefix .'ays_pb';

        $results = '';
        if($id != null){
            $sql = "SELECT * FROM {$ays_pb_table} WHERE id =".$id;
            $results = $wpdb->get_results( $sql, 'ARRAY_A' );
        }

        return $results;
    }

    public static function get_pb_options_by_id( $id ){
        global $wpdb;
        $ays_pb_table = $wpdb->prefix .'ays_pb';

        $options = '';
        if($id != null){
            $sql = "SELECT options FROM {$ays_pb_table} WHERE id =".$id;
            $results = $wpdb->get_row( $sql, 'ARRAY_A' );

            $options = ( json_decode($results['options'], true) != null ) ? json_decode($results['options'], true) : array();
        }

        return $options;
    }

    public static function replace_message_variables($content, $data){
        foreach($data as $variable => $value){
            $content = str_replace("%%".$variable."%%", $value, $content);
        }
        return $content;
    }

    public static function get_category_by_id($id){
        global $wpdb;

        $ays_pb_category_table = $wpdb->prefix .'ays_pb_categories';

        $results = '';
        if($id != null){
            $sql = "SELECT * FROM {$ays_pb_category_table} WHERE id =".$id;
            $results = $wpdb->get_row( $sql, 'ARRAY_A' );
        }

        return $results;
    }

    public static function get_user_profile_data(){

        $user_first_name = '';
        $user_last_name  = '';
        $user_nickname   = '';
        $user_wordpress_roles = '';
        $user_id = get_current_user_id();
        if($user_id != 0){
            $usermeta = get_user_meta( $user_id );
            if($usermeta !== null){
                $user_first_name = (isset($usermeta['first_name'][0]) && $usermeta['first_name'][0] != '' ) ? sanitize_text_field( $usermeta['first_name'][0] ) : '';
                $user_last_name  = (isset($usermeta['last_name'][0]) && $usermeta['last_name'][0] != '' ) ? sanitize_text_field( $usermeta['last_name'][0] ) : '';
                $user_nickname   = (isset($usermeta['nickname'][0]) &&  $usermeta['nickname'][0] != '' ) ? sanitize_text_field( $usermeta['nickname'][0] ) : '';
            }
        }
        $current_user_data = get_userdata( $user_id );
        if ( ! is_null( $current_user_data ) && $current_user_data ) {
            $user_display_name    = ( isset( $current_user_data->data->display_name ) && $current_user_data->data->display_name != '' ) ? sanitize_text_field( $current_user_data->data->display_name ) : "";
            $user_wordpress_email = ( isset( $current_user_data->data->user_email ) && $current_user_data->data->user_email != '' ) ? sanitize_text_field( $current_user_data->data->user_email ) : "";

            $user_wordpress_roles = ( isset( $current_user_data->roles ) && ! empty( $current_user_data->roles ) ) ? $current_user_data->roles : "";

            if ( !empty( $user_wordpress_roles ) && $user_wordpress_roles != "" ) {
                if ( is_array( $user_wordpress_roles ) ) {
                    $user_wordpress_roles = implode(",", $user_wordpress_roles);
                }
            }
        }

        $message_data = array(
            'user_first_name'       => $user_first_name,
            'user_last_name'        => $user_last_name,
            'user_nickname'         => $user_nickname,
            'user_wordpress_roles'  => $user_wordpress_roles,
        );
		
        return $message_data;
    }

    /*
    ==========================================
        Sale Banner | Start
    ==========================================
    */

    public static function ays_pb_sale_baner(){
        if(isset($_POST['ays_pb_sale_btn_spring'])){
            update_option('ays_pb_sale_notification_spring', 1); 
            update_option('ays_pb_sale_date', current_time( 'mysql' ));
        }

        if(isset($_POST['ays_pb_sale_btn_spring_for_two_months'])){
            update_option('ays_pb_sale_dismiss_for_two_month_spring', 1);
            update_option('ays_pb_sale_date', current_time( 'mysql' ));
        }

        $ays_pb_sale_date = get_option('ays_pb_sale_date');
        $ays_pb_sale_two_months = get_option('ays_pb_sale_dismiss_for_two_month_spring');

        $val = 60*60*24*5;
        if($ays_pb_sale_two_months == 1){
            $val = 60*60*24*61;
        }

        $current_date = current_time( 'mysql' );
        $date_diff = strtotime($current_date) -  intval(strtotime($ays_pb_sale_date)) ;
        // $val = 60*60*24*5;
        $days_diff = $date_diff / $val;

        if(intval($days_diff) > 0 ){
            update_option('ays_pb_sale_notification_spring', 0); 
            update_option('ays_pb_sale_dismiss_for_two_month_spring', 0);
        }

        $ays_pb_ishmar = intval(get_option('ays_pb_sale_notification_spring'));
        $ays_pb_ishmar += intval(get_option('ays_pb_sale_dismiss_for_two_month_spring'));
        if($ays_pb_ishmar == 0 ){
            if (isset($_GET['page']) && strpos($_GET['page'], AYS_PB_NAME) !== false) {
                // $this->ays_pb_sale_message($ays_pb_ishmar);
                self::ays_pb_spring_bundle_message($ays_pb_ishmar);
            }
        }
    }

    public static function ays_pb_sale_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
                    $content[] = '<a href="https://ays-pro.com/wordpress/popup-box" target="_blank" class="ays-pb-sale-banner-link"><img src="' . AYS_PB_ADMIN_URL . '/images/helloween_sale.png"></a>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box">';

                        $content[] = '<strong>';
                            $content[] = __( "Pre-Halloween big sale on Popup Box plugin to spice up your website and prepare for the spooky season!<br><span style='color:#E85011;'>31%</span> SALE on <span style='color:#E85011;'>Popup Box</span> PRO!", AYS_PB_NAME );
                        $content[] = '</strong>';

                        $content[] = '<br>';

                        $content[] = '<strong>';
                                $content[] = __( "Hurry up! Ends on October 31. <a href='https://ays-pro.com/wordpress/popup-box' target='_blank'>Check it out!</a>", AYS_PB_NAME );
                        $content[] = '</strong>';
                        
                        $content[] = '<form action="" method="POST">';
                            $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
                        $content[] = '</form>';
                            
                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box">';

                        $content[] = '<div id="ays-pb-countdown-main-container">';
                            $content[] = '<div class="ays-pb-countdown-container">';

                                $content[] = '<div id="ays-pb-countdown">';
                                    $content[] = '<ul>';
                                        $content[] = '<li><span id="ays-pb-countdown-days"></span>days</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-hours"></span>Hours</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-minutes"></span>Minutes</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-seconds"></span>Seconds</li>';
                                    $content[] = '</ul>';
                                $content[] = '</div>';

                                $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                                    $content[] = '<span>🚀</span>';
                                    $content[] = '<span>⌛</span>';
                                    $content[] = '<span>🔥</span>';
                                    $content[] = '<span>💣</span>';
                                $content[] = '</div>';

                            $content[] = '</div>';
                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<a href="https://ays-pro.com/wordpress/popup-box" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank" style="height: 32px; display: flex; align-items: center; font-weight: 500; " >' . __( 'Buy Now !', AYS_PB_NAME ) . '</a>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );
            echo $content;
        }
    }

    public static function ays_pb_winter_bundle_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
                    $content[] = '<a href="https://ays-pro.com/winter-bundle" target="_blank" class="ays-pb-sale-banner-link"><img src="' . AYS_PB_ADMIN_URL . '/images/winter_bundle_logo.png"></a>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box">';

                        $content[] = '<strong>';
                            $content[] = __( "Limited Time <span class='ays-pb-dicount-wrap-color'>50%</span> SALE on <br><span><a href='https://ays-pro.com/winter-bundle' target='_blank' class='ays-pb-dicount-wrap-color ays-pb-dicount-wrap-text-decoration' style='display:block;'>Winter Bundle</a></span> (Copy + Popup + Survey)!", AYS_PB_NAME );
                        $content[] = '</strong>';

                        $content[] = '<br>';

                        $content[] = '<strong>';
                                $content[] = __( "Hurry up! Ending on. <a href='https://ays-pro.com/winter-bundle' target='_blank'>Check it out!</a>", AYS_PB_NAME );
                        $content[] = '</strong>';
                            
                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box">';

                        $content[] = '<div id="ays-pb-countdown-main-container">';
                            $content[] = '<div class="ays-pb-countdown-container">';

                                $content[] = '<div id="ays-pb-countdown">';
                                    $content[] = '<ul>';
                                        $content[] = '<li><span id="ays-pb-countdown-days"></span>days</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-hours"></span>Hours</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-minutes"></span>Minutes</li>';
                                        $content[] = '<li><span id="ays-pb-countdown-seconds"></span>Seconds</li>';
                                    $content[] = '</ul>';
                                $content[] = '</div>';

                                $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                                    $content[] = '<span>🚀</span>';
                                    $content[] = '<span>⌛</span>';
                                    $content[] = '<span>🔥</span>';
                                    $content[] = '<span>💣</span>';
                                $content[] = '</div>';

                            $content[] = '</div>';

                            $content[] = '<form action="" method="POST">';
                                $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn_winter" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
                                $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn_winter_for_two_months" style="height: 32px; padding-left: 0">Dismiss ad for 2 months</button>';
                            $content[] = '</form>';

                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<a href="https://ays-pro.com/winter-bundle" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . __( 'Buy Now !', AYS_PB_NAME ) . '</a>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );
            echo $content;
        }
    }

    public static function ays_pb_spring_bundle_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-pb-dicount-month-main" class="notice notice-success is-dismissible ays_pb_dicount_info">';
                $content[] = '<div id="ays-pb-dicount-month" class="ays_pb_dicount_month">';
                    $content[] = '<a href="https://ays-pro.com/spring-bundle" target="_blank" class="ays-pb-sale-banner-link"><img src="' . AYS_PB_ADMIN_URL . '/images/spring_bundle_logo_box.png"></a>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box">';
                        $content[] = '<p style="margin: 0;">';
                            $content[] = '<strong>';
                                $content[] = __( "Spring is here! 
                                                    <span class='ays-pb-dicount-wrap-color'>50%</span> 
                                                        SALE on 
                                                    <span>
                                                        <a href='https://ays-pro.com/spring-bundle' target='_blank' class='ays-pb-dicount-wrap-color ays-pb-dicount-wrap-text-decoration'>
                                                            Spring Bundle
                                                        </a>
                                                    </span>
                                                    <span style='display: block;'>
                                                        Quiz + Popup + Copy
                                                    </span>", AYS_PB_NAME );
                            $content[] = '</strong>';
                            $content[] = '<br>';
                            // $content[] = '<strong>';
                            //         $content[] = __( "Hurry up! Ending on. <a href='https://ays-pro.com/spring-bundle' target='_blank'>Check it out!</a>", AYS_PB_NAME );
                            // $content[] = '</strong>';
                        $content[] = '</p>';
                    $content[] = '</div>';

                    $content[] = '<div class="ays-pb-dicount-wrap-box">';

                        $content[] = '<div id="ays-pb-countdown-main-container">';
                            // $content[] = '<div class="ays-pb-countdown-container">';

                            //     $content[] = '<div id="ays-pb-countdown">';
                            //         $content[] = '<ul>';
                            //             $content[] = '<li><span id="ays-pb-countdown-days"></span>days</li>';
                            //             $content[] = '<li><span id="ays-pb-countdown-hours"></span>Hours</li>';
                            //             $content[] = '<li><span id="ays-pb-countdown-minutes"></span>Minutes</li>';
                            //             $content[] = '<li><span id="ays-pb-countdown-seconds"></span>Seconds</li>';
                            //         $content[] = '</ul>';
                            //     $content[] = '</div>';

                            //     $content[] = '<div id="ays-pb-countdown-content" class="emoji">';
                            //         $content[] = '<span>🚀</span>';
                            //         $content[] = '<span>⌛</span>';
                            //         $content[] = '<span>🔥</span>';
                            //         $content[] = '<span>💣</span>';
                            //     $content[] = '</div>';

                            // $content[] = '</div>';

                            $content[] = '<form action="" method="POST">';
                                $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn_spring" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
                                $content[] = '<button class="btn btn-link ays-button" name="ays_pb_sale_btn_spring_for_two_months" style="height: 32px; padding-left: 0">Dismiss ad for 2 months</button>';
                            $content[] = '</form>';

                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<a href="https://ays-pro.com/spring-bundle" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . __( 'Buy Now !', AYS_PB_NAME ) . '</a>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );
            echo $content;
        }
    }

    /*
    ==========================================
        Sale Banner | End
    ==========================================
    */
}
