<?php

/*
 * Jappix Me - Your public profile, anywhere
 * Configuration reader
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */

?>

<?php

// Configuration cache
$CONFIG_CACHE = array();

// Read configuration
function readConfig() {
	global $CONFIG_CACHE;

	// Not already cached?
	if(empty($CONFIG_CACHE)) {
		// Open XML configuration file
		$xml_data = file_get_contents('./config.xml');
		$xml_doc = new SimpleXMLElement($xml_data);
		
		// Populate configuration cache array
		foreach($xml_doc->children() as $node_group) {
			if(!isset($CONFIG_CACHE[$node_group->getName()]))
				$CONFIG_CACHE[$node_group->getName()] = array();

			foreach($node_group->children() as $node_field)
				$CONFIG_CACHE[$node_group->getName()][$node_field->getName()] = (string)$node_field;
		}
	}
	
	return $CONFIG_CACHE;
}

// Get configuration
function getConfig($group, $field) {
	global $CONFIG_CACHE;

	// Read configuration
	readConfig();

	// Get it!
	if(isset($CONFIG_CACHE[$group]) && isset($CONFIG_CACHE[$group][$field]))
		return $CONFIG_CACHE[$group][$field];

	return false;
}

?>