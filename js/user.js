/*
 * Jappix Me - Your public profile, anywhere
 * User page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// XMPP connected handler
function handleConnected() {
	var param_server = $('#content .wrapper .comments').attr('data-server');
	var param_node = $('#content .wrapper .comments').attr('data-node');
	
	if(param_server && param_node)
		getComments(param_server, param_node);
}

// XMPP error handler
function handleError() {
	removeDB('jappix-me', 'stamp');
	errorComments('Error. Retry?');
}

// XMPP disconnected handler
function handleDisconnected() {
	removeDB('jappix-me', 'stamp');
	errorComments('Disconnected. Reconnect?');
}

// XMPP session save
function saveSession() {
	if(!isConnected())
		return;
	
	setDB('jappix-me', 'stamp', getTimeStamp());
	con.suspend(false);
}

// Comments in error
function errorComments(message) {
	$('#content .wrapper .comments .comments-content').html('<a class="comments-load" href="#">' + message.htmlEnc() + '</a>');
	$('#content .wrapper .comments .comments-load').click(initComments);
	
	$('#content .wrapper .comments .comments-form input.submit').attr('disabled', true);
}

// Logins to a anonymous account
function initComments() {
	$('#content .wrapper .comments .comments-load').replaceWith('<span class="comments-loading">Loading comments...</span>');
	
	// Can resume?
	var stamp = parseInt(getDB('jappix-me', 'stamp'));
	
	oArgs = new Object();
	oArgs.httpbase = $('#config input[name="xmpp-bosh"]').val();
	
	con = new JSJaCHttpBindingConnection(oArgs);
	
	con.registerHandler('onconnect', handleConnected);
	con.registerHandler('onresume', handleConnected);
	con.registerHandler('onerror', handleError);
	con.registerHandler('ondisconnect', handleDisconnected);
	
	// Must connect!
	if(((getTimeStamp() - stamp) >= JSJACHBC_MAX_WAIT) || !con.resume()) {
		oArgs = new Object();
		oArgs.domain = $('#config input[name="xmpp-domain"]').val();
		oArgs.authtype = 'saslanon';
		oArgs.resource = 'Jappix Me (WB' + (new Date()).getTime() + ')';
		oArgs.secure = true;
		oArgs.xmllang = 'en';
		
		con.connect(oArgs);
	}
	
	return false;
}

// Gets a given microblog comments node
function getComments(server, node) {
	var iq = new JSJaCIQ();
	iq.setType('get');
	iq.setTo(server);
	
	var pubsub = iq.appendNode('pubsub', {'xmlns': NS_PUBSUB});
	pubsub.appendChild(iq.buildNode('items', {'node': node, 'xmlns': NS_PUBSUB}));
	
	con.send(iq, handleComments);
	
	return false;
}

// Handles a microblog comments node items
function handleComments(iq) {
	// Error?
	if(iq.getType() == 'error') {
		$('#content .wrapper .comments .comments-content').html('<span class="comments-nothing">Error. Broken comments!</span>');
		$('#content .wrapper .comments .comments-form input.submit').attr('disabled', true);
		
		return;
	}
	
	var app_url = $('#config input[name="app-url"]').val();
	var anon_domain = $('#config input[name="xmpp-domain"]').val();
	var path = '#content .wrapper .comments .comments-content';
	var data = iq.getNode();
	var server = bareXID(getStanzaFrom(iq));
	var code = '';
	
	// Append the comments
	$(data).find('item').each(function() {
		// Get comment
		var current_xid = explodeThis(':', $(this).find('author uri').text(), 1);
		var current_name = $(this).find('author name').text();
		var current_date = $(this).find('published').text();
		var current_body = $(this).find('content[type=text]').text();
		var current_bname = current_xid;
		
		if(current_date)
			current_date = explodeThis(' - ', relativeDate(current_date), 0);
		else
			current_date = '';
		
		if(!current_body)
			current_body = $(this).find('title:not(source > title)').text();
		
		if(!current_xid)
			current_xid = '';
		
		if(!current_name && current_xid && current_xid.match('@') && (getXIDHost(current_xid) != anon_domain))
			current_name = getXIDNick(current_xid);
		
		if(!current_name)
			current_name = '<em>Anonymous</em>';
		else
			current_name.htmlEnc();
		
		var current_profile = app_url + 'unknown@' + anon_domain;
		var current_real = false;
		
		if(current_xid && current_xid.match('@') && (getXIDHost(current_xid) != anon_domain)) {
			current_profile = app_url + current_xid.htmlEnc();
			current_real = true;
		}
		
		var current_avatarlink = '';
		var current_namelink = '';
		
		if(current_real) {
			current_avatarlink = '<a href="' + current_profile + '" target="_blank"><img class="avatar" src="' + current_profile + '/avatar/32.png" alt="" /></a>';
			current_namelink = '<a class="name" href="' + current_profile + '" target="_blank">' + current_name + '</a>';
		}
		
		else {
			current_avatarlink = '<img class="avatar" src="' + current_profile + '/avatar/32.png" alt="" />';
			current_namelink = '<span class="name">' + current_name + '</span>';
		}
		
		if(current_body)
			code = '<div class="tabulate" data-author="' + encodeQuotes(current_xid) + '">' + 
						current_avatarlink + 
						
						'<div class="comment-container">' + 
							current_namelink + 
							'<span class="date">, ' + current_date.htmlEnc() + '</span>' + 
							'<p class="body">' + formatText(current_body) + '</p>' + 
						'</div>' + 
						
						'<div class="clear"></div>' + 
					'</div>' + code;
	});
	
	if(code)
		$(path).html(code);
	else
		$('#content .wrapper .comments .comments-loading').replaceWith('<span class="comments-nothing">No comments... Yet!</span>');
	
	$('#content .wrapper .comments .comments-form input.submit').removeAttr('disabled');
}

// Sends a comment on a given microblog comments node
function sendComment() {
	try {
		if($('#content .wrapper .comments .comments-form input.submit').is(':disabled'))
			return false;
		
		// Read data
		var app_url = $('#config input[name="app-url"]').val();
		var anon_domain = $('#config input[name="xmpp-domain"]').val();
		var name = $('#content .wrapper .comments .comments-form input.name').val();
		var value = $('#content .wrapper .comments .comments-form textarea.body').val();
		var server = $('#content .wrapper .comments').attr('data-server');
		var node = $('#content .wrapper .comments').attr('data-node');
		
		// Not enough data?
		if(!name || !value || !server || !node)
			return false;
		
		$('#content .wrapper .comments .comments-form *').attr('disabled', true);
		
		var date = getXMPPTime('utc');
		var hash = hex_md5(value + date);
		
		var iq = new JSJaCIQ();
		iq.setType('set');
		iq.setTo(server);
		
		// PubSub main elements
		var pubsub = iq.appendNode('pubsub', {'xmlns': NS_PUBSUB});
		var publish = pubsub.appendChild(iq.buildNode('publish', {'node': node, 'xmlns': NS_PUBSUB}));
		var item = publish.appendChild(iq.buildNode('item', {'id': hash, 'xmlns': NS_PUBSUB}));
		var entry = item.appendChild(iq.buildNode('entry', {'xmlns': NS_ATOM}));
		
		// Author infos
		var author = entry.appendChild(iq.buildNode('author', {'xmlns': NS_ATOM}));
		author.appendChild(iq.buildNode('name', {'xmlns': NS_ATOM}, name));
		author.appendChild(iq.buildNode('uri', {'xmlns': NS_ATOM}, 'xmpp:' + 'unknown@' + anon_domain));
		
		// Create the comment
		entry.appendChild(iq.buildNode('content', {'type': 'text', 'xmlns': NS_ATOM}, value));
		entry.appendChild(iq.buildNode('published', {'xmlns': NS_ATOM}, date));
		
		con.send(iq, handleSendComment);
		
		// Display the comment
		var current_date = explodeThis(' - ', relativeDate(date), 0);
		
		var code = '<div id="' + hash + '" class="tabulate" style="display: none;" data-author="' + encodeQuotes('unknown@' + anon_domain) + '">' + 
						'<img class="avatar" src="' + app_url + 'unknown@' + anon_domain + '/avatar/32.png" alt="" />' + 
						
						'<div class="comment-container">' + 
							'<span class="name">' + name.htmlEnc() + '</span>' + 
							'<span class="date">, ' + current_date.htmlEnc() + '</span>' + 
							'<p class="body">' + formatText(value) + '</p>' + 
							'<a class="cancel" href="#">Cancel my comment</a>' + 
						'</div>' + 
						
						'<div class="clear"></div>' + 
					'</div>';
		
		if(!$('#content .wrapper .comments .comments-content .tabulate').size())
			$('#content .wrapper .comments .comments-content').html(code);
		else
			$('#content .wrapper .comments .comments-content .tabulate:last').after(code);
		
		$('#content .wrapper .comments .comments-content .tabulate#' + hash + ' a.cancel').click(function() {
			cancelComment(server, node, hash);
			
			$(this).parent().parent().fadeOut(function() {
				$(this).remove();
				
				if(!$('#content .wrapper .comments .comments-content .tabulate').size())
					$('#content .wrapper .comments .comments-content').html('<span class="comments-nothing">No comments... Yet!</span>');
			});
			
			return false;
		});
		
		// Notify the followers
		var followers = [];
		
		$('#content .wrapper .comments .comments-content .tabulate').each(function() {
			var current_author = $(this).attr('data-author');
			
			if(current_author && current_author.match('@') && (getXIDHost(current_author) != anon_domain) && !existArrayValue(followers, current_author))
				followers.push(current_author);
		});
		
		var owner_xid = $('#content .wrapper .comments').attr('data-owner');
		if(owner_xid && !existArrayValue(followers, owner_xid))
			followers.push(owner_xid);
		
		var repeated_xid = $('#content .wrapper.channel .tabulate .right .meta .repeat').attr('data-xid');
		if(repeated_xid && !existArrayValue(followers, repeated_xid))
			followers.push(repeated_xid);
		
		// Notify users
		if(followers && followers.length) {
			var item_href = 'xmpp:' + server + '?;node=' + encodeURIComponent(node) + ';item=' + encodeURIComponent(hash);
			
			var parent_select = $('#content .wrapper .comments');
			var parent_data = [parent_select.attr('data-owner'), NS_URN_MBLOG, parent_select.attr('data-post')];
			
			for(n in followers)
				sendNotification(followers[n], 'unknown@' + anon_domain, name, 'comment', item_href, value, parent_data);
		}
	}
	
	catch(e) {}
	
	finally {
		return false;
	}
}

// Handles the comment publishing
function handleSendComment(iq) {
	if(iq.getType() == 'error')
		return;
	
	$('#content .wrapper .comments .comments-form input.name').val('');
	$('#content .wrapper .comments .comments-form textarea.body').val('');
	$('#content .wrapper .comments .comments-form *').removeAttr('disabled');
	
	var fade_it = $('#content .wrapper .comments .comments-content .tabulate:hidden:last');
	var id = fade_it.attr('id');
	
	fade_it.fadeIn();
	window.location.hash = id;
}

// Removes a given microblog comment item
function cancelComment(server, node, id) {
	var iq = new JSJaCIQ();
	iq.setType('set');
	iq.setTo(server);
	
	var pubsub = iq.appendNode('pubsub', {'xmlns': NS_PUBSUB});
	var retract = pubsub.appendChild(iq.buildNode('retract', {'node': node, 'xmlns': NS_PUBSUB}));
	retract.appendChild(iq.buildNode('item', {'id': id, 'xmlns': NS_PUBSUB}));
	
	con.send(iq);
}

// Sends a social notification
function sendNotification(xid, my_xid, my_name, type, href, text, parent) {
	// Notification ID
	var id = hex_md5(xid + text + getTimeStamp());
	
	// IQ
	var iq = new JSJaCIQ();
	iq.setType('set');
	iq.setTo(xid);
	
	// ATOM content
	var pubsub = iq.appendNode('pubsub', {'xmlns': NS_PUBSUB});
	var publish = pubsub.appendChild(iq.buildNode('publish', {'node': NS_URN_INBOX, 'xmlns': NS_PUBSUB}));
	var item = publish.appendChild(iq.buildNode('item', {'id': id, 'xmlns': NS_PUBSUB}));
	var entry = item.appendChild(iq.buildNode('entry', {'xmlns': NS_ATOM}));
	
	// Notification author (us)
	var author = entry.appendChild(iq.buildNode('author', {'xmlns': NS_ATOM}));
	author.appendChild(iq.buildNode('name', {'xmlns': NS_ATOM}, my_name));
	author.appendChild(iq.buildNode('uri', {'xmlns': NS_ATOM}, 'xmpp:' + my_xid));
	
	// Notification content
	entry.appendChild(iq.buildNode('published', {'xmlns': NS_ATOM}, getXMPPTime('utc')));
	entry.appendChild(iq.buildNode('content', {'type': 'text', 'xmlns': NS_ATOM}, text));
	entry.appendChild(iq.buildNode('link', {'rel': 'via', 'title': type, 'href': href, 'xmlns': NS_ATOM}));
	
	// Any parent item?
	if(parent && parent[0] && parent[1] && parent[2]) {
		// Generate the parent XMPP URI
		var parent_href = 'xmpp:' + parent[0] + '?;node=' + encodeURIComponent(parent[1]) + ';item=' + encodeURIComponent(parent[2]);
		
		entry.appendChild(iq.buildNode('link', {'rel': 'related', 'href': parent_href, 'xmlns': NS_ATOM}));
	}
	
	con.send(iq);
}

// Text format
function formatText(text) {
	// HTML-encode
	text = text.htmlEnc();
	
	// Text style
	text = text.replace(/(^|\s|>|\()((\*)([^<>'"\*]+)(\*))($|\s|<|\))/gi, '$1<b>$4</b>$6')
	text = text.replace(/(^|\s|>|\()((\/)([^<>'"\/]+)(\/))($|\s|<|\))/gi, '$1<em>$4</em>$6')
	text = text.replace(/(^|\s|>|\()((_)([^<>'"_]+)(_))($|\s|<|\))/gi, '$1<span style="text-decoration: underline;">$4</span>$6');
	
	// Apply links
	text = text.replace(/(\s|<br \/>|^)(([a-zA-Z0-9\._-]+)@([a-zA-Z0-9\.\/_-]+))(,|\s|$)/gi, '$1<a href="xmpp:$2" target="_blank">$2</a>$5');
	text = text.replace(/(\s|<br \/>|^|\()((https?|ftp|file|xmpp|irc|mailto|vnc|webcal|ssh|ldap|smb|magnet|spotify)(:)([^<>'"\s\)]+))/gim, '$1<a href="$2" target="_blank">$2</a>');
	
	return text;
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

// Item loader
function itemLoader() {
	if($('#content .wrapper .loading').size() && $('#content .wrapper .loading').hasClass('hidden')) {
		// Change loader
		var loader = $('#content .wrapper .loading');
		var user_val = loader.attr('data-user');
		var start_val = parseInt(loader.attr('data-start'));
		loader.attr('data-start', start_val + 1);
		loader.removeClass('hidden');
		
		// Change load link
		var loadmore = $('#content .wrapper a.loadmore');
		loadmore.hide();
		
		// Get the data
		if($('#content .wrapper.channel').size())
			$.get('/' + user_val + '/channel/' + start_val, function(data) {
				// No data?
				if(!data) {
					loadmore.remove();
					loader.after('<a class="gotop" href="#top">Return to top</a>');
					loader.remove();
					
					return;
				}
				
				// Append content
				$('#content .wrapper.channel .tabulate:last').after(data);
				loader.addClass('hidden');
				loadmore.show();
			});
		
		else if($('#content .wrapper.pictures').size())
			$.get('/' + user_val + '/pictures/' + start_val, function(data) {
				// No data?
				if(!data) {
					loadmore.remove();
					loader.after('<a class="gotop" href="#top">Return to top</a>');
					loader.remove();
					
					return;
				}
				
				// Convert data
				data = '<div>' + data + '</div>';
				
				// Parse data
				var first_rcvd = $(data).find('.tabulate').eq(0).find('h1').text();
				var last_dom = $('#content .wrapper.pictures .tabulate:last').text();
				
				// Must split data?
				if(first_rcvd == last_dom) {
					// Append first included item
					var first_html = $(data).find('.tabulate').eq(0);
					$(first_html).find('h1, .clear').remove();
					first_html = $(first_html).html();
					$('#content .wrapper.pictures .tabulate:last .clear').before(first_html);
					
					// Append other items
					var second_html = $(data);
					$(second_html).find('.tabulate:first').remove();
					second_html = $(second_html).html();
					$('#content .wrapper.pictures .tabulate:last').after(second_html);
				}
				
				else
					$('#content .wrapper.pictures .tabulate:last').after(data);
				
				loader.addClass('hidden');
				loadmore.show();
			});
	}
	
	return false;
}

// Social channel navigation
$(window).scroll(function() {
	if($('#content .wrapper .loading').size() && $(window).scrollTop() >= ($(document).height() - $(window).height() - 400))
		itemLoader();
});

// XMPP session saver
$(window).bind('beforeunload', saveSession);

// Picture gallery navigation
$(document).keyup(function(e) {
	if(!$('#content .wrapper a.navigation').size())
		return;
	
	var nav_href = '';
	var nav_path = '';
	
	if($('#content .wrapper.channel').size())
		nav_path = 'channel';
	else if($('#content .wrapper.pictures').size())
		nav_path = 'pictures';
	
	if(!nav_path)
		return;
	
	// Previous item?
	if(e.keyCode == 37)
		nav_href = $('#content .wrapper.' + nav_path + ' a.navigation.previous').click().focus().attr('href');
	
	// Next item?
	if(e.keyCode == 39)
		nav_href = $('#content .wrapper.' + nav_path + ' a.navigation.next').click().focus().attr('href');
	
	// Change the page?
	if(nav_href)
		window.location = nav_href;
	
	if((e.keyCode == 37) || (e.keyCode == 39))
		return false;
});

$(document).ready(function() {
	// Session save
	$('#content .wrapper a.navigation').click(saveSession);
	
	// Load more items
	$('#content .wrapper a.loadmore').click(itemLoader);
	
	// Comments load
	$('#content .wrapper .comments .comments-load').click(initComments);
	$('#content .wrapper .comments .comments-form input, #content .wrapper .comments .comments-form textarea').focus(function() {
		if($('#content .wrapper .comments .comments-load').size())
			initComments();
	});
	
	if($('#content .wrapper .comments .comments-load').size())
		initComments();
	
	// Comments send
	$('#content .wrapper .comments .comments-form').submit(sendComment);
	
	// Apply placeholder
	$('input[placeholder], textarea[placeholder]').placeholder();
});