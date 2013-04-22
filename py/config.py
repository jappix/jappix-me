'''

Jappix Me - Your public profile, anywhere
Configuration reader

License: AGPL
Author: Valerian Saliou

'''

import os
from xml.dom import minidom


############
### READ ###
############

# Configuration cache
CONFIG_CACHE = {}

# Read configuration
def read():
	# Not already cached?
	if not CONFIG_CACHE:
		# Open XML configuration file
		xml_doc = minidom.parse('../config.xml')
		node_jappix = xml_doc.getElementsByTagName('jappix')[0]

		# Populate configuration cache array
		for node_group in node_jappix.childNodes:
			if node_group.nodeName[0] == '#':
				continue

			if not node_group.nodeName in CONFIG_CACHE:
				CONFIG_CACHE[node_group.nodeName] = {}

			for node_field in node_group.childNodes:
				if node_field.nodeName[0] == '#':
					continue

				CONFIG_CACHE[node_group.nodeName][node_field.nodeName] = node_field.firstChild.data

	return CONFIG_CACHE


###########
### GET ###
###########

# Get configuration
def get(group, field):
	# Read configuration
	read()

	# Get it!
	if (group in CONFIG_CACHE) and (field in CONFIG_CACHE[group]):
		return CONFIG_CACHE[group][field]
	
	return False

# Get running path
def path():
	return os.path.abspath(os.path.join(os.path.dirname(os.path.realpath(__file__)), '..'))