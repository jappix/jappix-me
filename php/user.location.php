<?php

/*
 * Jappix Me - Your public profile, anywhere
 * User location page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// Get the user current location
$current_location = getUserCurrentLocation($user_vcard, $user_geoloc);

// Read vars
$location_method = $current_location['method'];
$user_location_short = $current_location['short'];
$user_location_full = $current_location['full'];
$use_jappix = false;

// Generate the info box message
if($location_method == 'vcard')
	$infobox_msg = '<em>'.htmlspecialchars($user_fn).'</em> lives in <em><a href="http://maps.google.com/?q='.rawurlencode($user_location_full).'" target="_blank">'.htmlspecialchars($user_location_short).'</a></em> (the current location is not shared).';
else if($location_method == 'pubsub')
	$infobox_msg = '<em>'.htmlspecialchars($user_fn).'</em>\'s current location is <em><a href="http://maps.google.com/?q='.rawurlencode($user_location_full).'" target="_blank">'.htmlspecialchars($user_location_short).'</a></em>.';
else {
	$infobox_msg = '<em>'.htmlspecialchars($user_fn).'</em>\'s location is not shared. We don\'t have any information!';
	$location_method = '';
	$use_jappix = true;
}

?>

<div class="info"><?php echo $infobox_msg; ?></div>
<?php if($use_jappix) { ?>
<a class="infomsg" href="https://jappix.com/" target="_blank"><em><?php echo htmlspecialchars($user_fn); ?></em> should use Jappix to update location »</a>
<?php } ?>

<?php if($location_method) { ?>
<div class="left">
	<a href="http://maps.google.com/?q=<?php echo rawurlencode($user_location_full); ?>" target="_blank"><img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo rawurlencode($user_location_full); ?>&amp;zoom=5&amp;size=530x620&amp;markers=color:0xFFFF00%7C%7C<?php echo rawurlencode($user_location_full); ?>&amp;sensor=false" alt="" /></a>
</div>

<div class="right">
	<img src="https://maps.googleapis.com/maps/api/streetview?size=200x200&amp;location=<?php echo rawurlencode($user_location_full); ?>&amp;heading=40&amp;sensor=false" alt="" />
	<img src="https://maps.googleapis.com/maps/api/streetview?size=200x200&amp;location=<?php echo rawurlencode($user_location_full); ?>&amp;heading=125&amp;sensor=false" alt="" />
	<img src="https://maps.googleapis.com/maps/api/streetview?size=200x200&amp;location=<?php echo rawurlencode($user_location_full); ?>&amp;heading=225&amp;sensor=false" alt="" />
</div>

<div class="clear"></div>
<?php } ?>