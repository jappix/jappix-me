'''

Jappix Me - Your public profile, anywhere
Profile updater

License: AGPL
Author: Valerian Saliou

'''

import xmpp, os, shutil, time, config

BASE_DIR = os.path.dirname(os.path.realpath(__file__)) + '..'


#############
### CACHE ###
#############

# Gets the XID to be updated
def need_update():
	need = []
	sub_dirs = os.listdir(BASE_DIR + '/cache')
	current_time = int(time.time())
	
	for user in sub_dirs:
		# Not a XID?
		if user.find('@') == -1:
			continue
		
		profile_path = BASE_DIR + '/cache/' + user
		current_path = profile_path + '/system/last'
		
		if os.path.exists(current_path):
			current_file = open(current_path, 'r')
			current_content = int(current_file.read().strip())
			current_file.close()
			
   			if (current_time - current_content) > 86400:
   				vcard_file = profile_path + '/profile/vcard'
   				
   				if os.path.exists(vcard_file) and ((current_time - int(os.path.getmtime(vcard_file))) > 864000):
   					shutil.rmtree(profile_path)
   				else:
	   				need.append(user)
	   				change_file = open(current_path, 'w')
	   				change_file.write(str(current_time))
					change_file.close()
	
	return need


#############
### VCARD ###
#############

# Gets the vCard
def get_vcard(session, user):
	vcard = xmpp.Node('vCard', attrs={'xmlns': xmpp.NS_VCARD})
	iq = xmpp.Protocol('iq', user, 'get', payload=[vcard])
	return session.SendAndCallForResponse(iq, handle_vcard)

# Handles the vCard
def handle_vcard(session, stanza):
	user_from = str(stanza.getFrom())
	current_path = BASE_DIR + '/cache/' + user_from + '/raw'
	
	if os.path.exists(current_path) and (stanza.getType() == 'result') and stanza.getTag('vCard'):
		change_file = open(current_path + '/vcard', 'w')
		change_file.write(xmpp.simplexml.ustr(stanza).encode('utf-8'))
		change_file.close()


#################
### MICROBLOG ###
#################

# Gets the microblog
def get_microblog(session, user):
	items = xmpp.Node('items', attrs={'node': 'urn:xmpp:microblog:0'})
	pubsub = xmpp.Node('pubsub', attrs={'xmlns': xmpp.NS_PUBSUB}, payload=[items])
	iq = xmpp.Protocol('iq', user, 'get', payload=[pubsub])
	return session.SendAndCallForResponse(iq, handle_microblog)

# Handles the microblog
def handle_microblog(session, stanza):
	user_from = str(stanza.getFrom())
	current_path = BASE_DIR + '/cache/' + user_from + '/raw'
	
	if os.path.exists(current_path):
		change_file = open(current_path + '/microblog', 'w')
		change_file.write(xmpp.simplexml.ustr(stanza).encode('utf-8'))
		change_file.close()


##############
### GEOLOC ###
##############

# Gets the geoloc
def get_geoloc(session, user):
	items = xmpp.Node('items', attrs={'node': 'http://jabber.org/protocol/geoloc', 'max_items': '1'})
	pubsub = xmpp.Node('pubsub', attrs={'xmlns': xmpp.NS_PUBSUB}, payload=[items])
	iq = xmpp.Protocol('iq', user, 'get', payload=[pubsub])
	return session.SendAndCallForResponse(iq, handle_geoloc)

# Handles the geoloc
def handle_geoloc(session, stanza):
	user_from = str(stanza.getFrom())
	current_path = BASE_DIR + '/cache/' + user_from + '/raw'
	
	if os.path.exists(current_path):
		change_file = open(current_path + '/geoloc', 'w')
		change_file.write(xmpp.simplexml.ustr(stanza).encode('utf-8'))
		change_file.close()


################
### LAUNCHER ###
################

# Logins to XMPP
def login():
    con = xmpp.Client(config.get('bot', 'domain'), debug=[])
    con.connect(server=(config.get('bot', 'domain'), 5222), secure=False)
    con.auth(config.get('bot', 'username'), config.get('bot', 'password'), 'Jappix Me (UB' + str(int(time.time())) + ')')
    return con

# Initializes
if __name__ == '__main__':
	users_need = need_update()
	
	if len(users_need) > 0:
		con = login()
		
		for user in users_need:
			get_vcard(con, user)
			get_microblog(con, user)
			get_geoloc(con, user)
			con.Process(1)
		
		# Then, let it run 1 minute max
		for i in range(60):
			con.Process(1)
		
		con.disconnect()