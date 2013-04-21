<?php

/*
 * Jappix Me - Your public profile, anywhere
 * User pictures page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */

?>

<?php

// Parse URL settings
$pix_id = $subsetting;

// Single picture requested?
if($pix_id) {
	$current_pix = $user_pictures[$pix_id];
	$pix_href = $current_pix['href'];
	$pix_prev = $current_pix['previous'];
	$pix_next = $current_pix['next'];
	$pix_pthumb = $current_pix['pthumb'];
	$pix_nthumb = $current_pix['nthumb'];
	$pix_title = $current_pix['title'];
	$pix_post = $current_pix['post'];
	$pix_comments = $current_pix['comments'];
	$pix_stamp = strtotime($current_pix['stamp']);
	
	echo '<div class="info">You are viewing <em>'.htmlspecialchars($pix_title).'</em>, added on '.date('F j, Y', $pix_stamp).'.</div>';
	echo '<div class="middle"><a class="medium" href="'.htmlspecialchars($pix_href).'" target="_blank" title="'.htmlspecialchars($pix_title).'"><img src="'.htmlspecialchars($pix_href).'" alt="" /></a></div>';
	
	if($pix_prev && $pix_pthumb)
		echo '<a class="navigation thumb previous" href="/'.htmlspecialchars($user).'/pictures/'.htmlspecialchars($pix_prev).'#content" title="« Previous picture"><img src="'.htmlspecialchars($pix_pthumb).'" alt="" /></a>';
	if($pix_next && $pix_nthumb)
		echo '<a class="navigation thumb next" href="/'.htmlspecialchars($user).'/pictures/'.htmlspecialchars($pix_next).'#content" title="Next picture »"><img src="'.htmlspecialchars($pix_nthumb).'" alt="" /></a>';
	
	genComments($user, $user_fn, $pix_post, $pix_comments);
}

// Timeline requested?
else {
	if(isset($user_pictures) && count($user_pictures)) { ?>
	<div class="info">These are the pictures <em><?php echo htmlspecialchars($user_fn); ?></em> shared across the time.</div>
	<?php } else { ?>
	<div class="info"><em><?php echo htmlspecialchars($user_fn); ?></em> did not share any picture in the social channel yet!</div>
	<a class="infomsg" href="https://jappix.com/" target="_blank"><em><?php echo htmlspecialchars($user_fn); ?></em> should use Jappix to share pictures »</a>
	<?php }
	
	genPictures($user, $user_pictures, 1, 60);
	
	if(isset($user_pictures) && (count($user_pictures) > 60)) { ?>
		<a class="loadmore" href="#">Load more</a>
		<img class="loading hidden" src="/img/loading.gif" alt="" data-user="<?php echo htmlspecialchars($user); ?>" data-start="1" />
<?php }
}

?>