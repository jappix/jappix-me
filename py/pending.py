'''

Jappix Me - Your public profile, anywhere
Pending profile checker

License: AGPL
Author: Valerian Saliou

'''

import xmpp, os, shutil, time, phpserialize, config

BASE_DIR = os.path.dirname(os.path.realpath(__file__)) + '..'


###############
### PENDING ###
###############

# Apply the settings to the pending accounts
def need_pending():
	need = []
	sub_dirs = os.listdir(BASE_DIR + '/pending')
	
	for user in sub_dirs:
		# Not a XID?
		if user.find('@') == -1:
			continue
		
		current_pending = BASE_DIR + '/pending/' + user
		current_cache = BASE_DIR + '/cache/' + user
		current_file = open(current_pending, 'r')
		current_content = current_file.read()
		current_file.close()
		current_data = phpserialize.loads(current_content)
		
		os.remove(current_pending)
		
		if login(current_data['usr'], current_data['srv'], current_data['pwd']):
			if current_data['type'] == 'new':
				if not os.path.exists(current_cache):
					os.mkdir(current_cache)
					os.mkdir(current_cache + '/raw')
					os.mkdir(current_cache + '/system')
					
					os.chmod(current_cache, 0750)
					os.chmod(current_cache + '/raw', 0750)
					os.chmod(current_cache + '/system', 0750)
				
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
			
			elif current_data['type'] == 'privacy':
				if current_data['remove'] == '1':
					if os.path.exists(current_cache):
						shutil.rmtree(current_cache)
				
				else:
					if os.path.exists(current_cache + '/system'):
						if current_data['update'] == '1':
							last_file = open(current_cache + '/system/last', 'w')
				   			last_file.write('0')
							last_file.close()
					
					if os.path.exists(current_cache + '/system'):
						if current_data['flagged'] == '1':
							flagged_file = open(current_cache + '/system/flagged', 'w')
			   				flagged_file.write(phpserialize.dumps('1'))
							flagged_file.close()
						
						elif current_data['flagged'] == '0':
							flagged_file = open(current_cache + '/system/flagged', 'w')
			   				flagged_file.write(phpserialize.dumps('0'))
							flagged_file.close()
						
						if current_data['search'] == '1':
							search_file = open(current_cache + '/system/search', 'w')
			   				search_file.write(phpserialize.dumps('1'))
							search_file.close()
						
						elif current_data['search'] == '0':
							search_file = open(current_cache + '/system/search', 'w')
			   				search_file.write(phpserialize.dumps('0'))
							search_file.close()


################
### LAUNCHER ###
################

# Checks an XMPP account
def login(user, domain, pwd):
	con = xmpp.Client(domain, debug=[])
	connector = con.connect(server=(domain, 5222))
	
	if not connector or not con.auth(user, pwd, 'Jappix Me (PB' + str(int(time.time())) + ')'):
		return False
	
	return True

# Initializes
if __name__ == '__main__':
	need_pending()