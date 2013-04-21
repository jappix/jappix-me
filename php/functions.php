<?php

/*
 * Jappix Me - Your public profile, anywhere
 * App functions
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


/* CACHE MANAGEMENT FUNCTIONS */

// Read a cache file
function readCache($user, $setting, $file) {
	return file_get_contents('./cache/'.$user.'/'.$setting.'/'.$file);
}

// Write a cache file
function writeCache($user, $setting, $file, $data) {
	if(is_writable('./cache')) {
		file_put_contents('./cache/'.$user.'/'.$setting.'/'.$file, $data);
		return true;
	}
	
	return false;
}

// Checks if a cache file is valid
function validCache($user, $setting, $file) {
	$path = './cache/'.$user.'/'.$setting.'/'.$file;
	$exists_path = file_exists($path);
	
	// Not valid cache if not exist or too old (1 day)
	if(($exists_path && (time() - (filemtime($path)) >= 86400)) || !$exists_path)
		return false;
	
	return true;
}

// Returns the general cache date
function dateCache($user) {
	$current_date = $file_date = time();
	$file_path = './cache/'.$user.'/profile/vcard';
	$update_path = './cache/'.$user.'/system/last';
	
	global $exists_profile;
	
	if($exists_profile)
		$file_date = filemtime($file_path);
	if(file_exists($update_path))
		$update_date = intval(file_get_contents($update_path));
	
	// Process stamp
	$left = $current_date - $file_date;
	
	// Convert into hours
	$left = intval($left/3600);
	
	// Manage exceptions
	if($left < 1)
		$left = 1;
	
	$pass_left = $left;
	$remaining = 24 - $left;
	
	if($remaining < 1)
		$remaining = 1;
	
	// Return strings
	if($left > 1)
		$left = $left.' hours';
	else
		$left = $left.' hour';
	
	if($remaining > 1)
		$remaining = $remaining.' hours';
	else
		$remaining = $remaining.' hour';
	
	// Will be updated!
	if($update_date == 0)
		$pass_left = 0;
	
	return array($left, $remaining, $pass_left);
}


/* IMAGE MANIPULATION FUNCTIONS */

// Resize an image with GD
function resizeImage($path, $input_ext, $output_ext, $new_size = 0) {
	// No GD?
	if(!function_exists('gd_info'))
	    return false;
	
	try {
        // Initialize GD
        switch($input_ext) {
            case 'jpg':
                $img_resize = imagecreatefromjpeg($path);
                
                break;
        	
            case 'gif':
                $img_resize = imagecreatefromgif($path);
                
                break;
        
            default:
                $img_resize = imagecreatefrompng($path);
        }
        
        // Get the image size
        $img_size = getimagesize($path);
        $img_width = $img_size[0];
        $img_height = $img_size[1];
		
        // Necessary to change the image width
        if($new_size && ($img_width > $img_height)) {
            $new_width = $new_size;
            $img_process = (($new_width * 100) / $img_width);
            $new_height = (($img_height * $img_process) / 100);
        }
		
        // Necessary to change the image height
        else if($new_size && ($img_width < $img_height)) {
            $new_height = $new_size;
            $img_process = (($new_height * 100) / $img_height);
            $new_width = (($img_width * $img_process) / 100);
        }
        
        // Necessary to change both width and height
        else if($new_size && ($img_width == $img_height))
            $new_width = $new_height = $new_size;
		
        // Else, just use the old sizes
        else {
        	$new_width = $img_width;
        	$new_height = $img_height;
        }
        
        // Create the new image
        $new_img = imagecreatetruecolor($new_width, $new_height);
        
        // Must keep alpha pixels?
        if(($input_ext == 'png') && ($output_ext == 'png')) {
            imagealphablending($new_img, false);
            imagesavealpha($new_img, true);
            
            // Set transparent pixels
            $transparent = imagecolorallocatealpha($new_img, 255, 255, 255, 127);
            imagefilledrectangle($new_img, 0, 0, $new_width, $new_height, $transparent);
        }
        
        // Copy the new image
        imagecopyresampled($new_img, $img_resize, 0, 0, 0, 0, $new_width, $new_height, $img_size[0], $img_size[1]);
		
        // Destroy the old data
        imagedestroy($img_resize);
        unlink($path);
		
        // Write the new image
        switch($output_ext) {
            case 'jpg':
                imagejpeg($new_img, $path, 85);
                
                break;
        	
            case 'gif':
                imagegif($new_img, $path);
                
                break;
            
            default:
            	imagepng($new_img, $path);
        }
        
        return true;
	}
	
	catch(Exception $e) {
	    return false;
	}
}


/* FILE MANAGEMENT FUNCTIONS */

// Creates a given directory
function createDir($dir) {
	if(!$dh = @opendir($dir))
		@mkdir($dir, 0777, true);
}

// Removes a given directory (with all sub-elements)
function removeDir($dir) {
	// Can't open the dir
	if(!$dh = @opendir($dir))
		return;
	
	// Loop the current dir to remove its content
	while(false !== ($obj = readdir($dh))) {
		// Not a "real" directory
		if(($obj == '.') || ($obj == '..'))
			continue;
		
		// Not a file, remove this dir
		if(!@unlink($dir.'/'.$obj))
			removeDir($dir.'/'.$obj);
	}
	
	// Close the dir and remove it!
	closedir($dh);
	@rmdir($dir);
}


/* DATA MANAGEMENT FUNCTIONS */

// Gets the XMPP data
function getXMPPData($user) {
	global $exists_user, $exists_profile, $exists_vcard, $exists_microblog, $exists_geoloc;
	
	// Fresh new raw data is waiting for us!
	if($exists_vcard && $exists_microblog && $exists_geoloc)
		return requestXMPPData($user);
	
	// We can read the cache
	return readXMPPData($user);
}

// Requests the XMPP data
function requestXMPPData($user) {
	// Get raw XML data
	$data = array(
					'vcard' => xmlToArray(strToXML(readCache($user, 'raw', 'vcard'))),
					'microblog' => xmlToArray(strToXML(readCache($user, 'raw', 'microblog'))),
					'geoloc' => xmlToArray(strToXML(readCache($user, 'raw', 'geoloc'))),
					'pictures'
				 );
	
	// Reset cache dirs
	removeDir('./cache/'.$user.'/raw');
	createDir('./cache/'.$user.'/raw');
	removeDir('./cache/'.$user.'/profile');
	createDir('./cache/'.$user.'/profile');
	removeDir('./cache/'.$user.'/pubsub');
	createDir('./cache/'.$user.'/pubsub');
	
	// Store the data
	$microblog_arr = $data['microblog'];
	
	foreach($data as $sub => $content) {
		if($sub == 'pictures') {
			$data['pictures'] = genPicturesArray($user, $microblog_arr);
			continue;
		}
		
		if($sub == 'microblog') {
			$data['microblog'] = genChannelArray($user, $microblog_arr);
			continue;
		}
		
		if($sub == 'vcard')
			$type = 'profile';
		else
			$type = 'pubsub';
		
		writeCache($user, $type, $sub, serialize($content));
	}
	
	// Remove cache avatars
	removeDir('./cache/'.$user.'/avatar');
	createDir('./cache/'.$user.'/avatar');
	
	$data['flagged'] = unserialize(readCache($user, 'system', 'flagged'));
	$data['search'] = unserialize(readCache($user, 'system', 'search'));
	
	return $data;
}

// Reads the XMPP data
function readXMPPData($user) {
	$data = array();
	
	// Items to read
	$to_read = array('vcard', 'microblog', 'geoloc', 'pictures', 'flagged', 'search');
	
	foreach($to_read as $current) {
		// Current type?
		if($current == 'vcard')
			$type = 'profile';
		else if(($current == 'flagged') || ($current == 'search'))
			$type = 'system';
		else
			$type = 'pubsub';
		
		if(file_exists('./cache/'.$user.'/'.$type.'/'.$current))
			$data[$current] = unserialize(readCache($user, $type, $current));
		else
			$data[$current] = array();
	}
	
	return $data;
}


/* UTILITIES */

// Validates a mail or XMPP address
function isAddress($address) {
	return preg_match('/^([^@]+)@([^@]+)$/', $address);
}

// Truncates a string
function truncate($string, $max = 20, $rep = '') {
	$string = preg_replace('/\s+?(\S+)?$/', $rep, substr($string, 0, $max));
	
	return $string;
}

// Gets the user full name
function getUserName($type, $user, $array) {
	// Get the user nickname
	$nickname = $full_name = $given_name = $family_name = '';
	
	if(isset($array['vcard'])) {
		$vcard_arr = $array['vcard'][0]['sub'];
		
		if(isset($vcard_arr['nickname']))
			$nickname = ucfirst(trim($vcard_arr['nickname'][0]['sub']));
		if(isset($vcard_arr['fn']))
			$family_name = ucfirst(trim($vcard_arr['fn'][0]['sub']));
		
		if(isset($vcard_arr['n'])) {
			$vcard_n = $vcard_arr['n'][0]['sub'];
			
			if(isset($vcard_n['given']))
				$given_name = ucfirst(trim($vcard_n['given'][0]['sub']));
			if(isset($vcard_n['family']))
				$family_name = ucfirst(trim($vcard_n['family'][0]['sub']));
		}
	}
	
	// Default nickname
	if(!$nickname)
		$nickname = ucfirst(trim(substr($user, 0, strrpos($user, '@'))));
	
	// User nickname requested?
	if($type == 'nick')
		return $nickname;
	
	// That's the full name we requested!
	if(!$full_name) {
		if($given_name) {
			$full_name = $given_name;
			
			if($family_name)
				$full_name .= ' '.$family_name;
		}
		
		else
			$full_name = $nickname;
	}
	
	return $full_name;
}

// Reads the user birthday UNIX stamp
function getUserBirthdayStamp($array) {
	$birthday = '';
	
	// Extract the value
	if(isset($array['vcard'])) {
		$vcard_arr = $array['vcard'][0]['sub'];
		
		if(isset($vcard_arr['bday']))
			$birthday = trim($vcard_arr['bday'][0]['sub']);
	}
	
	// Any birthday date?
	if($birthday && preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/', $birthday))
		$stamp = strtotime($birthday);
	else
		$stamp = 0;
	
	// Invalid stamp?
	if((time() - $stamp) < (86400 * 365))
		$stamp = 0;
	
	return $stamp;
}

// Gets the user birthday
function getUserBirthdayDate($stamp) {
	if(!$stamp)
		return '';
	
	return date('j F Y', $stamp);
}

// Gets the user age
function getUserAge($stamp) {
	if(!$stamp)
		return '';
	
	$year = date('Y', $stamp);
	$month = date('n', $stamp);
	$day = date('j', $stamp);
	
	$today['month'] = date('n');
	$today['day'] = date('j');
	$today['year'] = date('Y');
	
	$years = $today['year'] - $year;
	
	if($today['month'] <= $month) {
		if($month == $today['month']) {
			if($day > $today['day'])
				$years--;
		}
		
		else
			$years--;
	}
	
	return $years;
}

// Gets the user location
function getUserSite($array) {
	$site = '';
	
	// Extract the single values
	if(isset($array['vcard'])) {
		$vcard_arr = $array['vcard'][0]['sub'];
		
		if(isset($vcard_arr['url']))
			$site = trim($vcard_arr['url'][0]['sub']);
	}
	
	return $site;
}

// Extract multiple addresses from vCard
function extractVCardAddress($type, $array) {
	$result = array();
	
	// Nothing?
	if(!isset($array[$type]) || empty($array[$type][0]['sub']))
		return $result;
	
	// Element containing the requested information
	if($type == 'email')
		$sub_element = array('userid');
	else if($type == 'tel')
		$sub_element = array('number');
	else
		$sub_element = array('street', 'pcode', 'locality', 'region', 'ctry');
	
	// Element types
	$sub_types = array('home', 'work');
	
	// Get each sub-element
	foreach($array[$type] as $sub_array) {
		// Get the type
		$current_type = 'home';
		
		foreach($sub_types as $current_sub_type) {
			if(isset($sub_array['sub'][$current_sub_type]))
				$current_type = $current_sub_type;
			
			break;
		}
		
		// Get the value
		$current_result = '';
		
		foreach($sub_element as $current_sub_element) {
			if(isset($sub_array['sub'][$current_sub_element])) {
				$current_sub_value = trim($sub_array['sub'][$current_sub_element][0]['sub']);
				
				if($current_sub_value) {
					if($current_result)
						$current_result .= ', ';
					
					$current_result .= $current_sub_value;
				}
			}
		}
		
		if($current_result)
			$result[$current_type] = $current_result;
	}
	
	return $result;
}

// Generates the HTML code to get no spam for a mail address
function noSpamMail($address) {
	$at = strripos($address, '@');
	$before = substr($address, 0, $at);
	$after = substr($address, $at+1);
	
	return htmlspecialchars($before).'<img class="at" src="/img/at.png" alt="" />'.$after;
}

// Formats a plain text to a HTML one
function formatText($text) {
	// HTML-encode
	$html = htmlspecialchars($text);
	$html = nl2br($html);
	
	// Text style
	$html = preg_replace('/(^|\s|>|\()((\/)([^<>\'"\/]+)(\/))($|\s|<|\))/im', '$1<em>$4</em>$6', $html);
	$html = preg_replace('/(^|\s|>|\()((\*)([^<>\'"\*]+)(\*))($|\s|<|\))/im', '$1<b>$4</b>$6', $html);
	$html = preg_replace('/(^|\s|>|\()((_)([^<>\'"_]+)(_))($|\s|<|\))/im', '$1<span style="text-decoration: underline;">$4</span>$6', $html);
	
	// Apply links
	$html = preg_replace('/(\s|<br \/>|^)(([a-zA-Z0-9\._-]+)@([a-zA-Z0-9\.\/_-]+))(,|\s|$)/i', '$1<a href="xmpp:$2" target="_blank">$2</a>$5', $html);
	$html = preg_replace('/(\s|<br \/>|^|\()((https?|ftp|file|xmpp|irc|mailto|vnc|webcal|ssh|ldap|smb|magnet|spotify)(:)([^<>\'"\s\)]+))/im', '$1<a href="$2" target="_blank">$2</a>', $html);
	
	return $html;
}

// Converts a file name type to a file category
function fileToCat($name) {
	$ext = $name;
	
	if(strpos($name, '.'))
		$ext = substr(strrchr($name, '.'), 1);
	
	switch($ext) {
		// Images
		case 'jpg':
		case 'jpeg':
		case 'png':
		case 'bmp':
		case 'gif':
		case 'tif':
		case 'svg':
		case 'psp':
		case 'xcf':
			$cat = 'image';
			
			break;
		
		// Videos
		case 'ogv':
		case 'ogg':
		case 'mkv':
		case 'avi':
		case 'mov':
		case 'mp4':
		case 'm4v':
		case 'wmv':
		case 'asf':
		case 'mpg':
		case 'mpeg':
		case 'ogm':
		case 'rmvb':
		case 'rmv':
		case 'qt':
		case 'flv':
		case 'ram':
		case '3gp':
		case 'avc':
			$cat = 'video';
			
			break;
		
		// Sounds
		case 'oga':
		case 'mka':
		case 'flac':
		case 'mp3':
		case 'wav':
		case 'm4a':
		case 'wma':
		case 'rmab':
		case 'rma':
		case 'bwf':
		case 'aiff':
		case 'caf':
		case 'cda':
		case 'atrac':
		case 'vqf':
		case 'au':
		case 'aac':
		case 'm3u':
		case 'mid':
		case 'mp2':
		case 'snd':
		case 'voc':
			$cat = 'audio';
			
			break;
		
		// Documents
		case 'pdf':
		case 'odt':
		case 'ott':
		case 'sxw':
		case 'stw':
		case 'ots':
		case 'sxc':
		case 'stc':
		case 'sxi':
		case 'sti':
		case 'pot':
		case 'odp':
		case 'ods':
		case 'doc':
		case 'docx':
		case 'docm':
		case 'xls':
		case 'xlsx':
		case 'xlsm':
		case 'xlt':
		case 'ppt':
		case 'pptx':
		case 'pptm':
		case 'pps':
		case 'odg':
		case 'otp':
		case 'sxd':
		case 'std':
		case 'std':
		case 'rtf':
		case 'txt':
		case 'htm':
		case 'html':
		case 'shtml':
		case 'dhtml':
		case 'mshtml':
			$cat = 'document';
			
			break;
		
		// Packages
		case 'tgz':
		case 'gz':
		case 'tar':
		case 'ar':
		case 'cbz':
		case 'jar':
		case 'tar.7z':
		case 'tar.bz2':
		case 'tar.gz':
		case 'tar.lzma':
		case 'tar.xz':
		case 'zip':
		case 'xz':
		case 'rar':
		case 'bz':
		case 'deb':
		case 'rpm':
		case '7z':
		case 'ace':
		case 'cab':
		case 'arj':
		case 'msi':
			$cat = 'package';
			
			break;
		
		// Others
		default:
			$cat = 'other';
			
			break;
	}
	
	return $cat;
}

// Converts an XML string to an XML doc
function strToXML($xmlstr) {
	return simplexml_load_string($xmlstr);
}

// Converts an XML string into an array
function xmlToArray($xml) {
	$array = array();
	
	// Loop the XML elements
	if(!empty($xml) && count($xml->children())) {
		foreach($xml->children() as $name => $node) {
			$name = strtolower($name);
			
			// Element index (for multiple same elements)
			if(isset($array[$name]))
				$i = count($array[$name]);
			else
				$i = 0;
			
			// Element attributes
			$attributes = array();
			
			foreach($node->attributes() as $attr_key => $attr_value)
				$attributes[strtolower($attr_key)] = sprintf("%s", $attr_value);
			
			$array[$name][$i]['attr'] = $attributes;
			
			// Not the last element?
			if(!empty($node) && count($node->children()))
				$array[$name][$i]['sub'] = xmlToArray($node);
			else
				$array[$name][$i]['sub'] = sprintf("%s", $node);
		}
	}
	
	return $array;
}


/* CHANNEL MANAGEMENT FUNCTIONS */

// Generates the channel HTML code
function genChannel($user, $user_microblog, $start, $stop, $disp_id = null) {
	$k = 0;
	
	// Only keep the requested entry?
	if($disp_id && isset($user_microblog[$disp_id])) {
		$request_entry = $user_microblog[$disp_id];
		$user_microblog = array();
		$user_microblog[$disp_id] = $request_entry;
	}
	
	foreach($user_microblog as $id => $sub) {
		$k++;
		
		// Continue to first item?
		if($k < $start)
			continue;
		
		// Last item got?
		if($k > $stop)
			return;
		
		// Read the data
		$content = $stamp = $author_uri = $attached = $geoloc_short = $geoloc_full = $street = $locality = $region = $country = $lat = $lon = $post_id = $comments_href = '';
		
		if(isset($sub['item'])) {
			$item_sub = $sub['item'][0]['sub'];
			$item_attr = $sub['item'][0]['attr'];
			
			if(isset($item_attr['id']))
				$post_id = $item_attr['id'];
			
			if(isset($item_sub['entry'])) {
				$entry = $item_sub['entry'][0]['sub'];
				
				// Extract data
				if(isset($entry['body']))
					$content = trim($entry['body'][0]['sub']);
				if(isset($entry['content']))
					$content = trim($entry['content'][0]['sub']);
				if(isset($entry['published']))
					$stamp = trim($entry['published'][0]['sub']);
				if(isset($entry['link']))
					$attached = trim($entry['link'][0]['sub']);
				if(isset($entry['source'])) {
					$source = $entry['source'][0]['sub'];
					
					if(isset($source['author'])) {
						$author = $source['author'][0]['sub'];
						
						if(isset($author['uri']))
							$author_uri = trim($author['uri'][0]['sub']);
						
						if($author_uri && strpos($author_uri, ':'))
							$author_uri = substr(strrchr($author_uri, ':'), 1);
					}
				}
				
				// Extract comments
				if(isset($entry['link'])) {
					foreach($entry['link'] as $current_link) {
						$current_attr = $current_link['attr'];
						
						if(($current_attr['rel'] == 'replies') && isset($current_attr['title']) && ($current_attr['title'] == 'comments') && isset($current_attr['href'])) {
							$comments_href = $current_attr['href'];
							
							break;
						}
					}
				}
				
				// Extract geoloc
				if(isset($entry['geoloc'])) {
					$geoloc_adr = $entry['geoloc'][0]['sub'];
					
					if(isset($geoloc_adr['street']))
						$street = ucfirst(trim($geoloc_adr['street'][0]['sub']));
					if(isset($geoloc_adr['locality']))
						$locality = ucfirst(trim($geoloc_adr['locality'][0]['sub']));
					if(isset($geoloc_adr['region']))
						$region = ucfirst(trim($geoloc_adr['region'][0]['sub']));
					if(isset($geoloc_adr['country']))
						$country = ucfirst(trim($geoloc_adr['country'][0]['sub']));
					if(isset($geoloc_adr['lat']))
						$lat = ucfirst(trim($geoloc_adr['lat'][0]['sub']));
					if(isset($geoloc_adr['lon']))
						$lon = ucfirst(trim($geoloc_adr['lon'][0]['sub']));
					
					// Short user location
					if($locality)
						$geoloc_short .= $locality;
					
					if($region) {
						if($geoloc_short)
							$geoloc_short .= ', ';
						
						$geoloc_short .= $region;
					}
					
					if($country) {
						if($geoloc_short)
							$geoloc_short .= ', ';
						
						$geoloc_short .= $country;
					}
					
					// Full user location
					if($lat && $lon)
						$geoloc_full = $lat.','.$lon;
					else if($street && $geoloc_short)
						$geoloc_full = $street.', '.$geoloc_short;
					else
						$geoloc_full = $geoloc_short;
				}
				
				// Author URI
				if(!$author_uri || !strpos($author_uri, '@'))
					$author_uri = $user;
			}
		}
		
		// Display the data
		if($content) {
			$channel_displayed = true;
			
			$class_comments = '';
			
			if($comments_href)
				$class_comments = ' hascomments';
		
		?>
			<div class="tabulate" id="<?php echo htmlspecialchars($id); ?>">
				<img class="left avatar" src="/<?php echo htmlentities($author_uri); ?>/avatar/48.png" alt="" />
				
				<div class="right">
					<div class="content"><?php echo formatText($content); ?></div>
					<div class="meta">
						<?php if($disp_id) { ?>
						<span class="date<?php echo $class_comments; ?>">
						<?php } else { ?>
						<a class="date<?php echo $class_comments; ?>" href="/<?php echo htmlspecialchars($user); ?>/channel/<?php echo htmlspecialchars($id); ?>">
						<?php }
						if($stamp)
							echo htmlentities(date('jS F Y, g:ia', strtotime($stamp)));
						else
							echo 'Unknown date';
						?>
						<?php if($disp_id) { ?>
						</span>
						<?php } else { ?>
						</a>
						<?php }
						if($geoloc_short) { ?>
						<a class="geoloc" href="http://maps.google.com/?q=<?php echo rawurlencode($geoloc_full); ?>" target="_blank"><?php echo htmlspecialchars($geoloc_short); ?></a>
						<?php }
						if(strtolower($author_uri) != $user) { ?>
						<a class="repeat" href="/<?php echo htmlentities($author_uri); ?>" data-xid="<?php echo htmlentities($author_uri); ?>">Repeated</a>
						<?php } ?>
					</div>
					<div class="clear"></div>
					<?php if(isset($entry['link'])) {
						$attached_html = '';
						
						foreach($entry['link'] as $current_link) {
							$current_attr = $current_link['attr'];
							
							if(!isset($current_attr['rel']) || ($current_attr['rel'] != 'enclosure'))
								continue;
							
							// Get attributes
							$attr_title = $attr_type = $attr_href = '';
							
							if(isset($current_attr['title']))
								$attr_title = $current_attr['title'];
							if(isset($current_attr['type']))
								$attr_type = $current_attr['type'];
							if(isset($current_attr['href']))
								$attr_href = $current_attr['href'];
							
							// No URL?
							if(!$attr_href)
								continue;
							
							// No title?
							if(!$attr_title)
								$attr_title = 'Attached file';
							
							// Link content
							$link_content = htmlspecialchars($attr_title);
							$display_type = 'simple';
							$display_cat = fileToCat($attr_href);
							$href_target = ' target="_blank"';
							
							// Image thumbnail?
							if(isset($current_link['sub']['link'])) {
								$sub_link = $current_link['sub']['link'][0];
								
								if(isset($sub_link['attr']['href']) && isset($sub_link['attr']['title']) && ($sub_link['attr']['title'] == 'thumb'))
									$thumb = $sub_link['attr']['href'];
								else
									$thumb = '';
								
								if($thumb) {
									$link_content = '<img src="'.htmlspecialchars($thumb).'" alt="" />';
									$display_type = 'thumb';
									
									// Picture?
									if($attr_type == 'image/jpeg') {
										// Change file URL
										$picture_id = md5($attr_href);
										$attr_href = '/'.$user.'/pictures/'.$picture_id.'#content';
										$href_target = '';
									}
								}
							}
							
							// Display it!
							$attached_html .= '<a class="file '.$display_type.' '.$display_cat.'" title="'.htmlspecialchars($attr_title).'" href="'.htmlspecialchars($attr_href).'"'.$href_target.'>'.$link_content.'</a>';
						}
						
						if($attached_html)
							echo '<div class="attached">'.$attached_html.'</div>';
					} ?>
				</div>
				
				<div class="clear"></div>
			</div>
		<?php }
	}
	
	if(!$disp_id)
		$comments_href = '';
	
	return array('post' => $post_id, 'comments' => $comments_href);
}

// Generates the channel array
function genChannelArray($user, $user_microblog) {
	$microblog_items = $return_microblog = array();
	
	// First checks
	if(isset($user_microblog['pubsub'])) {
		$microblog_contents = $user_microblog['pubsub'][0]['sub'];
		
		if(isset($microblog_contents['items']))
			$microblog_items = $microblog_contents['items'][0]['sub'];
	}
	
	// Process each item
	if(isset($microblog_items['item'])) {
		foreach($microblog_items['item'] as $current_item) {
			$current_final = array('item' => array($current_item));
			$current_sub = $current_item['sub'];
			$current_content = $current_stamp = '';
			
			if(isset($current_sub['entry'])) {
				$current_entry = $current_sub['entry'][0]['sub'];
				
				if(isset($current_entry['body']))
					$current_content = trim($current_entry['body'][0]['sub']);
				if(isset($current_entry['content']))
					$current_content = trim($current_entry['content'][0]['sub']);
				if(isset($current_entry['published']))
					$current_stamp = trim($current_entry['published'][0]['sub']);
			}
			
			$current_id = md5($current_content.$current_stamp);
			$return_microblog[$current_id] = $current_final;
		}
	}
	
	writeCache($user, 'pubsub', 'microblog', serialize($return_microblog));
	
	return $return_microblog;
}

// Generates the pictures HTML code
function genPictures($user, $user_pictures, $start, $stop) {
	$tabulate_val = 'recent';
	$tabulate_all = array('recent');
	$pix_html = '';
	
	// Display pictures
	$k = 0;
	
	foreach($user_pictures as $current_pix) {
		$k++;
		
		// Continue to first item?
		if($k < $start)
			continue;
		
		// Last item got?
		if($k > $stop) {
			$pix_html .= '<div class="clear"></div></div>';
			echo $pix_html;
			return;
		}
		
		// Parse stamp
		if($current_pix['stamp'])
			$current_pix_stamp = intval(strtotime($current_pix['stamp']));
		else
			$current_pix_stamp = time() - 2678400;
		
		// Get the number of days since
		$current_pix_age = round(abs(time() - $current_pix_stamp) / 86400);
		
		// Recent?
		if($current_pix_age <= 30)
			$current_tab_val = 'Recent';
		
		// Older?
		else
			$current_tab_val = date('Y', $current_pix_stamp);
		
		// New tabulate block?
		if($tabulate_val != $current_tab_val) {
			$tabulate_val = $current_tab_val;
			array_push($tabulate_all, $tabulate_val);
			
			if($pix_html)
				$pix_html .= '<div class="clear"></div></div>';
			
			$pix_html .= '<div class="tabulate"><h1>'.htmlspecialchars($current_tab_val).'</h1>';
		}
		
		// Display thumb
		$pix_html .= '<div class="separatetop"><div class="separate"><a class="thumb" href="/'.htmlspecialchars($user).'/pictures/'.htmlspecialchars($current_pix['id']).'#content" title="'.htmlspecialchars($current_pix['title']).'"><img src="'.htmlspecialchars($current_pix['thumb']).'" alt="" /></a></div></div>';
	}
	
	// Very last item got!
	if($k == count($user_pictures))
		$pix_html .= '<div class="clear"></div></div>';
	
	// Nothing?
	if(!preg_match('/tabulate/', $pix_html))
		$pix_html = '';
	
	echo $pix_html;
}

// Generates the pictures array
function genPicturesArray($user, $user_microblog) {
	$microblog_items = array();
	
	if(isset($user_microblog['pubsub'])) {
		$microblog_contents = $user_microblog['pubsub'][0]['sub'];
		
		if(isset($microblog_contents['items']))
			$microblog_items = $microblog_contents['items'][0]['sub'];
	}
	
	if(isset($microblog_items['item'])) {
		$pix_arr = array();
		
		foreach($microblog_items['item'] as $sub) {
			// Read the data
			$entry = array();
			$stamp = $post_id = '';
			
			if(isset($sub['attr']) && isset($sub['attr']['id']))
				$post_id = $sub['attr']['id'];
			
			if(isset($sub['sub']['entry'][0]['sub'])) {
				$entry = $sub['sub']['entry'][0]['sub'];
				
				// Extract stamp
				if(isset($entry['published']))
					$stamp = trim($entry['published'][0]['sub']);
			}
			
			// Display the data
			if(isset($entry['link'])) {
				foreach($entry['link'] as $current_link) {
					$current_attr = $current_link['attr'];
					
					// Not an attachement?
					if(!isset($current_attr['rel']) || ($current_attr['rel'] != 'enclosure'))
						continue;
					
					// Get attributes
					$attr_title = $attr_type = $attr_href = $thumb = $comments_href = '';
					
					if(isset($current_attr['title']))
						$attr_title = $current_attr['title'];
					if(isset($current_attr['type']))
						$attr_type = $current_attr['type'];
					if(isset($current_attr['href']))
						$attr_href = $current_attr['href'];
					
					// No URL?
					if(!$attr_href)
						continue;
					
					// No title?
					if(!$attr_title)
						$attr_title = '';
					
					// Image thumbnail?
					if(isset($current_link['sub']['link'])) {
						foreach($current_link['sub']['link'] as $sub_link) {
							$current_attr = $sub_link['attr'];
							
							if(($current_attr['rel'] == 'replies') && isset($current_attr['title']) && ($current_attr['title'] == 'comments_file') && isset($current_attr['href'])) {
								$comments_href = $current_attr['href'];
								
								continue;
							}
							
							if(($current_attr['rel'] == 'self') && isset($current_attr['title']) && ($current_attr['title'] == 'thumb') && ($attr_type == 'image/jpeg') && isset($current_attr['href'])) {
								$thumb = $current_attr['href'];
								
								continue;
							}
						}
						
						if($thumb) {
							$picture_id = md5($attr_href);
							
							$pix_arr[$picture_id] = array(
														'id' => $picture_id,
														'href' => $attr_href,
														'thumb' => $thumb,
														'stamp' => $stamp,
														'title' => $attr_title,
														'post' => $post_id,
														'comments' => $comments_href
													);
							}
					}
				}
			}
		}
		
		// Set the next & previous IDs to the picture list array
		$id_index = array();
		
		foreach($pix_arr as $pix_id => $pix_sub)
			array_push($id_index, $pix_id);
		
		$loop_id = 0;
		$loop_previous = $loop_next = '';
		$loop_max = count($id_index) - 1;
		
		while($loop_id <= $loop_max) {
			if(!$id_index[$loop_id])
				continue;
			
			$loop_previous = $loop_pthumb = $loop_next = $loop_nthumb = '';
			
			// Previous data?
			if($loop_id > 0) {
				$loop_previous = $pix_arr[$id_index[$loop_id - 1]]['id'];
				$loop_pthumb = $pix_arr[$id_index[$loop_id - 1]]['thumb'];
			}
			
			// Next data?
			if($loop_id < $loop_max) {
				$loop_next = $pix_arr[$id_index[$loop_id + 1]]['id'];
				$loop_nthumb = $pix_arr[$id_index[$loop_id + 1]]['thumb'];
			}
			
			// Push data!
			$pix_arr[$id_index[$loop_id]]['previous'] = $loop_previous;
			$pix_arr[$id_index[$loop_id]]['next'] = $loop_next;
			$pix_arr[$id_index[$loop_id]]['pthumb'] = $loop_pthumb;
			$pix_arr[$id_index[$loop_id]]['nthumb'] = $loop_nthumb;
			
			$loop_id++;
		}
		
		// Store the picture list array
		writeCache($user, 'pubsub', 'pictures', serialize($pix_arr));
		
		return $pix_arr;
	}
	
	writeCache($user, 'pubsub', 'pictures', serialize(array()));
	
	return array();
}

// Generates the comments HTML code
function genComments($user, $user_fn, $post_id, $comments_href) {
	// Parse the comments href
	$param_server = $param_node = '';
	
	if($comments_href && preg_match('/^xmpp:(.+)\?;node=(.+)/i', $comments_href)) {
		$param_server = preg_replace('/^xmpp:(.+)\?;node=(.+)/i', '$1', $comments_href);
		$param_node = urldecode(preg_replace('/^xmpp:(.+)\?;node=(.+)/i', '$2', $comments_href));
	}
	
	// Output the HTML code
	if($param_server && $param_node) {
		echo '<div class="clear"></div>';
		
		echo '<div class="comments" data-server="'.htmlspecialchars($param_server).'" data-node="'.htmlspecialchars($param_node).'" data-post="'.htmlspecialchars($post_id).'" data-owner="'.htmlspecialchars($user).'">';
			echo '<div class="comments-content">';
				echo '<a class="comments-load">Load comments</a>';
			echo '</div>';
			
			echo '<div class="comments-tools">';
				echo '<div class="comments-info">';
					echo '<p>You can <b>anonymously post comments</b> here.</p>';
					echo '<p>Anonymous <b>comments cannot be removed</b>.</p>';
					echo '<p>To comment under your real identity, you need to <a href="xmpp:'.htmlspecialchars($user).'?subscribe" target="_blank"><b>add <em>'.htmlspecialchars($user_fn).'</em></b></a> to your friends and <a href="https://jappix.com/" target="_blank"><b>use Jappix</b></a>.</p>';
				echo '</div>';
				
				echo '<form class="comments-form">';
					echo '<input class="name" type="text" placeholder="Type your name..." required="required" />';
					echo '<textarea class="body" placeholder="Type your comment here..." required="required"></textarea>';
					echo '<input class="submit" type="submit" value="Send your comment" disabled="disabled" />';
					
					echo '<div class="clear"></div>';
				echo '</form>';
			echo '</div>';
			
			echo '<div class="clear"></div>';
		echo '</div>';
	}
}


/* LOCATION MANAGEMENT FUNCTIONS */

// Gets the user location (home)
function getUserLocation($user, $array) {
	$location = $locality = $region = $country = '';
	
	// Extract the single values
	if(isset($array['vcard'])) {
		$vcard_arr = $array['vcard'][0]['sub'];
		
		if(isset($vcard_arr['adr'])) {
			$vcard_adr = $vcard_arr['adr'][0]['sub'];
			
			if(isset($vcard_adr['locality']))
				$locality = ucfirst(trim($vcard_adr['locality'][0]['sub']));
			if(isset($vcard_adr['region']))
				$region = ucfirst(trim($vcard_adr['region'][0]['sub']));
			if(isset($vcard_adr['ctry']))
				$country = ucfirst(trim($vcard_adr['ctry'][0]['sub']));
		}
	}
	
	// Group the values
	if($locality)
		$location .= $locality;
	
	if($region) {
		if($location)
			$location .= ', ';
		
		$location .= $region;
	}
	
	if($country) {
		if($location)
			$location .= ', ';
		
		$location .= $country;
	}
	
	return $location;
}

// Gets the user location (current)
function getUserCurrentLocation($user_vcard, $user_geoloc) {
	$location_method = $user_location_short = $user_location_full = $street = $locality = $region = $country = $lat = $lon = '';
	
	// Get from Pubsub?
	if(isset($user_geoloc['pubsub'])) {
		$geoloc_arr = $user_geoloc['pubsub'][0]['sub'];
		
		if(isset($geoloc_arr['items'])) {
			$geoloc_items = $geoloc_arr['items'][0]['sub'];
			
			if(isset($geoloc_items['item'])) {
				$geoloc_item = $geoloc_items['item'][0]['sub'];
				
				if(isset($geoloc_item['geoloc'])) {
					$geoloc_adr = $geoloc_item['geoloc'][0]['sub'];
					
					if(isset($geoloc_adr['street']))
						$street = ucfirst(trim($geoloc_adr['street'][0]['sub']));
					if(isset($geoloc_adr['locality']))
						$locality = ucfirst(trim($geoloc_adr['locality'][0]['sub']));
					if(isset($geoloc_adr['region']))
						$region = ucfirst(trim($geoloc_adr['region'][0]['sub']));
					if(isset($geoloc_adr['country']))
						$country = ucfirst(trim($geoloc_adr['country'][0]['sub']));
					if(isset($geoloc_adr['lat']))
						$lat = ucfirst(trim($geoloc_adr['lat'][0]['sub']));
					if(isset($geoloc_adr['lon']))
						$lon = ucfirst(trim($geoloc_adr['lon'][0]['sub']));
					
					$location_method = 'pubsub';
				}
			}
		}
	}
	
	// Get from vCard?
	if(!$locality && !$region && !$country && isset($user_vcard['vcard'])) {
		$vcard_arr = $user_vcard['vcard'][0]['sub'];
		
		if(isset($vcard_arr['adr'])) {
			$vcard_adr = $vcard_arr['adr'][0]['sub'];
			
			if(isset($vcard_adr['street']))
				$street = ucfirst(trim($vcard_adr['street'][0]['sub']));
			if(isset($vcard_adr['locality']))
				$locality = ucfirst(trim($vcard_adr['locality'][0]['sub']));
			if(isset($vcard_adr['region']))
				$region = ucfirst(trim($vcard_adr['region'][0]['sub']));
			if(isset($vcard_adr['ctry']))
				$country = ucfirst(trim($vcard_adr['ctry'][0]['sub']));
			
			$location_method = 'vcard';
		}
	}
	
	// Short user location
	if($locality)
		$user_location_short .= $locality;
	
	if($region) {
		if($user_location_short)
			$user_location_short .= ', ';
		
		$user_location_short .= $region;
	}
	
	if($country) {
		if($user_location_short)
			$user_location_short .= ', ';
		
		$user_location_short .= $country;
	}
	
	// Full user location
	if($lat && $lon)
		$user_location_full = $lat.','.$lon;
	else if($street && $user_location_short)
		$user_location_full = $street.', '.$user_location_short;
	else
		$user_location_full = $user_location_short;
	
	// Still nothing?
	if(!$user_location_short)
		$location_method = 'none';
	
	// Generates the array to return
	$return_arr = array(
						'method' => $location_method,
						'short' => $user_location_short,
						'full' => $user_location_full
					   );
	
	return $return_arr;
}

?>