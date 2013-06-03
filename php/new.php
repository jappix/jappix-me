<?php

/*
 * Jappix Me - Your public profile, anywhere
 * New profile page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */

?>

<div id="top" class="wrapped">
	<a class="logo" href="/" title="Go to the Jappix Me homepage"></a>
	<span class="desc"><span class="desc_center"><?php echo $chapo; ?></span></span>
	
	<a class="button" href="/"><span class="button_center">« Cancel the profile creation</span></a>
	
	<div class="clear"></div>
</div>

<div id="explain">
	<div class="wrapped">You are going to <b>create your own profile</b>. Please <b>follow this form step by step</b> to ensure everything is okay to finalize the process.</div>
</div>

<div id="content" class="wrapped">
	<div class="tabulate step" id="step1">
		<div class="number">1</div>
		<div class="stepped">
			<h1>Read our privacy statement</h1>
			<p>Please <b>read our short privacy policy</b> about how we store data, how you can control your privacy and what are your rights on the data we store. <b>You must accept it</b> before you continue.</p>
			<p>Note that this is a <b>simplified version</b> of our full <b><a href="https://legal.jappix.com/">legal disclaimer</a></b>, that you must also read and accept.</p>
			
			<div class="policy">
				<p>Jappix Me is a french service, which is running under the french laws concerning computing and freedom. Jappix is registered at the <a href="http://www.cnil.fr/" target="_blank">CNIL</a> (<a href="http://en.wikipedia.org/wiki/CNIL" target="_blank">more details here</a>) under the <em>1437294</em> number.</p>
				<p>Your profile can only be created by yourself. We check your identity by asking your XMPP credentials. Once given, they are queued and our bot will login and check they are valid. Then, it will make your social channel and your current location public.</p>
				<p>Once done, our bot will logout from your account, remove your credentials and get all the required public data from your account. Your data will be then updated automatically every day, but you can force the update through the privacy manager.</p>
				<p>You can change your privacy settings whenever you want to using the privacy page. The link is located at the top of each profile page. Your credentials will be asked once again. Your profile can be removed from our system whenever you want to.</p>
				<p>The data you send on your profile (pictures, videos and other files) are own by you and only you. We don't set any extra-license on the external content we display on our website.</p>
				<p>If your profile contains shocking or adult-only files - mostly pictures or videos - you must flag it as innapropriate for children in your privacy settings. Such profiles are not indexed in search engines for safety reasons.</p>
				<p>You are not allowed to use Jappix Me for illegal purposes, such as publishing pedophilic or degrading content. Such profiles will be removed without any garanty. Remember that Jappix Me is hosted in France, so that French legislation applies to it.</p>
				<p>We don't do any commercial usage of your data. We won't sell your data to others and no copy of your data will be distributed to third parties. These copies include server files and server backup files. They will be kept private and stored on a secure system.</p>
			</div>
			
			<p>This privacy policy may be <b>subject to changes</b> in the future. Please <b>check it regularly</b>. If you don't agree to the new commit, you will need to remove your profile using the privacy tool.</p>
			
			<label><input class="read" type="checkbox" name="read" disabled="disabled" /> I have read and I understand the terms of use.</label>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="tabulate step disabled" id="step2">
		<div class="number">2</div>
		<div class="stepped">
			<h1>Login to your account</h1>
			<p>Please provide us <b>your XMPP account</b> credentials to allow us create your profile (<b>we don't store your password</b>).</p>
			<p>If you <b>don't have an XMPP account</b>, please <b>create <a href="https://jappix.com/" target="_blank">a free one here</a></b> to get your Jappix Me profile.</p>
			
			<form>
				<label>Address <input type="text" name="address" placeholder="yourself@jappix.com" disabled="disabled" required="required" /></label>
				<label>Password <input type="password" name="password" disabled="disabled" required="required" autocomplete="off" /></label>
				
				<input class="submit" type="submit" value="Login to my account" disabled="disabled" /><span class="status"></span>
			</form>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="tabulate step disabled" id="step3">
		<div class="number">3</div>
		<div class="stepped">
			<h1>Queue your profile</h1>
			<p>Yay! Your credentials are valid! Please now <b>confirm that you really want your profile to be created</b>. A queue request will be send to our bot.</p>
			
			<button class="create" disabled="disabled">Yes, I want my profile to be created now!</button><span class="status"></span>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="tabulate step disabled" id="step4">
		<div class="number">4</div>
		<div class="stepped">
			<h1>Invite your friends</h1>
			<p>Boost your social network, <b>invite your friends on Jappix Me</b>, so that you will be able to browse <b>more profile pages</b>.</p>
			<p>Consider this as <b>a wonderful gift</b> for your all friends. Note that we don't send twice an invitation to an user if he already received one from someone else.</p>
			
			<button class="invite" disabled="disabled">Invite my friends now!</button><span class="skip"> or <a>skip this</a></span><span class="status"></span>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="tabulate step disabled" id="step5">
		<div class="number">5</div>
		<div class="stepped">
			<h1>Thank you!</h1>
			<p>Your profile will be <b>ready in minutes</b> (we will notify you). Thanks a lot for using Jappix Me!</p>
			<p>Then, start <b>posting social updates using <a href="https://jappix.com/" target="_blank">Jappix</a></b>. These updates will appear <b>a few hours later</b> on your Jappix Me profile.</p>
			<p class="reveal">Here is the link to your profile: <a class="highlight" target="_blank"></a></p>
			<p>When you are waiting, you should have a look to <b>other Jappix services</b>: <a href="https://project.jappix.com/" target="_blank">Jappix Project</a> (your own social cloud), <a href="https://mini.jappix.com/" target="_blank">Jappix Mini</a> (your own website mini-chat).</p>
			<a class="button back" href="/">« Back to homepage</a>
		</div>
		
		<div class="clear"></div>
	</div>
</div>