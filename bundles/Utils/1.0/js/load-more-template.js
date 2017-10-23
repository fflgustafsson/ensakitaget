;(function(){

	var loadMore = function(button, wrapper){

		var exclude = [];

		$.each($('.puff'), function(i, elem) {
			exclude.push($(elem).data('id'));
		});

		$.post(data.ajaxurl, {

			'action' : 'load_more',
			'paged' :  wrapper.data('paged'),
			'config' : wrapper.data('config'),
			'meta_value' : wrapper.data('meta-value')
			// 'exclude' : exclude

		}, function(data) {

			console.log(data);

			if (data.noMore) {
				button.fadeOut();
			}

			wrapper.data('paged', data.nextPage);
			$(data.posts.join('')).insertBefore('.load-more .action').fadeIn();

		});

	};

	$(document).on('click', '.load-more .action', function(){

		loadMore($(this), $(this).parents('.load-more'));

	});


})();