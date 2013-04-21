<?php

/*
 * Jappix Me - Your public profile, anywhere
 * Not found page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// 404 error reason?
switch($reason_404) {
	case 'user':
		$reason_text = 'The profile you are looking for was not found. The user may not exist, or has been removed.';
		break;
	
	case 'pending':
		$reason_text = 'The profile you are looking for is being created. Our bot is currently working hard. You should <a href="/'.$user.'">check again</a>.';
		break;
	
	case 'channel':
		$reason_text = 'The channel entry you are looking for does not exist. It may have been removed. Please <a href="/'.$user.'">go back to the channel</a>.';
		break;
	
	case 'pix':
		$reason_text = 'The picture you are looking for could not be found. It may have been removed. Please <a href="/'.$user.'/pictures">go back to the gallery</a>.';
		break;
	
	case 'locked':
		$reason_text = 'The content you want to display is locked. You are not allowed to access it.';
		break;
	
	case 'page':
		$reason_text = 'The page you want to display does not exist. Please check the URL syntax is correct.';
		break;
	
	default:
		$reason_text = 'The file you are looking for does not exist on the server.';
}

?>

<div id="wrapper">
	<a class="logo" title="« Go back to homepage" href="/"></a>
	<?php if($reason_404 == 'pending') { ?>
	<h1>Please wait. <em>A little bit</em>.</h1>
	<?php } else { ?>
	<h1>Unlucky. <em>That's an error</em>.</h1>
	<?php } ?>
	<h4><?php echo $reason_text; ?></h4>
</div>