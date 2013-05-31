/*
 * Jappix Me - Your public profile, anywhere
 * Profile privacy page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// XMPP connected handler
function handleConnected() {
	// Change status
	$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Connected.');
	
	// Back button link
	$('#content .step .stepped a.button.back').attr('href', '/' + (con.username).htmlEnc() + '@' + (con.domain).htmlEnc());
	
	// Switch to next 2 steps!
	$('#content .step').eq(0).find('input').attr('disabled', true);
	$('#content .step').eq(0).addClass('disabled');
	$('#content .step').eq(1).removeClass('disabled');
	$('#content .step').eq(1).find('input').removeAttr('disabled');
	
	$('#content .step').eq(2).removeClass('disabled');
	$('#content .step').eq(2).find('button').removeAttr('disabled');
	
	if($('#content .step').eq(1).find('.saveit').is(':hidden'))
		$('#content .step').eq(1).find('.saveit').fadeIn('slow');
	
	window.location.hash = 'step2';
}

// XMPP error handler
function handleError() {
	$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Error.');
	$('#content .step').eq(0).find('input').removeAttr('disabled');
}

// XMPP disconnected handler
function handleDisconnected() {
	$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Disconnected.');
}

// XMPP node public access
function rightsAccessNode(node, value, handler) {
	var iq = new JSJaCIQ();
	iq.setType('set');
	
	// Main elements
	var pubsub = iq.appendNode('pubsub', {'xmlns': NS_PUBSUB_OWNER});
	var configure = pubsub.appendChild(iq.buildNode('configure', {'node': node, 'xmlns': NS_PUBSUB}));
	var x = configure.appendChild(iq.buildNode('x', {'xmlns': NS_XDATA, 'type': 'submit'}));
	
	// Form type
	var field1 = x.appendChild(iq.buildNode('field', {'var': 'FORM_TYPE', 'type': 'hidden', 'xmlns': NS_XDATA}));
	field1.appendChild(iq.buildNode('value', {'xmlns': NS_XDATA}, NS_PUBSUB_NC));
	
	// Access rights
	var field2 = x.appendChild(iq.buildNode('field', {'var': 'pubsub#access_model', 'xmlns': NS_XDATA}));
	field2.appendChild(iq.buildNode('value', {'xmlns': NS_XDATA}, value));
	
	if(handler)
		con.send(iq, handler);
	else
		con.send(iq);
}

// XMPP public microblog request
function makeMicroblogRights() {
	// Read from form
	var rights = null;
	var value = $('#content .step .stepped input[name=microblog]:checked').val();
	
	if(value == 'public')
		rights = 'open';
	else if(value == 'private')
		rights = 'presence';
	
	// Send rights!
	if(rights) {
		$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Social channel…').show();
		
		rightsAccessNode(NS_URN_MBLOG, rights, makeLocationRights);
	}
	
	else
		makeLocationRights();
}

// XMPP public location request
function makeLocationRights() {
	// Read from form
	var rights = null;
	var value = $('#content .step .stepped input[name=geoloc]:checked').val();
	
	if(value == 'public')
		rights = 'open';
	else if(value == 'private')
		rights = 'presence';
	
	// Send rights!
	if(rights) {
		$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Current location…').show();
		
		rightsAccessNode(NS_GEOLOC, rights, tellTheBot);
	}
	
	else
		tellTheBot();
}

// Server bot creation request
function tellTheBot() {
	var app_url = $('#config input[name="app-url"]').val();

	// Send the bot a request
	$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Sending to our bot…').show();
	
	// Read data
	var d_search = $('#content .step .stepped input[name=robots]:checked').val();
	var d_flagged = $('#content .step .stepped input[name=flagged]:checked').val();
	
	var d_update = '0';
	if($('#content .step .stepped input[name=update]').is(':checked'))
		d_update = '1';
	
	var d_remove = '0';
	if($('#content .step .stepped input[name=remove]').is(':checked'))
		d_remove = '1';
	
	$.post('/privacy/bot', {usr: con.username, srv: con.domain, pwd: con.pass, search: d_search, flagged: d_flagged, update: d_update, remove: d_remove}, function(data) {
		// Any error?
		if(data != 'OK') {
			$('#content .step:not(.disabled) .stepped .status').removeClass('network').text(data);
			
			return;
		}
		
		// Disconnect from XMPP
		con.disconnect();
		
		// Last step
		$('#content .step').eq(2).addClass('disabled');
		$('#content .step').eq(2).find('button').attr('disabled', true);
		$('#content .step').eq(3).removeClass('disabled');
		
		// Reveal the link to the profile
		$('#content .step .stepped .reveal a').attr('href', app_url + (con.username).htmlEnc() + '@' + (con.domain).htmlEnc());
		$('#content .step .stepped .reveal a').html(app_url + '<b>' + (con.username).htmlEnc() + '@' + (con.domain).htmlEnc() + '</b>');
	});
}

$(document).ready(function() {
	// Enable first form
	$('#content .step').eq(0).find('input').removeAttr('disabled');
	
	// Focus on first form input
	$('#content .step .stepped form input:first').focus();
	
	// Disabled click event
	$('*').click(function() {
		if($(this).parent().hasClass('disabled'))
			return false;
	});
	
	// Form event
	$('#content .step .stepped form').submit(function() {
		// Read data
		var address = $(this).find('input.[name=address]').val();
		var password = $(this).find('input.[name=password]').val();
		
		if(!address || !password)
			return false;
		
		var username, domain;
		
		// A domain is specified
		if(address.indexOf('@') != -1) {
			username = getXIDNick(address);
			domain = getXIDHost(address);
		}
		
		// Quick address input
		else {
			username = address;
			domain = 'jappix.com';
		}
		
		username = username.toLowerCase();
		domain = domain.toLowerCase();
		
		// Read config
		var config_bot_domain = $('#config input[name="bot-domain"]').val();
		var config_xmpp_bosh = $('#config input[name="xmpp-bosh"]').val();

		// Not allowed?
		if((domain != config_bot_domain) || (domain == 'gmail.com') || (domain == 'googlemail.com') || (domain == 'chat.facebook.com')) {
			$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Server not eligible. Must be ' + config_bot_domain).show();
			
			return false;
		}

		// Connect!
		oArgs = new Object();
		alert(config_xmpp_bosh);
		oArgs.httpbase = config_xmpp_bosh;
		oArgs.username = username;
		oArgs.domain = domain;
		oArgs.resource = 'Jappix Me (WB' + (new Date()).getTime() + ')';
		oArgs.pass = password;
		oArgs.secure = true;
		oArgs.xmllang = 'en';
		alert(oArgs.httpbase);
		// Domain not in serviced ones?
		con = new JSJaCHttpBindingConnection(oArgs);
		
		con.registerHandler('onconnect', handleConnected);
		con.registerHandler('onerror', handleError);
		con.registerHandler('ondisconnect', handleDisconnected);
		
		con.connect(oArgs);
		
		// Connecting status
		$(this).find('input').attr('disabled', true);
		$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Connecting…').show();

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
		makeMicroblogRights();
	});
	
	// Apply placeholder
	$('input[placeholder], textarea[placeholder]').placeholder();
});