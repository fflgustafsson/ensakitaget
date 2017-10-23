;(function($){

	// Request regeneration
	$('.wp-list-table').on('click', '.regenerate-image', function(e) {
		e.preventDefault();
		$.post(ajaxurl, {
			'action'	: 'regenerate_image',
			'media_id'	: $(this).data('image-id'),
			'type'		: 'single'
		}, function(data) {
			$('h2').after(data.status).next().delay(2000).fadeOut('fast');
		}, 'json');
	});

	var progressBar = $('.progress-bar-wrapper'),
		progress = $('.progress', progressBar),
		text = $('span', progressBar),
		response = $('.regenerate-all-response'),
		spinner = $('.spinner'),
		allIds,
		allIdsCount = 0,
		percent = 0;
		processCount = 0;

	var regenerateNextImage = function(){

		var id = allIds.pop();

		if (typeof id === 'undefined') return;

		$.post(ajaxurl, {
			'action'	: 'regenerate_image',
			'media_id'	: id,
			'type'		: 'all'
		}, function(data) {

			var elem = $('.regenerate-all-response'),
				li = false;

			if ('undefined' === typeof(data.status.error)) {
				li = $('<li />').text('media id #' + id + ': ' + data.status.file + ' ' + data.status.status);
			} else {
				url = '/wp-admin/post.php?post=' + id + '&action=edit';
				li = $('<li />', { 'class': 'error' }).html('<a style="color: red;" href="' + url + '" target="_blank">#' + id + ': ' + data.status.error + '</a>');
			}

			elem.append(li);

			processCount++;
			text.text(processCount + '/' + allIdsCount);
			percent = Math.floor((processCount / allIdsCount * 100));
			progress.css({ width: percent + '%' });

			progressBar.trigger('process-done');

			if (allIds.length !== 0) {
				regenerateNextImage();
				regenerateNextImage();
				regenerateNextImage();
				regenerateNextImage();
			}


		}, 'json');

	};

	// Track progress
	progressBar.on('process-done', function(){

		if (processCount === allIdsCount) {

			text.text('Klart!');
			$('#regenerate-all').removeAttr('disabled');
			percent = 0;
			processCount = 0;
			spinner.hide();
			return;

		}

	});

	// Request regeneration all
	$('#regenerate-all').on('click', function(e) {
		e.preventDefault();

		response.html('');
		text.text('');
		progress.css({ width: 0 });
		$('.progress-bar-wrapper').show();

		var button = $(this).attr('disabled', 'disabled');
		spinner.show();

		$.post(ajaxurl, {
			'action'	: 'get_all_attachment_ids'
		}, function(data) {

			allIds = data;
			allIdsCount = allIds.length;

			text.text('0' + '/' + allIdsCount);

			regenerateNextImage();
			regenerateNextImage();
			regenerateNextImage();
			regenerateNextImage();

		}, 'json');
	});

})(jQuery);

