<?php

/*
 * Jappix Me - Your public profile, anywhere
 * User export page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */

?>

<?php

// Widgets
$widget_url = '';

// Profile widget?
if(preg_match('/^profile((\/+)(.+)?)?$/', $subsetting)) {
	$subbed = trim(strtolower(substr(strstr($subsetting, '/'), 1)));
	
	// Default value
	if(!$subbed || (($subbed != 'small') && ($subbed != 'medium') && ($subbed != 'big')))
		$subbed = 'medium';
	
	// Redirect location
	$widget_url = $path_root_protocol.'://'.$path_root_domain.'/img/profile_'.rawurlencode($subbed).'.png';
}

// Current location widget?
if(preg_match('/^location((\/+)(.+)?)?$/', $subsetting)) {
	$subbed = trim(strtolower(substr(strstr($subsetting, '/'), 1)));
	
	// Extract useful data
	if(strpos($subbed, '/') != false) {
		$size = trim(substr($subbed, 0, strpos($subbed, '/')));
		$zoom = trim(substr($subbed, strpos($subbed, '/') + 1));
	}
	
	else {
		$size = trim($subbed);
		$zoom = '';
	}
	
	// Default settings
	if(!$size || !preg_match('/^[0-9]+x[0-9]+$/', $size))
		$size = '200x200';
	if(!$zoom || !preg_match('/^[0-9]+$/', $zoom))
		$zoom = 10;
	
	// Get the user current location
	$current_location = getUserCurrentLocation($user_geoloc);
	$user_location_full = $current_location['full'];
	
	// Redirect location
	$widget_url = 'https://maps.googleapis.com/maps/api/staticmap?center='.rawurlencode($user_location_full).'&zoom='.rawurlencode($zoom).'&size='.rawurlencode($size).'&markers=color:0xFFFF00%7C%7C'.rawurlencode($user_location_full).'&sensor=false';
}

if($widget_url) {
	header('Status: 303 See Other');
	header('Pragma: public');
	header('Cache-Control: maxage=86400');
	header('Expires: '.gmdate('D, d M Y H:i:s', (time() + 86400)).' GMT');
	header('Location: '.$widget_url);
	
	exit;
}

?>

<div class="info">Export some elements of <em><?php echo htmlspecialchars($user_fn); ?></em>'s profile to display them on the Web!</div>

<h4>Link to the profile</h4>
<div class="tabulate">
	<p><b>The link to</b> <em><?php echo htmlspecialchars($user_fn); ?></em>'s profile is:</p>
	<p><a class="highlight" href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>" target="_blank"><?php echo $path_root_protocol.'://'.$path_root_domain.'/'; ?><b><?php echo htmlspecialchars($user); ?></b></a></p>
</div>

<h4>Avatar</h4>
<div class="tabulate">
	<p>You can use <em><?php echo htmlspecialchars($user_fn); ?></em>'s <b>avatar within any external website</b> using this link:</p>
	<p><a class="highlight" href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/avatar" target="_blank"><?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/<b>avatar</b></a></p>
	<p>You can also <b>request the avatar with the type</b> (<em>png</em>, <em>gif</em> or <em>jpg</em>) <b>and size</b> (<em>16px</em> to <em>250px</em>, can either be <em>original</em> to keep the original avatar size) of your choice:</p>
	<p><a class="highlight" href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/avatar/64.jpg" target="_blank"><?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/avatar/<b>64</b>.<b>jpg</b></a></p>
	<p><a class="highlight" href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/avatar/250.gif" target="_blank"><?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/avatar/<b>250</b>.<b>gif</b></a></p>
	<p><a class="highlight" href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/avatar/original.jpg" target="_blank"><?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/avatar/<b>original</b>.<b>jpg</b></a></p>
</div>

<h4>Profile button</h4>
<div class="tabulate">
	<p><b>Add a button</b> to your website or blog sidebar to allow users <b>view the profile</b> and add <em><?php echo htmlspecialchars($user_fn); ?></em> as a friend.</p>
	<p>You can choose whether to use a <em>small</em>, <em>medium</em> or <em>big</em> button. Here we have the <em>medium</em> one:</p>
	<p><span class="highlight">&lt;a href="<a href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>" target="_blank"><?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?></a>" target="_blank"&gt;&lt;img src="<a href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/export/profile/medium" target="_blank"><?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/export/profile/<b>medium</b></a>" alt="" /&gt;&lt;/a&gt;</span></p>
</div>

<h4>Location map</h4>
<div class="tabulate">
	<p><b>Add a map</b> to your website or blog sidebar to let users know <b>where <em><?php echo htmlspecialchars($user_fn); ?></em> is</b> on the globe.</p>
	<p>You can define both map size and zoom in the URL. Here we have a small <em>200px</em> map with a <em>big</em> zoom:</p>
	<p><span class="highlight">&lt;a href="<a href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/location" target="_blank"><?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/location</a>" target="_blank"&gt;&lt;img src="<a href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/export/location/200x200/12" target="_blank"><?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>/export/location/<b>200x200</b>/<b>12</b></a>" alt="" /&gt;&lt;/a&gt;</span></p>
</div>