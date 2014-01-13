/*
 * Jappix Me - Your public profile, anywhere
 * Profile privacy page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// Credentials storage
var USER_USERNAME = '';
var USER_DOMAIN = '';
var USER_PASSWORD = '';

// XMPP connected handler
function handleConnected() {
	// Change status
	if((typeof(con) != 'undefined') && con && con.connected()) {
		// Stop waiter
		$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Connected.');

		// Disconnect from XMPP (not needed then)
		con.disconnect();
	}
	
	// Back button link
	$('#content .step .stepped a.button.back').attr('href', '/' + USER_USERNAME.htmlEnc() + '@' + USER_DOMAIN.htmlEnc());
	
	// Switch to next 2 steps!
	$('#content .step').eq(0).find('input').attr('disabled', true);
	$('#content .step').eq(0).addClass('disabled');
	$('#content .step').eq(1).removeClass('disabled');
	$('#content .step').eq(1).find('input').removeAttr('disabled');
	
	$('#content .step').eq(2).removeClass('disabled');
	$('#content .step').eq(2).find('button').removeAttr('disabled');
	
	if($('#content .step').eq(1).find('.saveit').is(':hidden')) {
		$('#content .step').eq(1).find('.saveit').fadeIn('slow');
	}
	
	window.location.hash = 'step2';
}

// XMPP error handler
function handleError() {
	$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Wrong credentials.');
	$('#content .step').eq(0).find('input').removeAttr('disabled');
}

// Server bot creation request
function submitBot() {
	var app_url = $('#config input[name="app-url"]').val();

	// Send the bot a request
	$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Sending to our bot…').show();
	
	// Read data
	var d_search = $('#content .step .stepped input[name=robots]:checked').val();
	var d_flagged = $('#content .step .stepped input[name=flagged]:checked').val();
	var d_microblog = $('#content .step .stepped input[name=microblog]:checked').val();
	var d_geoloc = $('#content .step .stepped input[name=geoloc]:checked').val();
	var d_update = $('#content .step .stepped input[name=update]').is(':checked') ? '1' : '0';
	var d_remove = $('#content .step .stepped input[name=remove]').is(':checked') ? '1' : '0';

	$.post('/privacy/bot', {
		usr: USER_USERNAME,
		srv: USER_DOMAIN,
		pwd: USER_PASSWORD,
		search: d_search,
		flagged: d_flagged,
		microblog: d_microblog,
		geoloc: d_geoloc,
		update: d_update,
		remove: d_remove
	}, function(data) {
		// Any error?
		if(data != 'OK') {
			$('#content .step:not(.disabled) .stepped .status').removeClass('network').text(data);
			
			return;
		}
		
		// Job done!
		$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Done.');

		// Last step
		$('#content .step').eq(2).addClass('disabled');
		$('#content .step').eq(2).find('button').attr('disabled', true);
		$('#content .step').eq(3).removeClass('disabled');
		
		// Reveal the link to the profile
		$('#content .step .stepped .reveal a').attr('href', app_url + USER_USERNAME.htmlEnc() + '@' + USER_DOMAIN.htmlEnc());
		$('#content .step .stepped .reveal a').html(app_url + '<b>' + USER_USERNAME.htmlEnc() + '@' + USER_DOMAIN.htmlEnc() + '</b>');
	});
}

$(document).ready(function() {
	// Enable first form
	$('#content .step').eq(0).find('input').removeAttr('disabled');
	
	// Focus on first form input
	$('#content .step .stepped form input:first').focus();
	
	// Disabled click event
	$('*').click(function() {
		if($(this).parent().hasClass('disabled')) {
			return false;
		}
	});
	
	// Form event
	$('#content .step .stepped form').submit(function() {
		// Read data
		var address = $(this).find('input.[name=address]').val();
		var password = $(this).find('input.[name=password]').val();
		
		if(!address || !password) {
			return false;
		}
		
		var username, domain;
		
		if(address.indexOf('@') != -1) {
			// A domain is specified
			username = Common.getXIDNick(address);
			domain = Common.getXIDHost(address);
		} else {
			// Quick address input
			username = address;
			domain = 'jappix.com';
		}
		
		username = username.toLowerCase();
		domain = domain.toLowerCase();
		
		// Read config
		var config_bot_domain = $('#config input[name="bot-domain"]').val();
		var config_xmpp_bosh = $('#config input[name="xmpp-bosh"]').val();
		var config_xmpp_websocket = $('#config input[name="xmpp-websocket"]').val();

		// Not allowed?
		if((domain == 'gmail.com') || (domain == 'googlemail.com') || (domain == 'chat.facebook.com')) {
			$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Server not eligible.').show();
			
			return false;
		}

		// Store credentials
		USER_USERNAME = username;
		USER_DOMAIN = domain;
		USER_PASSWORD = password;

		// Lock credentials
		$(this).find('input').attr('disabled', true);

		// Can check credentials? (domain allowed by BOSH)
		if(domain == config_bot_domain) {
			// Connect!
			if(config_xmpp_websocket && typeof window.WebSocket !== undefined) {
				con = new JSJaCWebSocketConnection({
					httpbase: config_xmpp_websocket
				});
			} else {
				con = new JSJaCHttpBindingConnection({
					httpbase: config_xmpp_bosh
				});
			}

			con.registerHandler('onconnect', handleConnected);
			con.registerHandler('onerror', handleError);
			
			con.connect({
				username: username,
				domain: domain,
				resource: 'Jappix Me (WB' + (new Date()).getTime() + ')',
				pass: password,
				secure: true,
				xmllang: 'en'
			});

			// Waiter
			$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Connecting…').show();
		} else {
			handleConnected();
		}

		return false;
	});
	
	// Profile removal
	if($('#content .step .stepped input[name=remove]').is(':checked')) {
		$('#content .step .stepped .subtabulate:not(.removal)').addClass('disabled');
		$('#content .step .stepped .subtabulate:not(.removal) input').attr('disabled', true);
	}
	
	$('#content .step .stepped input[name=remove]').click(function() {
		if($(this).is(':checked')) {
			$('#content .step .stepped .subtabulate:not(.removal)').addClass('disabled');
			$('#content .step .stepped .subtabulate:not(.removal) input').attr('disabled', true);
		}
		
		else {
			$('#content .step .stepped .subtabulate:not(.removal)').removeClass('disabled');
			$('#content .step .stepped .subtabulate:not(.removal) input').removeAttr('disabled');
		}
	});
	
	$('#content .step .stepped button').click(function() {
		// Disable second step
		$('#content .step').eq(1).find('input').attr('disabled', true);
		$('#content .step').eq(1).addClass('disabled');
		
		// Set microblog & geoloc to private if profile removal?
		if($('#content .step .stepped input[name=remove]').is(':checked')) {
			$('#content .step .stepped input[name=microblog]').val('private');
			$('#content .step .stepped input[name=geoloc]').val('private');
		}
		
		// Save!
		$(this).attr('disabled', true);

		submitBot();
	});
	
	// Apply placeholder
	$('input[placeholder], textarea[placeholder]').placeholder();
});