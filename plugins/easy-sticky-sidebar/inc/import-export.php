<?php
class Wordpress_CTA_Import_Export {

    public function __construct() {
		$this->export_cta();
		$this->import_sidebars();
    }

	function export_cta() {
        if ( !isset($_REQUEST['_nonce_export']) || !wp_verify_nonce( $_REQUEST['_nonce_export'], '_nonce_export_cta' )) {
            return;
        }

		$items = $_POST['cta'];

		if ( empty($items)) {
			return;
		}

        global $wpdb;
        $results = $wpdb->get_results(sprintf("SELECT * FROM $wpdb->sticky_cta WHERE id IN (%s)", implode(', ', $items)));
		
		array_walk($results, function(&$item){
			$item = new WP_Sticky_CTA_Data($item);
            unset($item->id, $item->image_attachment_id, $item->locations);            
        });

		$file_name = sprintf("%s-%s-%s", sanitize_title(get_bloginfo( 'name')), 'multiple-cta', time());
		if ( count($results) == 1) {
			$file_name = sprintf("%s-%s-%s", sanitize_title(get_bloginfo( 'name')), sanitize_title($results[0]->sidebar_name), time());
		}	

        header('Content-disposition: attachment; filename='.$file_name.'.json');
        header('Content-type: application/json');
        echo json_encode($results);
        exit;
    }
	
	public function import_sidebars() {
		if ( !isset($_FILES['cta-import']) || !isset($_REQUEST['_nonce']) || !wp_verify_nonce($_REQUEST['_nonce'], 'nonce_import_field' ) ) {
			return;
		}

		$sidebars = @file_get_contents($_FILES['cta-import']['tmp_name']);
		$sidebars = json_decode($sidebars);
		if ( !$sidebars || !is_array($sidebars) ) {
			return;
		}		

		$medias = [];

		while ($sidebar = current($sidebars)) {
			next($sidebars);
			
			
			if ( !isset($medias[$sidebar->sticky_s_media] ) ) {

				$image_content = @file_get_contents($sidebar->sticky_s_media);

				if ( $image_content ) {
					$filename = basename($sidebar->sticky_s_media);
		            $upload = wp_upload_bits( $filename, null, $image_content);

		            if ( $upload['error'] == false ) {
		                $attach_id = wp_insert_attachment([
							'guid' => $upload['url'],
							'post_mime_type' => $upload['type'],
							'post_title' => sanitize_file_name( $filename ),
							'post_content' => '',
							'post_status' => 'inherit'
						], $upload['file']);

						$medias[$sidebar->sticky_s_media] = ['id' => $attach_id, 'guid' => $upload['url']];
						$sidebar->image_attachment_id = $attach_id;
						$sidebar->sticky_s_media = $upload['url'];
			
						require_once( ABSPATH . 'wp-admin/includes/image.php' );
						$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
						wp_update_attachment_metadata( $attach_id, $attach_data );
		            }
				}

			} else {
				$sidebar->image_attachment_id = $medias[$sidebar->sticky_s_media]['id'];
				$sidebar->sticky_s_media = $medias[$sidebar->sticky_s_media]['guid'];
			}          

			easy_sticky_sidebar_insert($sidebar);
		}

		exit(wp_safe_redirect( add_query_arg('settings-updated', true, $_REQUEST['_wp_http_referer']) ));
	}

	public function output() {
		global $wpdb;

		$sidebars = $wpdb->get_results("SELECT * FROM $wpdb->sticky_cta");
		array_walk($sidebars, function(&$item){
			$item = new WP_Sticky_CTA_Data($item);
		}); ?>
		<div class="wrap wrap-easy-sticky-sidebar">
			<?php get_easy_sticky_sidebar_header(['class' => 'medium']) ?>

		    <div class="easy-sticky-sidebar-container medium">
		        <hr class="wp-header-end">
		        <div class="easy-sticky-sidebar-tab-panel">
		            <nav class="tab-nav">
		                <a class="active" href="#tab-content-export"><?php _e('Export', 'wordpress-cta-pro') ?></a>
		                <a href="#tab-content-import"><?php _e('Import', 'wordpress-cta-pro') ?></a>
		            </nav>

		            <div class="easy-sticky-sidebar-tab-content">
		                <div id="tab-content-export">
		                    <header><?php _e('Export CTA', 'wordpress-cta-pro') ?></header>

							<form method="post">
								<?php wp_nonce_field( '_nonce_export_cta', '_nonce_export') ?>

			                    <table class="form-table form-table-export">
			                        <tbody>
			                            <tr valign="top">
			                                <th scope="row">Select Items</th>
			                                <td>
			                                    <ul class="export-cta-list">
			                                        <li><label><input type="checkbox" data-select="all"> Select All</label></li>
			                                        
													<?php foreach ($sidebars as $sidebar) {
														printf('<li><label><input type="checkbox" name="cta[]" value="%d" /> %s</label></li>', $sidebar->id, $sidebar->sidebar_name);
													} ?>
			                                    </ul>
			                                </td>
			                            </tr>
			                        </tbody>
			                    </table>

								<?php submit_button( __( 'Export', 'wordpress-cta-pro' ), 'primary', 'action', false); ?>
							</form>
						</div>

		                <div id="tab-content-import">
		                    <header><?php _e('Import CTA', 'wordpress-cta-pro') ?></header>
							<?php 
								if ( isset($_GET['settings-updated'])) {
									echo '<div class="updated"><p>Successfully imported</p></div>';
								}
							?>

		                    <form method="post" enctype="multipart/form-data">
		                        <?php wp_nonce_field( 'nonce_import_field', '_nonce') ?>
		                        <p><input name="cta-import" type="file"></p>
		                        <?php submit_button( __( 'Import', 'wordpress-cta-pro' ), 'primary', 'action', false); ?>
		                    </form>
		                </div>
		            </div>
		        </div>
		    </div>
		</div>
		<?php
	}
}

return new Wordpress_CTA_Import_Export();