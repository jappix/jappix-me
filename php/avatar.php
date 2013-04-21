<?php

/*
 * Jappix Me - Your public profile, anywhere
 * Avatar retrieval service
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// Avatar data
$avatar_file = $avatar_type = '';

// Get the avatar settings
$avatar_str = $subsetting;

if(strpos($avatar_str, '.') !== false)
	$avatar_settings = explode('.', $avatar_str);
else
	$avatar_settings = array($avatar_str, 'png');

$avatar_size = $avatar_settings[0];
$avatar_format = $avatar_settings[1];

if($avatar_size && ctype_digit($avatar_size)) {
	$avatar_size = intval($avatar_size);
	
	// Too big!
	if($avatar_size > 250)
		$avatar_size = 250;
	
	// Too small!
	else if($avatar_size < 16)
		$avatar_size = 16;
}

else
	$avatar_size = 'original';

if(!$avatar_format || !preg_match('/^(png|jpg|gif)$/', $avatar_format))
	$avatar_format = 'png';

$avatar_filename = $avatar_size.'.'.$avatar_format;

// Path to be used
if(!$exists_profile || file_exists('./cache/'.$user.'/avatar/not_exists'))
	$base_path = './img';
else
	$base_path = './cache/'.$user;

// From cache (the reference avatar is always PNG-encoded)
if(file_exists($base_path.'/avatar/original.png')) {
	if(($avatar_filename == 'original.png') || file_exists($base_path.'/avatar/'.$avatar_filename)) {
		$avatar_file = file_get_contents($base_path.'/avatar/'.$avatar_filename);
		$avatar_type = $avatar_format;
	}
}

// From vCard data
else {
	// Extract the vCard data
	$avatar_type = $avatar_binval = '';
	
	if(isset($user_vcard['vcard'])) {
		$vcard_arr = $user_vcard['vcard'][0]['sub'];
		
		if(isset($vcard_arr['photo'])) {
			$vcard_photo = $vcard_arr['photo'][0]['sub'];
			
			if(isset($vcard_photo['type']))
				$avatar_type = substr(strrchr($vcard_photo['type'][0]['sub'], '/'), 1);
			if(isset($vcard_photo['binval']))
				$avatar_binval = $vcard_photo['binval'][0]['sub'];
		}
	}
	
	// Default avatar type
	if(!$avatar_type)
		$avatar_type = 'png';
	
	// JPEG files must have .jpg extension
	if($avatar_type == 'jpeg')
		$avatar_type = 'jpg';
	
	// Decode the base64-encoded avatar (only PNG, GIF and JPG supported)
	if($avatar_binval && preg_match('/^(png|jpg|gif)$/', $avatar_type)) {
		$avatar_file = base64_decode($avatar_binval);
		$avatar_exists = true;
	}
	
	// Default avatar
	else
		$avatar_exists = false;
	
	// Reset the avatar folder
	removeDir('./cache/'.$user.'/avatar');
	createDir('./cache/'.$user.'/avatar');
	
	// Avatar exists marker
	if($avatar_exists) {
		writeCache($user, 'avatar', 'exists', '');
		
		// Store the original avatar
		writeCache($user, 'avatar', 'original.png', $avatar_file);
		
		// PNG-encode it if necessary
		if($avatar_type != 'png')
			resizeImage('./cache/'.$user.'/avatar/original.png', $avatar_type, 'png');
		
		// Resized/re-encoded avatar wanted (to pass the next condition)?
		if($avatar_filename != 'original.png')
			$avatar_file = $avatar_type = '';
	}
	
	else {
		writeCache($user, 'avatar', 'not_exists', '');
		
		$base_path = './img';
		$avatar_file = file_get_contents($base_path.'/avatar/'.$avatar_filename);
		$avatar_type = $avatar_format;
	}
}

// No data?
if(($avatar_filename != 'original.png') && (!$avatar_file || !$avatar_type)) {
	// New size
	if($avatar_size == 'original')
		$avatar_size = 0;
	
	// We must resize the avatar from the original
	$avatar_file = file_get_contents($base_path.'/avatar/original.png');
	file_put_contents($base_path.'/avatar/'.$avatar_filename, $avatar_file);
	resizeImage($base_path.'/avatar/'.$avatar_filename, 'png', $avatar_format, $avatar_size);
	
	// And get it again!
	$avatar_file = file_get_contents($base_path.'/avatar/'.$avatar_filename);
	$avatar_type = $avatar_format;
}

// Still nothing?!
if(!$avatar_file || !$avatar_type) {
	$avatar_file = file_get_contents('./img/avatar/original.png');
	$avatar_type = 'png';
}

// MIME type
$avatar_mime = 'image/'.$avatar_type;

if($avatar_type == 'jpg')
	$avatar_mime = 'image/jpeg';

// Set the HTTP headers
header('Content-Length: '.strlen($avatar_file));
header('Content-Type: '.$avatar_mime);
header('Pragma: public');
header('Cache-Control: maxage=86400');
header('Expires: '.gmdate('D, d M Y H:i:s', (time() + 86400)).' GMT');

// Return the binary output
exit($avatar_file);

?>