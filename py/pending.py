#!/usr/bin/env python

'''

Jappix Me - Your public profile, anywhere
Pending profile checker

License: AGPL
Author: Valerian Saliou

'''

import xmpp, os, shutil, time, phpserialize, config

BASE_DIR = config.path()


###############
### MESSAGE ###
###############

def message_app_send(session, user, body, app_data):
	url = xmpp.Node('url', payload=[app_data['url']])
	action = xmpp.Node('action', attrs={'type': app_data['type'], 'job': app_data['job'], 'success': app_data['success']})
	
	data = xmpp.Node('data', attrs={'xmlns': 'jappix:app:' + app_data['id']}, payload=[action, url])
	name = xmpp.Node('name', attrs={'id': app_data['id']}, payload=[app_data['name']])

	app = xmpp.Node('app', attrs={'xmlns': 'jappix:app'}, payload=[name, data])
	x = xmpp.Node('x', attrs={'xmlns': 'jabber:x:oob'}, payload=[url])
	body = xmpp.Node('body', payload=[body])

	iq = xmpp.Protocol('message', user, 'headline', payload=[body, x, app])

	return session.send(iq)


##############
### PUBSUB ###
##############

def pubsub_configure(session, user, node, model, handler):
	value_access = xmpp.Node('value', payload=[model])
	field_access = xmpp.Node('field', attrs={'var': 'pubsub#access_model'}, payload=[value_access])

	value_type = xmpp.Node('value', payload=[xmpp.NS_PUBSUB + '#node_config'])
	field_type = xmpp.Node('field', attrs={'var': 'FORM_TYPE', 'type': 'hidden'}, payload=[value_type])

	x = xmpp.Node('x', attrs={'xmlns': xmpp.NS_DATA, 'type': 'submit'}, payload=[field_type, field_access])
	configure = xmpp.Node('configure', attrs={'xmlns': xmpp.NS_PUBSUB, 'node': node}, payload=[x])
	pubsub = xmpp.Node('pubsub', attrs={'xmlns': xmpp.NS_PUBSUB + '#owner'}, payload=[configure])
	iq = xmpp.Protocol('iq', '', 'set', payload=[pubsub])

	return session.SendAndCallForResponse(iq, handler)


#################
### CONFIGURE ###
#################

def microblog_access(session, user, model):
	print "[pending:configure] Configuring microblog for " + user + " as '" + model + "'..."

	pubsub_configure(session, user, 'urn:xmpp:microblog:0', model, microblog_access_handle);

def microblog_access_handle(session, stanza):
	user_from = str(stanza.getFrom() or stanza.getTo())

	if stanza.getType() != 'error':
		print "[pending:configure] Configured microblog for " + user_from + "."
	else:
		print "[pending:configure] Could not configure microblog for '" + user_from + "'."

def geoloc_access(session, user, model):
	print "[pending:configure] Configuring geoloc for " + user + " as '" + model + "'..."

	pubsub_configure(session, user, xmpp.NS_GEOLOC, model, geoloc_access_handle);

def geoloc_access_handle(session, stanza):
	user_from = str(stanza.getFrom() or stanza.getTo())
	
	if stanza.getType() != 'error':
		print "[pending:configure] Configured geoloc for " + user_from + "."
	else:
		print "[pending:configure] Could not configure geoloc for '" + user_from + "'."


###############
### PENDING ###
###############

# Apply the settings to the pending accounts
def need_pending():
	app_id = 'me'
	app_name = 'Jappix Me'

	need = []
	notifications = []
	sub_dirs = os.listdir(BASE_DIR + '/pending')
	
	for user in sub_dirs:
		# Not a XID?
		if user.find('@') == -1:
			continue
		
		print "[pending:main] Processing pending user " + user + "..."

		current_pending = BASE_DIR + '/pending/' + user
		current_cache = BASE_DIR + '/cache/' + user
		current_file = open(current_pending, 'r')
		current_content = current_file.read()
		current_file.close()
		current_data = phpserialize.loads(current_content)
		
		os.remove(current_pending)
		
		# Check account credentials
		login_result = login(current_data['usr'], current_data['srv'], current_data['pwd'])

		print "[pending:main] Connecting to " + user + "..."

		if login_result['success']:
			if current_data['type'] == 'new':
				print "[pending:new] Creating new user " + user + "..."

				# Make microblog & geoloc public
				microblog_access(login_result['session'], user, 'open')
				geoloc_access(login_result['session'], user, 'open')

				# Create storage tree
				if not os.path.exists(current_cache):
					os.mkdir(current_cache)
					os.mkdir(current_cache + '/raw')
					os.mkdir(current_cache + '/system')
					
					os.chmod(current_cache, 0750)
					os.chmod(current_cache + '/raw', 0750)
					os.chmod(current_cache + '/system', 0750)
				
				# Update profile data
				if os.path.exists(current_cache + '/system'):
					last_file = open(current_cache + '/system/last', 'w')
		   			last_file.write('0')
					last_file.close()
					
					flagged_file = open(current_cache + '/system/flagged', 'w')
		   			flagged_file.write(phpserialize.dumps('0'))
					flagged_file.close()
					
					search_file = open(current_cache + '/system/search', 'w')
		   			search_file.write(phpserialize.dumps('1'))
					search_file.close()

				# Notify the user
				notifications.append({
					'user': user,
					'body': 'Your Jappix Me profile is being created, it will be available in a few moments. Check it out on ' + config.get('app', 'url') + '/' + user,
					
					'data': {
						'id': app_id,
						'name': app_name,

						'url': config.get('app', 'url') + '/' + user,
						'type': 'profile',
						'job': 'new',
						'success': '1'
					}
				})
			
			elif current_data['type'] == 'privacy':
				if current_data['remove'] == '1':
					print "[pending:remove] Removing user " + user + "..."

					# Make microblog & geoloc private
					microblog_access(login_result['session'], user, 'presence')
					geoloc_access(login_result['session'], user, 'presence')

					# Remove user from filesystem
					if os.path.exists(current_cache):
						shutil.rmtree(current_cache)

					# Notify the user
					notifications.append({
						'user': user,
						'body': 'Your Jappix Me profile has been removed. We will miss you :(',
						
						'data': {
							'id': app_id,
							'name': app_name,

							'url': config.get('app', 'url') + '/' + user,
							'type': 'profile',
							'job': 'remove',
							'success': '1'
						}
					})
				
				else:
					print "[pending:privacy] Updating privacy settings for user " + user + "..."

					# Update profile data
					if os.path.exists(current_cache + '/system'):
						# Update?
						if current_data['update'] == '1':
							last_file = open(current_cache + '/system/last', 'w')
				   			last_file.write('0')
							last_file.close()

						# Flagged?
						if current_data['flagged'] == '1':
							flagged_file = open(current_cache + '/system/flagged', 'w')
			   				flagged_file.write(phpserialize.dumps('1'))
							flagged_file.close()
						
						elif current_data['flagged'] == '0':
							flagged_file = open(current_cache + '/system/flagged', 'w')
			   				flagged_file.write(phpserialize.dumps('0'))
							flagged_file.close()
						
						# Search?
						if current_data['search'] == '1':
							search_file = open(current_cache + '/system/search', 'w')
			   				search_file.write(phpserialize.dumps('1'))
							search_file.close()
						
						elif current_data['search'] == '0':
							search_file = open(current_cache + '/system/search', 'w')
			   				search_file.write(phpserialize.dumps('0'))
							search_file.close()

						# Microblog?
						if current_data['microblog'] == 'public':
							microblog_access(login_result['session'], user, 'open')
						
						elif current_data['microblog'] == 'private':
							microblog_access(login_result['session'], user, 'presence')

						# Geoloc?
						if current_data['geoloc'] == 'public':
							geoloc_access(login_result['session'], user, 'open')
						
						elif current_data['geoloc'] == 'private':
							geoloc_access(login_result['session'], user, 'presence')

					# Notify the user
					notifications.append({
						'user': user,
						'body': 'Your Jappix Me profile has been updated. View it on ' + config.get('app', 'url') + '/' + user,
						
						'data': {
							'id': app_id,
							'name': app_name,

							'url': config.get('app', 'url') + '/' + user,
							'type': 'profile',
							'job': 'update',
							'success': '1'
						}
					})

			# Let it run 1 second max per user
			(login_result['session']).Process(1)

			# Close connection
			(login_result['session']).disconnect()

			print "[pending:main] Disconnected from " + user + "."

		else:
			# Notify the user
			notifications.append({
				'user': user,
				'body': 'Jappix Me could not connect to your account to create or update your profile, check your credentials. You can retry on ' + config.get('app', 'url') + '/',
				
				'data': {
					'id': app_id,
					'name': app_name,

					'url': config.get('app', 'url') + '/' + user,
					'type': 'profile',
					'job': 'check',
					'success': '0'
				}
			})

			print "[pending:main] Could not connect to " + user + "."
		
		print "[pending:main] Processed pending user " + user + "."

	# Send notification messages (if any)
	if notifications:
		print "[pending:main] Connecting to bot..."

		login_bot = login(config.get('bot', 'username'), config.get('bot', 'domain'), config.get('bot', 'password'))

		if login_bot['success']:
			for current_notification in notifications:
				message_app_send(login_bot['session'], current_notification['user'], current_notification['body'], current_notification['data'])

			len_notifications = len(notifications)

			if len_notifications == 1:
				print "[pending:main] Sent 1 notification message."
			else:
				print "[pending:main] Sent " + str(len_notifications) + " notification messages."

			# Let it run 1 second max after packets are sent
			(login_bot['session']).Process(1)

			# Close connection
			(login_bot['session']).disconnect()

			print "[pending:main] Disconnected from bot."
		else:
			print "[pending:main] Could not connect to bot."
	else:
		print "[pending:main] Nothing to do (no pending user)."


################
### LAUNCHER ###
################

# Checks an XMPP account
def login(user, domain, pwd):
	return_values = {
		'success': False
	}

	con = xmpp.Client(domain, debug=[])
	connector = con.connect(server=(domain, 5222))
	
	if connector and con.auth(user, pwd, 'Jappix Me (PB' + str(int(time.time())) + ')'):
		return_values['success'] = True
		return_values['session'] = con
	
	return return_values


# Initializes
if __name__ == '__main__':
	need_pending()