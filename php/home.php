<?php

/*
 * Jappix Me - Your public profile, anywhere
 * Home page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */

?>

<div id="top" class="wrapped">
	<a class="logo" href="/" title="Go to the Jappix Me homepage"></a>
	<span class="desc"><span class="desc_center"><?php echo $chapo; ?></span></span>
	
	<a class="button new" href="/new"><span class="button_center">Create your profile »</span></a>
	<a class="button privacy" href="/privacy"><span class="button_center">Privacy</span></a>
	
	<div class="clear"></div>
</div>

<div id="explain">
	<div class="wrapped">Jappix Me is a <b>free tool to create your own profile</b>. Stream your life, share your pictures, introduce yourself. <b>Own your digital identity</b>.</div>
</div>

<div id="content" class="wrapped">
	<div class="left">
		<h4>“On the verge to free your digital identity.”</h4>
		<p><em>Imagine</em>. All your social life in one public place you own. Share your status updates, your pictures, your current location, your contact details and even more.</p>
		<p><em>That's Jappix Me</em>. A powerful tool to display your own XMPP profile on the Web (read an <a href="http://en.wikipedia.org/wiki/Extensible_Messaging_and_Presence_Protocol" target="_blank">introduction to XMPP</a>). Best of that - it is free.</p>
		<p><b>Jump in a new social world</b>. Yay!</p>
		
		<a class="button new" href="/new"><span class="button_center">Create my profile »</span></a>
		
		<div class="clear"></div>
		
		<div class="random">
			<?php
			
			$scanned_users = scandir('./cache');
			$available_users = array();
			
			// Remove unwanted items from the array
			foreach($scanned_users as $current_scanned_user) {
				if(strpos($current_scanned_user, '@'))
					array_push($available_users, $current_scanned_user);
			}
			
			shuffle($available_users);
			$k = 14;
			$i = 1;
			
			foreach($available_users as $current_user) {
				if($i > $k)
					break;
				
				if(!file_exists('./cache/'.$current_user.'/avatar/exists'))
					continue;
				
				echo '<a href="/'.htmlspecialchars($current_user).'" title="'.htmlspecialchars($current_user).'"><img src="/'.htmlspecialchars($current_user).'/avatar/40.png" alt="" /></a> ';
				$i++;
			}
			
			?>
		</div>
	</div>
	
	<div class="right">
		<a class="big" href="/valerian@jappix.com" title="Share your status updates »"><img src="/img/home_screen_1.png" alt="" /></a>
		<a class="small" href="/julien@jappix.com/location" title="Tell the world where you currently are »"><img src="/img/home_screen_3.png" alt="" /></a>
		<a class="medium" href="/valerian@jappix.com/pictures/96babd4a2b2725ab9b61d3b98e0b3798" title="Share your pictures »"><img src="/img/home_screen_2.png" alt="" /></a>
	</div>
	
	<div class="clear"></div>
</div>