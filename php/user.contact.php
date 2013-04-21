<?php

/*
 * Jappix Me - Your public profile, anywhere
 * User contact page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// Read the user vCard
$user_mail = $user_phone = $user_postal = array();

// Get the vCard content
if(isset($user_vcard['vcard'])) {
	$vcard_arr = $user_vcard['vcard'][0]['sub'];
	
	// Extract the e-mail address(es)
	$user_mail = extractVCardAddress('email', $vcard_arr);
	$user_phone = extractVCardAddress('tel', $vcard_arr);
	$user_address = extractVCardAddress('adr', $vcard_arr);
}

?>

<div class="info">Find your own way to contact <em><?php echo htmlspecialchars($user_fn); ?></em>!</div>

<h4>By XMPP (using <a href="https://jappix.com/" target="_blank">Jappix</a>, yay!)</h4>
<div class="tabulate">
	<p>Use your XMPP account (<b><a href="https://jappix.com/" target="_blank">don't have one?</a></b>) to <b>chat with <em><?php echo htmlspecialchars($user_fn); ?></em></b>. You should <b><a href="xmpp:<?php echo htmlspecialchars($user); ?>?subscribe" target="_blank">add <em><?php echo htmlspecialchars($user_fn); ?></em></a></b> to your friend list before you start a chat with <em><?php echo htmlspecialchars($user_fn); ?></em> (you will see when <em><?php echo htmlspecialchars($user_fn); ?></em> will be online).</p>
	<p>If you cannot open the XMPP link below, you should configure your client to open these links (i.e. <a href="https://jappix.com/" target="_blank">Jappix</a> settings).</p>
	<p><span class="highlight"><a href="xmpp:<?php echo htmlspecialchars($user); ?>" target="_blank"><?php echo htmlspecialchars($user); ?></a></span></p>
</div>

<?php if(!empty($user_mail)) { ?>
<h4>By mail (<em>Lazy Way</em>)</h4>
<div class="tabulate">
	<p><b>Send an e-mail to <em><?php echo htmlspecialchars($user_fn); ?></em></b> with the address (or multiple addresses) below.</p>
	<?php foreach($user_mail as $user_mail_type => $user_mail_value) { ?>
	<p><span class="highlight"><span class="underline"><?php echo noSpamMail($user_mail_value); ?></span> (<b><?php echo htmlspecialchars($user_mail_type); ?></b>)</span></p>
	<?php } ?>
</div>
<?php } ?>

<?php if(!empty($user_phone)) { ?>
<h4>How to <em><a href="http://en.wikipedia.org/wiki/Call_Me_(Blondie_song)" target="_blank">Call Me</a></em> ♪</h4>
<div class="tabulate">
	<p><b>Call <em><?php echo htmlspecialchars($user_fn); ?></em></b> using the phone number (or multiple numbers) below.</p>
	<p><em><?php echo htmlspecialchars($user_fn); ?></em>'s phone may not be located in the same country as yours. <b>Some extra costs may apply</b> for international calls (depending of your telephone or mobile phone operator).</p>
	<?php foreach($user_phone as $user_phone_type => $user_phone_value) { ?>
	<p><span class="highlight"><?php echo htmlspecialchars($user_phone_value); ?> (<b><?php echo htmlspecialchars($user_phone_type); ?></b>)</span></p>
	<?php } ?>
</div>
<?php } ?>

<?php if(!empty($user_address)) { ?>
<h4>By <em><a href="http://en.wikipedia.org/wiki/Pigeon_post" target="_blank">Pigeon Post</a></em>®</h4>
<div class="tabulate">
	<p>Contact <em><?php echo htmlspecialchars($user_fn); ?></em> <b>through the postal way</b> with the address (or multiple addresses) below.</p>
	<?php foreach($user_address as $user_address_type => $user_address_value) { ?>
	<p><span class="highlight address"><a href="http://maps.google.com/maps?q=<?php echo rawurlencode($user_address_value); ?>" target="_blank"><?php echo htmlspecialchars($user_address_value); ?></a> (<b><?php echo htmlspecialchars($user_address_type); ?></b>)<span class="submap"><img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo rawurlencode($user_address_value); ?>&amp;zoom=11&amp;size=400x150&amp;markers=size:mid%7Ccolor:0xFFFF00%7C%7C<?php echo rawurlencode($user_address_value); ?>&amp;sensor=false" alt="" /><img class="streetview" src="https://maps.googleapis.com/maps/api/streetview?size=200x150&amp;location=<?php echo rawurlencode($user_address_value); ?>&amp;heading=125&amp;sensor=false" alt="" /></span></span></p>
	<?php } ?>
</div>
<?php } ?>