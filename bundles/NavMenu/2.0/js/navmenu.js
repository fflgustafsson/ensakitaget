;(function($){
	$('#add-sb-custom-navmenu').on('click', function(e){
		e.preventDefault();

		var self = $(this),
			parent = self.parents('.sb-nav-menu'),
			checkboxes = parent.find('input:checked'),
			checked = [],
			sb_nav_menu_nonce = parent.find('#sb_nav_menu_nonce').val();


		parent.find('.spinner').show();

		checkboxes.each(function(){
			checked.push($(this).val());
		});


		self.prop( 'disabled', true );


		$.post(ajaxurl, {
			action: 'navmenu_add_custom',
			nav_menu_items: checked,
			nonce: sb_nav_menu_nonce
		}, function(data){

			$( '#menu-to-edit' ).append(data);
			parent.find('.spinner').hide();
            self.prop('disabled', false);
            checkboxes.prop('checked', false);
            wpNavMenu.initToggles();
		});

	});
})(jQuery);