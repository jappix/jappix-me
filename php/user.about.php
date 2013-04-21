<?php

/*
 * Jappix Me - Your public profile, anywhere
 * User about page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// Read the user vCard
$user_bio = '';

// Get the vCard content
if(isset($user_vcard['vcard'])) {
	$vcard_arr = $user_vcard['vcard'][0]['sub'];
	
	if(isset($vcard_arr['desc']))
		$user_bio = trim($vcard_arr['desc'][0]['sub']);
}

?>

<div class="info">This is the resume of <em><?php echo htmlspecialchars($user_fn); ?></em>'s life. Some kind of biography.</div>

<div class="tabulate resume">
	<?php if($user_bio)
		echo formatText($user_bio);
	else { ?>
		<p><em><?php echo htmlspecialchars($user_fn); ?></em> did not write anything!</p>
	<?php } ?>
</div>