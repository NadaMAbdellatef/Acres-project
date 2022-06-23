<?php
$action = (isset($_GET['action'])) ? sanitize_text_field( $_GET['action'] ) : '';
$heading = '';
$loader_iamge = '';
$id = ( isset( $_GET['popup_category'] ) ) ? absint( intval( $_GET['popup_category'] ) ) : null;
$popup_category = array(
    'id'            => '',
    'title'         => '',
    'description'   => '',
    'published'     => ''
);
switch( $action ) {
    case 'add':
        $heading = __('Add new category', $this->plugin_name);
        $loader_iamge = "<span class='display_none'><img src=".AYS_PB_ADMIN_URL."/images/loaders/loading.gif></span>";
        break;
    case 'edit':
        $heading = __('Edit category', $this->plugin_name);
        $loader_iamge = "<span class='display_none'><img src=".AYS_PB_ADMIN_URL."/images/loaders/loading.gif></span>";
        $popup_category = $this->popup_categories_obj->get_popup_category( $id );
        break;
}
if( isset( $_POST['ays_submit'] ) ) {
    $_POST['id'] = $id;
    $result = $this->popup_categories_obj->add_edit_popup_category();
}
if(isset($_POST['ays_apply'])){
    $_POST["id"] = $id;
    $_POST['ays_change_type'] = 'apply';
    $this->popup_categories_obj->add_edit_popup_category();
}

// General Settings | options
$gen_options = ($this->settings_obj->ays_get_setting('options') === false) ? array() : json_decode( stripcslashes($this->settings_obj->ays_get_setting('options') ), true);

// WP Editor height
$pb_wp_editor_height = (isset($gen_options['pb_wp_editor_height']) && $gen_options['pb_wp_editor_height'] != '') ? absint( sanitize_text_field($gen_options['pb_wp_editor_height']) ) : 150 ;

//Category title
$categoty_title = ( isset( $popup_category['title'] ) && $popup_category['title'] != '' ) ? stripslashes( $popup_category['title'] ) : '';

//Category description
$category_description = ( isset( $popup_category['description'] ) && $popup_category['description'] != '' ) ? stripslashes( $popup_category['description'] ) : '';

//Published Category
$published_category = ( isset($popup_category['published'] ) && $popup_category['published'] != '' ) ? stripslashes($popup_category['published'] ) : '1';

$next_pb_cat_id = "";
if ( isset( $id ) && !is_null( $id ) ) {
    $next_pb_cat_data = $this->get_next_or_prev_row_by_id( $id, "next", "ays_pb_categories" );
    $next_pb_cat_id = (isset( $next_pb_cat_data['id'] ) && $next_pb_cat_data['id'] != "") ? absint( $next_pb_cat_data['id'] ) : null;
}
$prev_pb_cat_id = "";
if ( isset( $id ) && !is_null( $id ) ) {
    $prev_pb_cat_data = $this->get_next_or_prev_row_by_id( $id, "prev", "ays_pb_categories" );
    $prev_pb_cat_id = (isset( $prev_pb_cat_data['id'] ) && $prev_pb_cat_data['id'] != "") ? absint( $prev_pb_cat_data['id'] ) : null;
}

?>
<div class="wrap">
    <div class="container-fluid">
        <div class="ays-pb-heading-box">
            <div class="ays-pb-wordpress-user-manual-box">
                    <a href="https://ays-pro.com/wordpress-popup-box-plugin-user-manual" target="_blank"><?php echo __("View Documentation", $this->plugin_name); ?></a>
            </div>
        </div>
        <h1><?php echo $heading; ?></h1>
        <hr/>
        <form class="ays-pb-category-form" id="ays-pb-category-form" method="post">
            <input type="hidden" class="pb_wp_editor_height" value="<?php echo $pb_wp_editor_height; ?>">
            <div class="form-group row">
                <div class="col-sm-2">
                    <label for='ays-title'>
                        <?php echo __('Category name', $this->plugin_name); ?>
                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Define the category name.',$this->plugin_name)?>">
                            <i class="ays_fa ays_fa-info-circle"></i>
                        </a>
                    </label>
                </div>
                <div class="col-sm-10">
                    <input class='ays-text-input' id='ays-title' name='ays_title' required type='text' value='<?php echo esc_attr($categoty_title); ?>'>
                </div>
            </div>

            <hr/>
            <div class='ays-field'>
                <label for='ays-description'>
                    <?php echo __('Description', $this->plugin_name); ?>
                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Write category description if necessary.',$this->plugin_name)?>">
                        <i class="ays_fa ays_fa-info-circle"></i>
                    </a>
                </label>
                <?php
                $content = $category_description;
                $editor_id = 'ays-description';
                $settings = array('editor_height'=>$pb_wp_editor_height,'textarea_name'=>'ays_description','editor_class'=>'ays-textarea');
                wp_editor($content, $editor_id, $settings);
                ?>
            </div>

            <hr/>
            <div class="form-group row">
                <div class="col-sm-2">
                    <label>
                        <?php echo __('Category status', $this->plugin_name); ?>
                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Select whether or not to display the new category in the settings.',$this->plugin_name)?>">
                            <i class="ays_fa ays_fa-info-circle"></i>
                        </a>
                    </label>
                </div>

                <div class="col-sm-3">
                    <div class="form-check form-check-inline">
                        <input type="radio" id="ays-publish" name="ays_publish" value="1" <?php echo ( $published_category == '' ) ? "checked" : ""; ?> <?php echo ( $published_category == '1') ? 'checked' : ''; ?> />
                        <label class="form-check-label" for="ays-publish"> <?php echo __('Published', $this->plugin_name); ?> </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" id="ays-unpublish" name="ays_publish" value="0" <?php echo ( $published_category  == '0' ) ? 'checked' : ''; ?> />
                        <label class="form-check-label" for="ays-unpublish"> <?php echo __('Unpublished', $this->plugin_name); ?> </label>
                    </div>
                </div>
            </div>

            <hr/>
            <div class="form-group row ays-pb-button-box">
                <div class="col-sm-10 ays-pb-button-first-row" style="padding: 0;">
                <?php
                    wp_nonce_field('popup_category_action', 'popup_category_action');
                    $other_attributes = array( 'id' => 'ays-cat-button-apply' );
                    $other_attributes_save = array( 'id' => 'ays-cat-button-apply' );
                    submit_button( __( 'Save and close', $this->plugin_name ), 'primary', 'ays_submit', false, $other_attributes );
                    submit_button( __( 'Save', $this->plugin_name), '', 'ays_apply', false, $other_attributes_save);
                    echo $loader_iamge;
                ?>
                </div>
                <div class="col-sm-2 ays-pb-button-second-row">
                <?php
                    if ( $prev_pb_cat_id != "" && !is_null( $prev_pb_cat_id ) ) {
                        $other_attributes = array(
                            'id' => 'ays-pb-category-prev-button',
                            'data-message' => __( 'Are you sure you want to go to the previous popup category page?', $this->plugin_name),
                            'href' => sprintf( '?page=%s&action=%s&popup_category=%d', esc_attr( $_REQUEST['page'] ), 'edit', absint( $prev_pb_cat_id ) )
                        );
                        submit_button(__('Previous Popup Category', $this->plugin_name), 'button button-primary ays_default_btn ays-pb-next-prev-button-class ays-button', 'ays_pb_category_prev_button', false, $other_attributes);
                    }
                ?>
                <?php
                    if ( $next_pb_cat_id != "" && !is_null( $next_pb_cat_id ) ) {
                        $other_attributes = array(
                            'id' => 'ays-pb-category-next-button',
                            'data-message' => __( 'Are you sure you want to go to the next popup category page?', $this->plugin_name),
                            'href' => sprintf( '?page=%s&action=%s&popup_category=%d', esc_attr( $_REQUEST['page'] ), 'edit', absint( $next_pb_cat_id ) )
                        );
                        submit_button(__('Next Popup Category', $this->plugin_name), 'button button-primary ays_default_btn ays-pb-next-prev-button-class ays-button', 'ays_pb_category_next_button', false, $other_attributes);
                    }
                ?>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    jQuery(document).ready(function($){
        $('[data-toggle="tooltip"]').tooltip({
            template: '<div class="tooltip ays-pb-custom-class-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
        });
    });    
</script>