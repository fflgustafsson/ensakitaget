;(function($){

	var sbUtils = sbUtils || {};

	// Copy post
	$('.row-actions').on('click', 'a.duplicate', function(e){
		e.preventDefault();

		var refresh = $(this).data('location');

		$.post(ajaxurl, {
			'action'  : 'duplicate_post',
			'post_id' : $(this).data('post-id')
		}, function(data) {
			if (data === 'OK') {
				window.location.href = refresh;
			}
		});

	});

	// Template meta boxes
	sbUtils.toggleMetabox = function(){

		var select = $('#page_template').val();

		if (typeof select === 'undefined') return;

		$('.sb-template-meta-box').hide();

		var template = select.split('/');
		template = template.pop();

		$('.sb-' + template.replace('.php', '')).fadeIn();

		return;

	};

	sbUtils.toggleMetabox();

	// Toggle Profiles metabox
	$('#page_template').on('change', sbUtils.toggleMetabox);

})(jQuery);