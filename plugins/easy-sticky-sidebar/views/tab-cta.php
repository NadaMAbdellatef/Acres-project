<?php 

$cta_links_attrs = sprintf('href="%s"', $ctacontent->tab_cta_url);
if ( $ctacontent->tab_cta_target_blank == 'yes' ) {
	$cta_links_attrs .= ' target="_blank"';
}

if ( $ctacontent->tab_cta_nofollow == 'yes' ) {
	$cta_links_attrs .= ' rel="nofollow"';
}

ob_start(); ?>

<div id="<?php echo 'easy-sticky-sidebar-' . $ctacontent->id ?>" class="<?php echo esc_attr(implode(' ', $cta_classes)) ?>" data-id="<?php echo esc_attr($ctacontent->id); ?>">
	<a class="sticky-sidebar-button" <?php echo $cta_links_attrs; ?>>
		<div><?php echo $ctacontent->SSuprydp_button_option_text ?></div>
	</a>
	<?php 
	if ( function_exists('wordpress_cta_pro_get_close_button')) {
		wordpress_cta_pro_get_close_button($ctacontent);
	} ?>
</div>
<?php

echo ob_get_clean();