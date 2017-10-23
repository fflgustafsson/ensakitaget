

// Use this file as a template
// sbSocial.isGaInUse and sbSocial.isTagManagerInUse is written by localization script in class Analytics


// FACEBOOK 
var dataLayer = dataLayer || [];

function registerFacebookListeners() {

	FB.Event.subscribe('edge.create', function(targetUrl) {
		// GA version
		if( parseInt(sbSocial.isGaInUse, 10) === 1 )
			_gaq.push(['_trackSocial', 'facebook', 'like', targetUrl]);

		// Tag Manger Version
		if( parseInt(sbSocial.isTagManagerInUse, 10) === 1 )
			dataLayer.push( {
				'event' : 'socialInt',
				'socialNetwork' : 'facebook',
				'socialAction' : 'like',
				'socialTarget' : targetUrl
			});

		console.log( "Like Event sent" );
	});

	FB.Event.subscribe('edge.remove', function(targetUrl) {
		// GA version
		if( parseInt(sbSocial.isGaInUse, 10) === 1 )
			_gaq.push(['_trackSocial', 'facebook', 'unlike', targetUrl]);

		// Tag Manger Version
		if( parseInt(sbSocial.isTagManagerInUse, 10) === 1 )
			dataLayer.push( {
				'event' : 'socialInt',
				'socialNetwork' : 'facebook',
				'socialAction' : 'unlike',
				'socialTarget' : targetUrl
			});

		console.log( "Unlike Event sent" );
	});

	FB.Event.subscribe('message.send', function(targetUrl) {
		// Ga Version
		if( parseInt(sbSocial.isGaInUse, 10) === 1 )
			_gaq.push(['_trackSocial', 'facebook', 'send', targetUrl]);

		// Tag Manger Version
		if( parseInt(sbSocial.isTagManagerInUse, 10) === 1 )
			dataLayer.push( {
				'event' : 'socialInt',
				'socialNetwork' : 'facebook',
				'socialAction' : 'send',
				'socialTarget' : targetUrl
			});

		console.log( "Share Event sent" );
	});
}

//TWITTER
function trackTwitter(intent_event) {
	if (intent_event) {
		var opt_pagePath;
		if (intent_event.target && intent_event.target.nodeName == 'IFRAME') {
			opt_target = extractParamFromUri(intent_event.target.src, 'url');
		}

		//GA version
		if( parseInt(sbSocial.isGaInUse, 10) === 1 )
			_gaq.push(['_trackSocial', 'twitter', 'tweet', opt_pagePath]);

		// Tag Manger Version
		if( parseInt(sbSocial.isTagManagerInUse, 10) === 1 )
			dataLayer.push( {
				'event' : 'socialInt',
				'socialNetwork' : 'twitter',
				'socialAction' : 'tweet',
				'socialTarget' : targetUrl
			});
	}
}

//Wrap event bindings - Wait for async js to load
twttr.ready(function (twttr) {
	//event bindings
	twttr.events.bind('tweet', trackTwitter);
});

