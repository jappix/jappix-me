<?php

/*
 * Jappix Me - Your public profile, anywhere
 * User channel page
 * 
 * License: AGPL
 * Author: Valérian Saliou
 */


// Parse URL settings
$entry_id = $subsetting;

// Single channel entry requested?
if($entry_id) {
	// Get entry stamp
	if(isset($user_microblog[$entry_id]['entry']) && isset($user_microblog[$entry_id]['entry'][0]['sub']['published'])) {
		$entry_published = $user_microblog[$entry_id]['entry'][0]['sub']['published'][0]['sub'];
		$entry_stamp = strtotime($entry_published);
	}
	
	else
		$entry_stamp = 0;
	
	// Get previous & next entry IDs
	$entry_id_prev = $entry_id_next = '';
	$surround_ids = array_keys($user_microblog);
	$id_index = array_search($entry_id, $surround_ids);
	
	if(($id_index > 0) && isset($surround_ids[$id_index - 1]))
		$entry_id_prev = $surround_ids[$id_index - 1];
	if(isset($surround_ids[$id_index + 1]))
		$entry_id_next = $surround_ids[$id_index + 1];
	
	if($entry_stamp) { ?>
	<div class="info">You are viewing a channel entry posted on <?php echo date('F j, Y', $entry_stamp); ?>.</div>
	<?php } else { ?>
	<div class="info">You are viewing a single channel entry.</div>
	<?php }
	
	$chan_arr = genChannel($user, $user_microblog, 1, 1, $entry_id);
	
	if($entry_id_prev)
		echo '<a class="navigation previous" href="/'.htmlspecialchars($user).'/channel/'.htmlspecialchars($entry_id_prev).'">« Previous entry</a>';
	if($entry_id_next)
		echo '<a class="navigation next" href="/'.htmlspecialchars($user).'/channel/'.htmlspecialchars($entry_id_next).'">Next entry »</a>';
	
	genComments($user, $user_fn, $chan_arr['post'], $chan_arr['comments']);
	
	?>
	<div class="clear"></div>
<?php }

// Full channel requested?
else {
	if(count($user_microblog)) { ?>
		<div class="info">This is the public social channel of <em><?php echo htmlspecialchars($user_fn); ?></em>, a kind of <em>lifestream</em>.</div>
	<?php } else { ?>
		<div class="info"><em><?php echo htmlspecialchars($user_fn); ?></em> did not post any public social channel entry yet!</div>
		<a class="infomsg" href="https://jappix.com/" target="_blank"><em><?php echo htmlspecialchars($user_fn); ?></em> should use Jappix to post entries »</a>
	<?php }
	
	genChannel($user, $user_microblog, 1, 10);
	
	if(count($user_microblog) > 10) { ?>
		<a class="loadmore" href="#">Load more</a>
		<img class="loading hidden" src="/img/loading.gif" alt="" data-user="<?php echo htmlspecialchars($user); ?>" data-start="1" />
	<?php }
} ?>