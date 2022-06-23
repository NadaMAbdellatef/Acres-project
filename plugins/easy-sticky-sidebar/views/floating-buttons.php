<?php 

$cta_classes[] = 'easy-sticky-sidebar ess-floating-buttons';




$floating_buttons = Wordpress_CTA_Free_Floating_Buttons::get_buttons($ctacontent);

if ( sizeof($floating_buttons) === 0 ) {
	return;
}

$has_text_items = array_filter($floating_buttons, function($button) {
	$text = trim($button->text);
	return strlen($text) > 0;
});

$hide_text = $ctacontent->hide_floating_button_text === 'yes' || sizeof($has_text_items) === 0;

if ( $hide_text ) {
	array_push($cta_classes, 'floating-button-no-text');
} ?>

<div id="<?php echo 'easy-sticky-sidebar-' . $ctacontent->id ?>" class="<?php echo esc_attr(implode(' ', $cta_classes)) ?>" data-id="<?php echo esc_attr($ctacontent->id); ?>">

	<ul class="floating-buttons-container">
		<?php foreach ($floating_buttons as $key => $button) : 
			$has_link = !empty($button->url); 
			$class = $has_link ? 'has-link' : '';
			printf('<li class="floating-button-%d %s">', $key, $class);

			ob_start();
			if ( $button->icon ) {
				printf('<i class="icon %s"></i>', esc_attr( $button->icon ));
			}

			if ( $button->text && $hide_text === false ) {
				echo esc_html( $button->text );
			}

			$html = ob_get_clean();

			if ( $has_link ) {
				$html = sprintf('<a href="%s">%s</a>', esc_url_raw($button->url), $html);
			}

			echo $html;

			echo '</li>';
		endforeach; ?>
	</ul>
</div>