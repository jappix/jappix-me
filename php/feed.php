<?php

/*
 * Jappix Me - Your public profile, anywhere
 * ATOM feed service
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */

?>

<?php

// User social feed

// ATOM header
header('Content-type: application/atom+xml; charset=utf-8');

// ATOM content
echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<feed xmlns="http://www.w3.org/2005/Atom">';
	echo '<title>'.htmlspecialchars($user_fn).'\'s social feed</title>';
	echo '<link href="'.$path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user).'"/>';
	echo '<author>';
		echo '<name>'.htmlspecialchars($user_fn).'</name>';
		echo '<email>'.htmlspecialchars($user).'</email>';
	echo '</author>';
	echo '<id>urn:uuid:'.sha1($user).'</id>';
	
	if(count($user_microblog)) {
		$k = 1;
		
		foreach($user_microblog as $id => $sub) {
			if($k > 10)
				break;
			
			$content = $published = $updated = '';
			
			if(isset($sub['item'])) {
				$item = $sub['item'][0]['sub'];
				
				if(isset($item['entry'])) {
					$entry = $item['entry'][0]['sub'];
					
					// Extract data
					if(isset($entry['body']))
						$content = trim($entry['body'][0]['sub']);
					if(isset($entry['content']))
						$content = trim($entry['content'][0]['sub']);
					if(isset($entry['published']))
						$published = trim($entry['published'][0]['sub']);
					if(isset($entry['updated']))
						$updated = trim($entry['updated'][0]['sub']);
					
					if(($k == 1) && $updated && $content)
						echo '<updated>'.htmlspecialchars($updated).'</updated>';
				}
			}
			
			if($content) {
				echo '<entry>';
					echo '<title>'.htmlspecialchars(truncate($content, 50, '…')).'</title>';
					echo '<link href="'.$path_root_protocol.'://'.$path_root_domain.'/'.htmlspecialchars($user).'/channel/'.htmlspecialchars($id).'"/>';
					echo '<id>'.htmlspecialchars($id).'</id>';
					
					if($published)
						echo '<published>'.htmlspecialchars($published).'</published>';
					if($updated)
						echo '<updated>'.htmlspecialchars($updated).'</updated>';
					
					echo '<summary>'.htmlspecialchars($content).'</summary>';
				echo '</entry>';
			}
			
			$k++;
		}
	}

echo '</feed>';

?>