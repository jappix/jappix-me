<?php

/*
 * Jappix Me - Your public profile, anywhere
 * CRON service
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// Change current working dir
chdir(dirname(__FILE__));

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
$count_update = 0;
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

		$count_update++;

		// Regenerate user XMPP data
		$current_data = requestXMPPData($current_user);

		// Any avatar for this user?
		$exists_avatar = false;
		$current_user_vcard = $current_data['vcard'];

		if(isset($current_user_vcard['vcard'])) {
			$current_vcard_arr = $current_user_vcard['vcard'][0]['sub'];
			
			if(isset($current_vcard_arr['photo'])) {
				$current_vcard_photo = $current_vcard_arr['photo'][0]['sub'];

				// Get avatar data
				$current_avatar_binval = isset($current_vcard_photo['binval']) ? $current_vcard_photo['binval'][0]['sub'] : null;
				$current_avatar_type = isset($current_vcard_photo['type']) ? substr(strrchr($current_vcard_photo['type'][0]['sub'], '/'), 1) : null;

				// Default avatar type
				if(!$current_avatar_type)
					$current_avatar_type = 'png';

				// JPEG files must have .jpg extension
				if($current_avatar_type == 'jpeg')
					$current_avatar_type = 'jpg';

				// Avatar exists?
				if($current_avatar_binval && preg_match('/^(png|jpg|gif)$/', $current_avatar_type))
					$exists_avatar = true;
			}
		}

		if($exists_avatar)
			writeCache($current_user, 'avatar', 'exists', '');
		else
			writeCache($current_user, 'avatar', 'not_exists', '');
	}
}

// Nobody updated?
if($count_update == 0)
	print('[cron] Nothing to do.'."\n");

// All done!
print("\n");
print('[cron] Done.'."\n");
print('[cron] Bye Bye!'."\n");

exit;