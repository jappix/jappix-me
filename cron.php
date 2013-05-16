<?php

/*
 * Jappix Me - Your public profile, anywhere
 * CRON service
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// Initialize
include('./php/config.php');
include('./php/functions.php');

// Disable PHP error reporting
if(getConfig('app', 'mode') != 'development') {
	ini_set('display_errors', 'off');
	ini_set('error_reporting', 0);
}

// Don't allow non-CLI requests
if(sourceClient() != 'cli')
	exit('Command-Line CRON Service. Please call me from your shell.');

// Greet the shell user
print('[cron] Welcome, master!'."\n");
print('[cron] Scanning users...'."\n");
print("\n");

// Regenerate updated user data
$available_users = scandir('./cache');

foreach($available_users as $current_user) {
	// Not a XID?
	if(!strpos($current_user, '@'))
		continue;

	// Check for fresh raw data
	$exists_vcard = file_exists('./cache/'.$current_user.'/raw/vcard');
	$exists_microblog = file_exists('./cache/'.$current_user.'/raw/microblog');
	$exists_geoloc = file_exists('./cache/'.$current_user.'/raw/geoloc');

	// Check a raw file is available
	if($exists_vcard && $exists_microblog && $exists_geoloc) {
		print('[cron] Regenerating storage for '.$current_user.'...'."\n");

		// Regenerate user XMPP data
		$current_data = requestXMPPData($current_user);

		// Any avatar for this user?
		if(isset($current_data['vcard'])) {
			$current_vcard_arr = $current_data['vcard'][0]['sub'];
			
			if(isset($current_vcard_arr['photo'])) {
				$current_vcard_photo = $vcard_arr['photo'][0]['sub'];
				
				// User has an avatar
				if(isset($current_vcard_photo['type']) && isset($current_vcard_photo['binval']) && $current_vcard_photo['type'] && $current_vcard_photo['binval']) {
					// Avatar exists?
					if($current_vcard_photo['binval'] && preg_match('/^(png|jpg|gif)$/', $current_vcard_photo['type']))
						writeCache($current_user, 'avatar', 'exists', '');
					else
						writeCache($current_user, 'avatar', 'not_exists', '');
				}
			}
		}
	}
}

// All done!
print("\n");
print('[cron] Done.'."\n");
print('[cron] Bye Bye!'."\n");

exit;