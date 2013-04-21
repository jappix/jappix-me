<?php

/*
 * Jappix Me - Your public profile, anywhere
 * Profile privacy page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */

?>

<div id="top" class="wrapped">
	<a class="logo" href="/" title="Go to the Jappix Me homepage"></a>
	<span class="desc"><span class="desc_center"><?php echo $chapo; ?></span></span>
	
	<a class="button" href="/"><span class="button_center">« Go back to homepage</span></a>
	
	<div class="clear"></div>
</div>

<div id="explain">
	<div class="wrapped">You are about to <b>change your privacy settings</b>. Please first <b>login to your account</b>. Then you will access your own profile settings.</div>
</div>

<div id="content" class="wrapped">
	<div class="tabulate step" id="step1">
		<div class="number">1</div>
		<div class="stepped">
			<h1>Login to your account</h1>
			<p>Please <b>login to your XMPP account</b> in order to make change to your profile.</p>
			
			<form>
				<label>Address <input type="text" name="address" placeholder="yourself@jappix.com" disabled="disabled" required="required" /></label>
				<label>Password <input type="password" name="password" disabled="disabled" required="required" autocomplete="off" /></label>
				
				<input class="submit" type="submit" value="Login to my account" disabled="disabled" /><span class="status"></span>
			</form>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="tabulate step disabled" id="step2">
		<div class="number">2</div>
		<div class="stepped">
			<h1>Change your privacy settings</h1>
			<p>Now you can <b>make change</b> to your profile settings.</p>
			<p>Your privacy matters. Please <b>take them seriously</b>!</p>
			
			<div class="subtabulate">
				<p>My social channel is:</p>
				
				<label><input type="radio" name="microblog" value="public" disabled="disabled" /> Public</label>
				<label><input type="radio" name="microblog" value="private" disabled="disabled" /> Private</label>
				<label><input type="radio" name="microblog" value="none" checked="checked" disabled="disabled" /> Do not change</label>
			</div>
			
			<div class="subtabulate">
				<p>My current location is:</p>
				
				<label><input type="radio" name="geoloc" value="public" disabled="disabled" /> Visible</label>
				<label><input type="radio" name="geoloc" value="private" disabled="disabled" /> Hidden</label>
				<label><input type="radio" name="geoloc" value="none" checked="checked" disabled="disabled" /> Do not change</label>
			</div>
			
			<div class="subtabulate">
				<p>Allow search engines (<em>Google</em>, <em>Bing</em>) to access my profile:</p>
				
				<label><input type="radio" name="robots" value="1" disabled="disabled" /> Allow</label>
				<label><input type="radio" name="robots" value="0" disabled="disabled" /> Disallow</label>
				<label><input type="radio" name="robots" value="none" checked="checked" disabled="disabled" /> Do not change</label>
			</div>
			
			<div class="subtabulate">
				<p>Flag your profile as containing shocking or adult-only files (users will be warned):</p>
				
				<label><input type="radio" name="flagged" value="0" disabled="disabled" /> Appropriate for everyone</label>
				<label><input type="radio" name="flagged" value="1" disabled="disabled" /> Inappropriate for children</label>
				<label><input type="radio" name="flagged" value="none" checked="checked" disabled="disabled" /> Do not change</label>
			</div>
			
			<div class="subtabulate">
				<p>Force the profile update, instead of waiting next everyday update (takes some minutes):</p>
				<label><input type="checkbox" name="update" checked="checked" disabled="disabled" /> Update my profile now</label>
			</div>
			
			<div class="subtabulate removal">
				<p>Remove my profile (can be recreated later):</p>
				<label><input type="checkbox" name="remove" disabled="disabled" /> Remove my profile</label>
			</div>
			
			<p class="saveit"><b>Save your changes</b> below once you are ready!</p>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="tabulate step disabled" id="step3">
		<div class="number">3</div>
		<div class="stepped">
			<h1>Save your changes</h1>
			<p><b>Please confirm</b> that you want your new privacy settings to be saved.</p>
			<p>If you unchecked the “<em>Update my profile now</em>” setting, your new settings will be applied during the <b>next profile update</b> (everyday). You would be better <b>keep it enabled</b>.</p>
			
			<button disabled="disabled">Yes, I want my changes to be saved now!</button><span class="status"></span>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="tabulate step disabled" id="step4">
		<div class="number">4</div>
		<div class="stepped">
			<h1>Changes saved!</h1>
			<p>Your changes were <b>successfully saved</b>!.</p>
			<p>You can now go back to your profile. It should be updated in the <b>next minutes</b>.</p>
			<a class="button back">« Back to my profile</a>
		</div>
		
		<div class="clear"></div>
	</div>
</div>