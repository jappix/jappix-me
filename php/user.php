<?php

/*
 * Jappix Me - Your public profile, anywhere
 * User page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// Generate the aka code
$aka_code = '';

if($user_fn != $user_nick)
	$aka_code = ' <span class="nickname"><em>aka</em> '.htmlspecialchars($user_nick).'</span>';

// Generate the user birthday code
if($user_bday_stamp)
	$birthday_code = '<b>'.htmlspecialchars($user_age).'</b> year old (born '.htmlspecialchars($user_bday).')';
else
	$birthday_code = 'We don\'t know the age of this user';

// Generate the location code
if($user_location)
	$location_code = 'Lives in <b><a href="http://maps.google.com/maps?q='.rawurlencode($user_location).'" target="_blank">'.htmlspecialchars($user_location).'</a></b>';
else
	$location_code = 'Lives somewhere in the Universe, between Venus and Mars';

// Sidebar button to be highlighted
$current_sidebar = array();
$current_sidebar['channel'] = ' channel';
$current_sidebar['pictures'] = ' pictures';
$current_sidebar['location'] = ' location';
$current_sidebar['contact'] = ' contact';
$current_sidebar['about'] = ' about';
$current_sidebar['share'] = ' share';
$current_sidebar['export'] = ' export';
$current_sidebar[$subpage] = ' '.$subpage.' current';

// Get the cache date
$cache_date = dateCache($user);

// Is it the user's birthday?
$birthday_today = false;
$birthday_class = '';

if($user_bday_stamp && (date('j/n') == date('j/n', $user_bday_stamp))) {
	$birthday_today = true;
	$birthday_class = ' class="birthday"';
}

?>

<div id="top" class="wrapped">
	<a class="logo" href="/" title="Go to the Jappix Me homepage"></a>
	<span class="desc"><span class="desc_center"><?php if($birthday_today) echo 'Happy birthday, '.htmlspecialchars($user_fn).'!'; else echo $chapo; ?></span></span>
	
	<a class="button new" href="/new"><span class="button_center">Create your profile »</span></a>
	<a class="button privacy" href="/privacy"><span class="button_center">Privacy</span></a>
	<a class="button feed" href="/<?php echo htmlspecialchars($user); ?>/feed"><span class="button_center">Feed</span></a>
	
	<div class="clear"></div>
</div>

<div id="quicklook"<?php echo $birthday_class; ?>>
	<div class="wrapped">
		<div class="avatarize">
			<img class="avatar" src="/<?php echo $user; ?>/avatar/96.png" alt="" title="Yay! That's me!" />
		</div>
		
		<div class="actions">
			<a class="add" title="Add <?php echo htmlspecialchars($user_fn); ?> to your friends »" href="xmpp:<?php echo htmlspecialchars($user); ?>?subscribe" target="_blank">Add</a>
			<a class="chat" title="Start a chat with <?php echo htmlspecialchars($user_fn); ?> »" href="xmpp:<?php echo htmlspecialchars($user); ?>" target="_blank">Chat</a>
			<?php if($user_site) { ?>
			<a class="site" title="Access <?php echo htmlspecialchars($user_fn); ?>'s website »" href="<?php echo htmlspecialchars($user_site); ?>" target="_blank">Site</a>
			<?php } ?>
		</div>
		
		<div class="infos">
			<h1 class="name"><?php echo htmlspecialchars($user_fn).$aka_code ?></h1>
			<p class="age"><?php echo $birthday_code; ?></p>
			<p class="home"><?php echo $location_code; ?></p>
			<p class="xmpp">Socializes using <b><a href="xmpp:<?php echo htmlspecialchars($user); ?>" target="_blank"><?php echo htmlspecialchars($user); ?></a></b></p>
		</div>
		
		<div class="clear"></div>
	</div>
</div>

<div id="content" class="wrapped">
	<div class="sidebar">
		<a class="switch<?php echo $current_sidebar['channel']; ?>" href="/<?php echo htmlspecialchars($user); ?>">Channel</a>
		<a class="switch<?php echo $current_sidebar['pictures']; ?>" href="/<?php echo htmlspecialchars($user); ?>/pictures">Pictures</a>
		<a class="switch<?php echo $current_sidebar['location']; ?>" href="/<?php echo htmlspecialchars($user); ?>/location">Location</a>
		<a class="switch<?php echo $current_sidebar['contact']; ?>" href="/<?php echo htmlspecialchars($user); ?>/contact">Contact</a>
		<a class="switch<?php echo $current_sidebar['about']; ?>" href="/<?php echo htmlspecialchars($user); ?>/about">About</a>
		
		<div style="height: 20px;"></div>
		
		<a class="switch<?php echo $current_sidebar['share']; ?>" href="/<?php echo htmlspecialchars($user); ?>/share">Share</a>
		<a class="switch<?php echo $current_sidebar['export']; ?>" href="/<?php echo htmlspecialchars($user); ?>/export">Export</a>
	</div>
	
	<div class="wrapper <?php echo $subpage; ?>">
		<?php include('./php/user.'.$subpage.'.php'); ?>
	</div>
	
	<div class="clear"></div>
</div>