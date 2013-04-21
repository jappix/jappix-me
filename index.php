<?php

/*
 * Jappix Me - Your public profile, anywhere
 * Routing service
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */

?>

<?php

// Initialize
include('./php/config.php');
include('./php/functions.php');

// Disable PHP error reporting
if(getConfig('app', 'mode') != 'development') {
	ini_set('display_errors', 'off');
	ini_set('error_reporting', 0);
}

$user = $setting = null;
$url = $init_url = $_SERVER['REQUEST_URI'];

// Get app root path
$path_root_protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
$path_root_domain = $_SERVER['HTTP_HOST'];

// Redirect to a clean URL?
if(preg_match('/^\/\?u=(.+)/', $url))
	$url = preg_replace('/^\/\?u=(.+)/', '$1', $url);
if(preg_match('/&s=(.+)/', $url))
	$url = preg_replace('/&s=(.+)/', '/$1', $url);
if(preg_match('/%40/', $url))
	$url = preg_replace('/%40/', '@', $url);

if(preg_match('/(\/\/+)/', $url))
	$url = preg_replace('/(\/\/+)/', '/', $url);

if(preg_match('/^(\/+)$/', $url))
	$url = '/';
else if(preg_match('/(\/+)$/', $url))
	$url = preg_replace('/(\/+)$/', '', $url);

if($url != strtolower($url))
	$url = strtolower($url);

// Temporary 'vanaryon' to 'valerian' redirect
if(preg_match('/^\/vanaryon@(.+)/', $url))
	$url = preg_replace('/^\/vanaryon@(.+)/', '/valerian@$1', $url);

// Don't redirect if this is a folder
if(file_exists('.'.$url))
	$url = $init_url;

if($url != $init_url) {
	header('Status: 301 Moved Permanently', false, 301);
	header('Location: '.$url);
	
	exit;
}

// Username?
if(isset($_GET['u']) && !empty($_GET['u']))
	$user = strtolower(trim($_GET['u']));

// Setting?
if(isset($_GET['s']) && !empty($_GET['s']))
	$setting = strtolower(trim($_GET['s']));

// Page infos
$chapo = 'Your public profile. Anywhere.';
$title = 'Jappix Me - '.$chapo;

// Page type
if($user) {
	// New profile?
	if($user == 'new') {
		if($setting == 'bot') {
			if(isset($_POST['usr']) && isset($_POST['srv']) && isset($_POST['pwd'])) {
				// Store data in an array
				$data_store = array(
									'type' => 'new',
									'usr'  => trim(strtolower($_POST['usr'])),
									'srv'  => trim(strtolower($_POST['srv'])),
									'pwd'  => trim($_POST['pwd'])
								   );
				
				$store_user = $data_store['usr'].'@'.$data_store['srv'];
				
				// Pending profile
				if(file_exists('./pending/'.$store_user))
					exit('Profile request already sent!');
				
				// Full profile
				if(file_exists('./cache/'.$store_user))
					exit('Profile already exists!');
				
				file_put_contents('./pending/'.$store_user, serialize($data_store));
				
				exit('OK');
			}
			
			else
				exit('Invalid query!');
		}
		
		else if($setting == 'invite') {
			$xml_users = '<jappix xmlns="jappix:me:invite">';
			
			if(isset($_POST['list'])) {
				$list_users = unserialize($_POST['list']);
				
				foreach($list_users as $c_list_users) {
					$c_list_users = strtolower($c_list_users);
					$c_file_users = './invite/'.$c_list_users;
					
					if(!file_exists($c_file_users)) {
						$xml_users .= '<user>'.htmlspecialchars($c_list_users).'</user>';
						file_put_contents($c_file_users, '');
					}
				}
			}
			
			$xml_users .= '</jappix>';
			
			exit($xml_users);
		}
		
		$page = 'new';
		$title = 'Create your profile - Jappix Me';
	}
	
	// Privacy settings?
	else if($user == 'privacy') {
		// Bot query?
		if($setting == 'bot') {
			if(isset($_POST['usr']) && isset($_POST['srv']) && isset($_POST['pwd']) && isset($_POST['flagged']) && isset($_POST['search']) && isset($_POST['update']) && isset($_POST['remove'])) {
				// Store data in an array
				$data_store = array(
									'type'	 => 'privacy',
									'usr'    => trim(strtolower($_POST['usr'])),
									'srv'    => trim(strtolower($_POST['srv'])),
									'pwd'    => trim($_POST['pwd']),
									'search' => trim($_POST['search']),
									'flagged' => trim($_POST['flagged']),
									'update' => trim($_POST['update']),
									'remove' => trim($_POST['remove'])
								   );
				
				$store_user = $data_store['usr'].'@'.$data_store['srv'];
				
				// Any error?
				if(!file_exists('./cache/'.$store_user))
					exit('Profile does not exist!');
				if(file_exists('./pending/'.$store_user))
					exit('Changes already pending!');
				
				file_put_contents('./pending/'.$store_user, serialize($data_store));
				
				exit('OK');
			}
			
			else
				exit('Invalid query!');
			
			exit;
		}
		
		$page = 'privacy';
		$title = 'Manage your privacy settings - Jappix Me';
	}
	
	else if(($user == 'ads') || ($user == 'cache') || ($user == 'invite') || ($user == 'pending') || ($user == 'py')) {
		$page = '404';
		$reason_404 = 'locked';
	}
	
	// Valid XMPP address?
	else if(isAddress($user)) {
		// Sub-setting to be used
		$subsetting = substr(strstr($setting, '/'), 1);
		
		// Sub-page to be displayed
		$subpage = preg_replace('/^([^\/]+)([\/]+)?(.+)?$/', '$1', $setting);
		
		if(!$subpage)
			$subpage = 'channel';
		
		// Profile availability
		global $exists_user, $exists_profile, $exists_vcard, $exists_microblog, $exists_geoloc;
		
		$exists_user = file_exists('./cache/'.$user);
		$exists_profile = file_exists('./cache/'.$user.'/profile');
		$exists_vcard = file_exists('./cache/'.$user.'/raw/vcard');
		$exists_microblog = file_exists('./cache/'.$user.'/raw/microblog');
		$exists_geoloc = file_exists('./cache/'.$user.'/raw/geoloc');
		
		// Get the user's XMPP data
		$user_data = getXMPPData($user);
		$user_vcard = $user_data['vcard'];
		$user_fn = getUserName('fn', $user, $user_vcard);
		$user_microblog = $user_data['microblog'];
		$user_geoloc = $user_data['geoloc'];
		$user_pictures = $user_data['pictures'];
		$user_flagged = $user_data['flagged'];
		$user_search = $user_data['search'];
		
		// User avatar requested?
		if($subpage == 'avatar') {
			include('./php/avatar.php');
			exit;
		}
		
		// If user does not exist, abort
		if(!$exists_user || (!$exists_profile && !$exists_vcard && !$exists_microblog && !$exists_geoloc)) {
			$page = '404';
			
			// Profile being created?
			if(file_exists('./pending/'.$user) || $exists_user)
				$reason_404 = 'pending';
			else
				$reason_404 = 'user';
		}
		
		else {
			// User feed requested?
			if($subpage == 'feed') {
				include('./php/feed.php');
				exit;
			}
			
			// User export item requested?
			if(($subpage == 'export') && $subsetting && preg_match('/^(location|profile)((\/+)(.+)?)?$/', $subsetting)) {
				include('./php/user.export.php');
				exit;
			}
			
			// User channel requested?
			if(($subpage == 'channel') && $subsetting && preg_match('/^[0-9]+$/', $subsetting)) {
				$channel_start = intval($subsetting) * 10;
				$channel_stop = $channel_start + 10;
				$channel_start++;
				
				genChannel($user, $user_microblog, $channel_start, $channel_stop);
				
				exit;
			}
			
			// User pictures requested?
			if(($subpage == 'pictures') && preg_match('/^[0-9]+$/', $subsetting)) {
				$pictures_start = intval($subsetting) * 60;
				$pictures_stop = $pictures_start + 60;
				$pictures_start++;
				
				genPictures($user, $user_pictures, $pictures_start, $pictures_stop);
				
				exit;
			}
			
			$page = 'user';
			$title = $user.' - Jappix Me';
			
			// Redirect if we don't know that page (or if channel page asked)
			if(!file_exists('./php/user.'.$subpage.'.php') || ($url == '/'.$user.'/channel')) {
				header('Status: 301 Moved Permanently', false, 301);
				header('Location: /'.$user);
				
				exit;
			}
			
			// Non-existent channel entry
			if(($subpage == 'channel') && $subsetting && !isset($user_microblog[$subsetting])) {
				$page = '404';
				$reason_404 = 'channel';
			}
			
			// Non-existent picture
			else if(($subpage == 'pictures') && $subsetting && !isset($user_pictures[$subsetting])) {
				$page = '404';
				$reason_404 = 'pix';
			}
			
			// Profile is okay
			else {
				// Global user information
				$user_nick = getUserName('nick', $user, $user_vcard);
				$user_bday_stamp = getUserBirthdayStamp($user_vcard);
				$user_bday = getUserBirthdayDate($user_bday_stamp);
				$user_age = getUserAge($user_bday_stamp);
				$user_location = getUserLocation($user, $user_vcard);
				$user_site = getUserSite($user_vcard);
				
				// Sub-page title
				if($subpage == 'channel') {
					if($subsetting) {
						$ent_content = '';
						
						if(isset($user_microblog[$subsetting]['item'])) {
							$ent_item = $user_microblog[$subsetting]['item'][0]['sub'];
							
							if(isset($ent_item['entry'])) {
								$ent_entry = $ent_item['entry'][0]['sub'];
								
								if(isset($ent_entry['body']))
									$ent_content = trim($ent_entry['body'][0]['sub']);
								if(isset($ent_entry['content']))
									$ent_content = trim($ent_entry['content'][0]['sub']);
							}
						}
						
						if(!$ent_content)
							$ent_content = 'unknown content';
						
						$ent_title = truncate($ent_content, 50, '…');
						
						$title = htmlspecialchars($user_fn).' ('.htmlspecialchars($ent_title).') - Jappix Me';
					}
					
					else
						$title = htmlspecialchars($user_fn).' - Jappix Me';
				}
				
				else if(($subpage == 'pictures') && $subsetting) {
					$pix_name = $user_pictures[$subsetting]['title'];
					
					if(!$pix_name)
						$pix_name = 'unknown name';
					
					$title = htmlspecialchars($user_fn).' ('.ucfirst($subpage).', '.htmlspecialchars(ucfirst($pix_name)).') - Jappix Me';
				}
				
				else
					$title = htmlspecialchars($user_fn).' ('.ucfirst($subpage).') - Jappix Me';
			}
		}
	}
	
	// Error!
	else {
		$page = '404';
		$reason_404 = 'page';
	}
}

else if($setting == '404') {
	$page = '404';
	$reason_404 = 'file';
}

else
	$page = 'home';

// 404 header
if($page == '404') {
	if($reason_404 == 'pending')
		$title = 'Please wait (a little bit) - Jappix Me';
	else
		$title = 'That\'s an error - Jappix Me';
	
	header('Status: 404 Not Found', false, 404);
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $title; ?></title>
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="stylesheet" href="/css/<?php echo $page; ?>.css" type="text/css" media="all" />
	<!--[if lte IE 7]><link rel="stylesheet" href="/css/ie7.css" type="text/css" media="all" /><![endif]-->
	<!--[if lte IE 6]><link rel="stylesheet" href="/css/ie6.css" type="text/css" media="all" /><![endif]-->
	<?php if($page == 'user') { ?>
	<link rel="alternate" type="application/atom+xml" href="/<?php echo htmlspecialchars($user); ?>/feed" />
	<?php } if(($page == 'user') || ($page == 'new') || ($page == 'privacy')) { ?>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
	<script type="text/javascript" src="https://static.jappix.com/php/get.php?l=en&amp;t=js&amp;f=common.js~browser-detect.js~constants.js~datastore.js~jsjac.js~jquery.placeholder.js~date.js"></script>
	<script type="text/javascript" src="/js/<?php echo $page; ?>.js"></script>
	<?php } if(($page == 'user') && (($user_search == '0') || ($user_flagged == '1'))) { ?>
	<meta name="robots" content="noindex, nofollow" />
	<?php } ?>
</head>

<body>
	<?php if(($page == 'user') && ($user_flagged == '1')) { ?>
	<div class="flagged">
		<span class="flag">
			<span class="wrapped">This profile has been reported to be <b>inappropriate for children</b>. It may contain <b>shocking or adult-only</b> files<noscript> / <b>JavaScript is disabled</b></noscript>.</span>
		</span>
		
		<span class="marginizer">Flagged margin, hidden ;-)</span>
	</div>
	<?php } else if(($page == 'home') || ($page == 'user') || ($page == 'new') || ($page == 'privacy')) { ?>
	<div id="description">Jappix Me is a free tool to create your profile. Share updates, pictures, places and resume yourself. Use a single avatar everywhere on the Web. All you need in a single place.</div>
	<noscript>
		<span class="noscript">
			<span class="wrapped">Woops! It seems <b>your browser cannot handle our beautiful JavaScript</b>. Jappix Me requires it to work correctly, you'd better <b>enable it</b>.</span>
		</span>
		
		<span class="marginizer">No script margin, hidden ;-)</span>
	</noscript>
	<?php } ?>
	
	<?php include('./php/'.$page.'.php'); ?>
	
	<?php if(($page == 'home') || ($page == 'user') || ($page == 'new') || ($page == 'privacy')) { ?>
	<div id="foot" class="wrapped">
		<span><b><a href="https://project.jappix.com/contact">Contact</a></b> - <b><a href="https://legal.jappix.com/">Legal</a></b> - © 2011-<?php echo(date('Y')); ?> <a href="http://frenchtouch.pro/">FrenchTouch Web Agency</a></span>
		<?php if($page == 'user') {
			$google_link = 'https://www.google.com/search?q='.rawurlencode('site:'.$path_root_domain.'/'.$user);
			
			if(($user_search == '0') || ($user_flagged == '1')) {
				$class_link = 'locked';
				
				if($user_flagged == '1')
					$title_link = 'Your profile has been reported as inapropriate for children. For safety reasons, search engines cannot index it. Click here to check what Google knows about you »';
				else
					$title_link = 'Search engines cannot access your profile. Only human users can view it, but they cannot find your profile easily. Click here to check what Google knows about you »';
			}
			
			else {
				$class_link = 'unlocked';
				$title_link = 'Search engines can access your profile. People may find your profile easily. Click here to check what Google knows about you »';
			}
			
			echo '<a class="locker '.htmlspecialchars($class_link).'" title="'.htmlspecialchars($title_link).'" href="'.htmlspecialchars($google_link).'" target="_blank"></a>';
			
			if($cache_date[2] == 0) { ?>
			<span class="right">Profile is being updated by our bot.</span>
			<?php } else if($cache_date[2] <= 24) { ?>
			<span class="right">Profile updated <?php echo $cache_date[0]; ?> ago. Next update in <?php echo $cache_date[1]; ?>.</span>
			<?php } else { ?>
			<span class="right">Profile update error. Our bot will retry <abbr title="As Soon As Possible">ASAP</abbr>.</span>
			<?php }
		} else { ?>
		<span class="social">
			<a class="facebook" href="http://www.facebook.com/jappix" title="Follow us on Facebook!"></a>
			<a class="twitter" href="http://twitter.com/jappixorg" title="Follow us on Twitter!"></a>
		</span>
		
		<span class="right"><a class="logo" href="https://jappix.com/">Jappix</a> - <a href="https://me.jappix.com/">Jappix Me</a> - <a href="http://jappix.pro/">Jappix Pro</a> - <a href="http://jappix.org/">Jappix Download</a> - <a href="http://jappix.net/">Jappix Network</a></span>
		<?php } ?>
		<div class="clear"></div>
	</div>
	<?php } ?>
	
	<!-- BEGIN CONFIG -->
	<div id="config">
		<input type="hidden" name="xmpp-domain" value="<?php echo getConfig('xmpp', 'domain'); ?>" />
		<input type="hidden" name="xmpp-bosh" value="<?php echo getConfig('xmpp', 'bosh'); ?>" />
		<input type="hidden" name="app-url" value="<?php echo $path_root_protocol.'://'.$path_root_domain.'/'; ?>" />
	</div>
	<!-- END CONFIG -->

	<?php if((getConfig('analytics', 'enable') == 'true') && getConfig('analytics', 'server') && getConfig('analytics', 'id')) { ?>
	<!-- BEGIN ANALYTICS -->
		<script type="text/javascript">
			var pkBaseURL = (("https:" == document.location.protocol) ? "https://<?php echo getConfig('analytics', 'server'); ?>/" : "http://<?php echo getConfig('analytics', 'server'); ?>/");
			
			document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		
		<script type="text/javascript">
			try {
				var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", <?php echo getConfig('analytics', 'id'); ?>);
				
				piwikTracker.trackPageView();
				piwikTracker.enableLinkTracking();
			} catch(err) {}
		</script>
	<!-- END ANALYTICS -->
	<?php } ?>
</body>

</html>