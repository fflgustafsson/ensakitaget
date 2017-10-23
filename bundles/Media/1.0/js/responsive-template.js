;(function(){

	// Image load
	var globalW = $(window).width(),
		rTimeout = null,
		responsiveImage = $('.responsive-image');

	var returnMedia = function(requestedWidth, media)
	{

		var elem;

		for (var width in media) {

			var obj = media[width],
				max = Math.max(width, requestedWidth);

			if (max > requestedWidth || requestedWidth == width) {
				elem = obj;
				break;
			}

		}

		elem.tag = $('<img />', { 'src' : elem.src, 'alt' : elem.alt });

		return elem;

	};

	var loadResponsive = function(elem){

		if (typeof elem === 'undefined') {
			elem = responsiveImage;
		}

		$.each(elem, function(index, val) {

			var figure = $(this),
				img = $(this).find('img'),
				ratio = (window.devicePixelRatio) ? window.devicePixelRatio : 1,
				width = figure.width() * ratio,
				media = figure.data('media');

			if (0 === media.length) return false;

			var data = returnMedia(width, media);

			if (img.length === 0) {

				$(data.tag).appendTo(figure).fadeIn('fast', function(){
					figure.siblings('.off').fadeIn('fast');
				});

			} else {

				if (img.attr('src') === data.src) {
					return;
				}

				img.attr('src', data.src);

			}


		});

	};

	// Initial image load
	loadResponsive();

	// Waiting for resize to be done
	$(window).on('resize', function(){

		var w = $(window).width();

		if (w !== globalW) {
			clearTimeout(rTimeout);
			rTimeout = setTimeout(loadResponsive, 500);
		}

		w = globalW;

	});


})();