<?php

/*
 * Jappix Me - Your public profile, anywhere
 * User share page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */

?>

<div class="info">Share <em><?php echo htmlspecialchars($user_fn); ?></em>'s profile with your friends on other social networks!</div>

<h4>Share on Facebook</h4>
<div class="tabulate">
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) {return;}
		js = d.createElement(s); js.id = id;
		js.src = 'https://connect.facebook.net/en_US/all.js#xfbml=1&appId=190693020991176';
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	<p><span class="upper"><b>Like</b> <em><?php echo htmlspecialchars($user_fn); ?></em>'s profile page:</span><div class="fb-like" data-send="false" data-layout="button_count" data-width="200" data-show-faces="false" data-href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>"></div><div class="clear"></div></p>
	<p><span class="upper"><b>Send</b> <em><?php echo htmlspecialchars($user_fn); ?></em>'s profile to your Facebook friends:</span><div class="fb-send" data-href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>"></div></p>
</div>

<h4>Share on Twitter</h4>
<div class="tabulate">
	<p><span class="upper"><b>Tweet</b> a link to <em><?php echo htmlspecialchars($user_fn); ?></em>'s profile page:</span><a href="https://twitter.com/share" class="twitter-share-button" data-text="<?php echo htmlentities($user); ?>'s public profile on Jappix Me" data-count="horizontal" data-url="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>">Tweet</a><script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script></p>
</div>

<h4>Share on Google+</h4>
<div class="tabulate">
	<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
	<p><span class="upper"><b>+1</b> <em><?php echo htmlspecialchars($user_fn); ?></em>'s profile page:</span><g:plusone size="medium" href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>"></g:plusone></p>
</div>

<h4>Mail your friends!</h4>
<div class="tabulate">
	<p>Mail your friends the profile page of <em><?php echo htmlspecialchars($user_fn); ?></em>: <b><a href="mailto:?subject=<?php echo rawurlencode($user_fn." on Jappix Me"); ?>&amp;body=<?php echo rawurlencode("Hey!\n\nHave a look at ".$user_fn."'s profile on Jappix Me!\nRight there: ".$path_root_protocol."://".$path_root_domain."/".$user."\n\nCheers."); ?>" target="_blank">create the mail</a></b></p>
</div>

<h4>Grandma <em>way of sharing</em></h4>
<div class="tabulate">
	<p>You can also send a postal card (using Pigeon or anything else) to all your friends, telling them about this profile. That's is your grandmother's favorite social network. Really.</p>
	<p>Don't forget to write down on the card the link to <em><?php echo htmlspecialchars($user_fn); ?></em>'s profile page:</p>
	<p><a class="highlight" href="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user); ?>" target="_blank"><?php echo $path_root_protocol.'://'.$path_root_domain.'/'; ?><b><?php echo htmlspecialchars($user); ?></b></a></p>
</div>