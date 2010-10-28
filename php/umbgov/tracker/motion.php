<?php
// tracker/motion.php
chdir("../");
include("global.php");
include("includes/functions_bbcode.php");

$o = form_number($_GET['o']);

// Get motion info
$db->Query("SELECT * FROM umb_tracker_motions
			WHERE motion_id='$o'");
if($db->rows == 0)
	$umb->Message("Motion #$m does not exist");
$motion = $db->Fetch();
	
// Get votes
$db->Query("SELECT v.*, user_id, user_name, user_title FROM umb_tracker_votes v
			LEFT JOIN umb_users u ON v.vote_user=u.user_id
			WHERE vote_motion='$o'
			ORDER BY user_title ASC, user_name ASC");
while($row = $db->Fetch()){
	$votes[] = array(
		'uid' => $row['user_id'],
		'user' => $row['user_name'],
		'vote' => $labels['votes'][$row['vote_value']],
		'value' => strtolower($labels['votes'][$row['vote_value']]),
	);
}

$template->Load('tracker_votes_stub');
$template->AssignVars(array(
	'title' => $motion['motion_title'],
	'desc' => bbcode_parse($motion['motion_description'], PERMISSION_MANAGER),
	'vote_count' => count($votes),
	's_vote' => &$votes,
	'page_view' => !isset($_GET['ajax']),
));
$content = $template->Parse(true);

if(isset($_GET['ajax'])){
	echo $content;
} else {
	include('tracker/common.php');
}
?>