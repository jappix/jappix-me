/*
 * Jappix Me - Your public profile, anywhere
 * New profile page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// XMPP connected handler
function handleConnected() {
	$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Connected.');
	
	// Switch to next step!
	$('#content .step .stepped form input').attr('disabled', true);
	$('#content .step').eq(1).addClass('disabled');
	$('#content .step').eq(2).removeClass('disabled');
	$('#content .step').eq(2).find('button').removeAttr('disabled');
	
	window.location.hash = 'step3';
}

// XMPP error handler
function handleError() {
	$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Error.');
	$('#content .step .stepped form input').removeAttr('disabled');
}

// XMPP disconnected handler
function handleDisconnected() {
	$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Disconnected.');
}

// XMPP node public access
function publicAccessNode(node, handler) {
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
	field2.appendChild(iq.buildNode('value', {'xmlns': NS_XDATA}, 'open'));
	
	if(handler)
		con.send(iq, handler);
	else
		con.send(iq);
}

// XMPP public microblog request
function makeMicroblogPublic() {
	$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Social channel…').show();
	
	publicAccessNode(NS_URN_MBLOG, makeLocationPublic);
}

// XMPP public location request
function makeLocationPublic() {
	$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Current location…').show();
	
	publicAccessNode(NS_GEOLOC, tellTheBot);
}

// Server bot creation request
function tellTheBot() {
	// Send the bot a request
	$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Adding to queue…').show();
	
	$.post('/new/bot', {usr: con.username, srv: con.domain, pwd: con.pass}, function(data) {
		// Any error?
		if(!data.match(/OK/)) {
			$('#content .step:not(.disabled) .stepped .status').removeClass('network').text(data);
			
			return;
		}
		
		// Job done!
		$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Done.');
		
		// Next step
		$('#content .step').eq(2).addClass('disabled');
		$('#content .step').eq(2).find('button').attr('disabled', true);
		$('#content .step').eq(3).removeClass('disabled');
		$('#content .step').eq(3).find('button').removeAttr('disabled')
		
		// Redirect
		window.location.hash = 'step4';
	});
}

// Checks if a value exists in an array
function existArrayValue(array, value) {
	try {
		// Loop in the array
		for(i in array) {
			if(array[i] == value)
				return true;
		}
		
		return false;
	}
	
	catch(e) {
		return false;
	}
}

// Converts a JS array to a serialized PHP one
function jsArrayToPHP(a) {
	var a_php = '';
	var total = 0;
	
	for(var key in a)  {
		++ total;
		
		a_php = a_php + 's:' + String(key).length + ':"' + String(key) + '";s:' + String(a[key]).length + ':"' + String(a[key]) + '";';
    }
	
	a_php = 'a:' + total + ':{' + a_php + '}';
	
	return a_php;
}

// Gets the user friend list
function listInviteFriends() {
	$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Getting friend list…').show();
	
	var iq = new JSJaCIQ();
	
	iq.setType('get');
	iq.setQuery(NS_ROSTER);
	
	con.send(iq, handleInviteFriends);
}

// Handles the user friend list
function handleInviteFriends(iq) {
	var users = [];
	
	$(iq.getQuery()).find('item').each(function() {
		var current_user = $(this).attr('jid');
		
		if(current_user.match(/@/) && !current_user.match(/%/) && !current_user.match(/\\40/) && !existArrayValue(users, current_user))
			users.push(current_user);
	});
	
	inviteFriends('invite', users);
}

// Sends the invite messages
function sendInviteFriends(users) {
	var app_url = $('#config input[name="app-url"]').val();

	if(users && users.length) {
		for(i in users) {
			var mess = new JSJaCMessage();
			
			mess.setTo(users[i]);
			mess.setSubject('Join me on Jappix Me!');
			mess.setType('normal');
			mess.setBody('Hey, I just created my Jappix Me profile! Jappix Me is a free tool to create your own public profile, using your social channel and lots of information from your XMPP account.\n\nIf you want to see my profile, visit: ' + app_url + con.username + '@' + con.domain + ' which will be soon available!\n\nJoin us on ' + app_url + ' and create your own profile for free! ;-)\n\n\n*This is an automated message, sent to you because one of your friends invited his buddy list to join him on Jappix Me. You will not receive it twice.*');
			
			con.send(mess);
		}
	}
	
	// Job done!
	var invite_result = 'No friend invited.';
	
	if(users.length == 1)
		invite_result = '1 friend invited.';
	else if(users.length > 1)
		invite_result = users.length + ' friends invited.';
	
	$('#content .step:not(.disabled) .stepped .status').removeClass('network').text(invite_result);
	
	// Last step
	$('#content .step').eq(3).addClass('disabled');
	$('#content .step').eq(3).find('button').attr('disabled', true);
	$('#content .step').eq(4).removeClass('disabled');
	
	// Reveal the link to the profile
	$('#content .step .stepped .reveal a').attr('href', app_url + (con.username).htmlEnc() + '@' + (con.domain).htmlEnc());
	$('#content .step .stepped .reveal a').html(app_url + '<b>' + (con.username).htmlEnc() + '@' + (con.domain).htmlEnc() + '</b>');
	$('#content .step .stepped .reveal').fadeIn('slow');
	
	window.location.hash = 'step5';
	
	// Disconnect from XMPP
	con.disconnect();
}

// Friend invite request
function inviteFriends(mode, users) {
	// Skip?
	if(mode == 'skip') {
		var app_url = $('#config input[name="app-url"]').val();

		// Last step
		$('#content .step').eq(3).addClass('disabled');
		$('#content .step').eq(3).find('button').attr('disabled', true);
		$('#content .step').eq(4).removeClass('disabled');
		
		// Reveal the link to the profile
		$('#content .step .stepped .reveal a').attr('href', app_url + (con.username).htmlEnc() + '@' + (con.domain).htmlEnc());
		$('#content .step .stepped .reveal a').html(app_url + '<b>' + (con.username).htmlEnc() + '@' + (con.domain).htmlEnc() + '</b>');
		$('#content .step .stepped .reveal').fadeIn('slow');
		
		window.location.hash = 'step5';
		
		return false;
	}
	
	// Send the bot a request
	$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Sending invite messages…').show();
	
	$.post('/new/invite', {list: jsArrayToPHP(users)}, function(data) {
		var to_invite = [];
		
		$(data).find('user').each(function() {
			var current_push = $(this).text();
			
			if(current_push && !existArrayValue(to_invite, current_push))
				to_invite.push(current_push);
		});
		
		// Send messages
		sendInviteFriends(to_invite);
	});
}

$(document).ready(function() {
	// Enable first input
	$('#content .step').eq(0).find('input').removeAttr('disabled');
	
	// Disabled click event
	$('*').click(function() {
		if($(this).parent().hasClass('disabled'))
			return false;
	});
	
	// Form event
	$('#content .step .stepped input.read').click(function() {
		$(this).attr('disabled', true);
		$('#content .step').eq(0).addClass('disabled');
		$('#content .step').eq(1).removeClass('disabled');
		$('#content .step').eq(1).find('input').removeAttr('disabled');
		$('#content .step').eq(1).find('input:first').focus();
		
		window.location.hash = 'step2';
	});
	
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
		
		// Not allowed?
		if((domain == 'gmail.com') || (domain == 'googlemail.com') || (domain == 'chat.facebook.com')) {
			$('#content .step:not(.disabled) .stepped .status').removeClass('network').text('Server not eligible.').show();
			return false;
		}
		
		// Connect!
		oArgs = new Object();
		oArgs.httpbase = $('#config input[name="xmpp-bosh"]').val();
		
		con = new JSJaCHttpBindingConnection(oArgs);
		
		con.registerHandler('onconnect', handleConnected);
		con.registerHandler('onerror', handleError);
		con.registerHandler('ondisconnect', handleDisconnected);
		
		oArgs = new Object();
		oArgs.username = username;
		oArgs.domain = domain;
		oArgs.resource = 'Jappix Me (WB' + (new Date()).getTime() + ')';
		oArgs.pass = password;
		oArgs.secure = true;
		oArgs.xmllang = 'en';
		
		con.connect(oArgs);
		
		// Connecting status
		$(this).find('input').attr('disabled', true);
		$('#content .step:not(.disabled) .stepped .status').addClass('network').text('Connecting…').show();
		
		return false;
	});
	
	$('#content .step .stepped button.create').click(function() {
		$(this).attr('disabled', true);
		makeMicroblogPublic();
	});
	
	$('#content .step .stepped button.invite').click(function() {
		$(this).attr('disabled', true);
		listInviteFriends();
	});
	
	$('#content .step .stepped .skip a').click(function() {
		if($(this).parent().parent().parent().hasClass('disabled'))
			return false;
		
		return inviteFriends('skip');
	});
	
	// Apply placeholder
	$('input[placeholder], textarea[placeholder]').placeholder();
});