jQuery(document).ready(function ($) {
	var CTA_Click = [];

	$('.easy-sticky-sidebar .btn-ess-close').on('click', function (e) {
        e.stopPropagation();
        $(this).closest('.easy-sticky-sidebar').fadeOut(200, function(){
            $(this).remove();
        })
	})

	var width = $(window).width();

	if (width >= 767) {
		$(window).scroll(function () {
			var scroll = $(window).scrollTop();
			if (scroll <= 120) {
				return
			}

			jQuery('.easy-sticky-sidebar.sticky-cta:not(.scrolled)').each(function(){
				if ( $(this).hasClass('shrink-disabled') ) {
					return;
				}

				cta_id = parseInt($(this).data('id'));
				if ( !CTA_Click.includes(cta_id) ) {
					$(this).addClass('shrink scrolled');
				}
			})
		});
	}

	$('body').on('click', '.easy-sticky-sidebar .sticky-sidebar-button:not(a)', function (e) {
		e.preventDefault();

		current_cta = $(this).closest('.easy-sticky-sidebar');

		cta_id = parseInt(current_cta.data('id'));
		if ( cta_id > 0 && !CTA_Click.includes(cta_id) ) {
			CTA_Click.push(cta_id);
		}

		current_cta.toggleClass('shrink');
	})
});
