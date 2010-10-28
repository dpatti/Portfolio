<?php
// tracker/meeting.php
chdir("../");
include("global.php");

$u = form_number($_GET['u']);

// Get user info
$db->Query("SELECT * FROM umb_users
			WHERE user_id='$u'");
if($db->rows == 0)
	$umb->Message("User #$m does not exist");
$user = $db->Fetch();

// Get votes
$db->Query("SELECT m.*, o.*, v.* FROM umb_tracker_votes v
			LEFT JOIN umb_users u ON v.vote_user=u.user_id
			LEFT JOIN umb_tracker_motions o ON v.vote_motion=o.motion_id
			LEFT JOIN umb_tracker_meetings m ON m.meeting_id=o.motion_meeting
			WHERE user_id='$u'
			ORDER BY motion_title ASC");
while($row = $db->Fetch()){
	$m = $row['meeting_id'];
	$data[$m]['mid'] = $row['meeting_id'];
	$data[$m]['date'] = $umb->FormatTime($row['meeting_timestamp'], 'meetingtime');
	$data[$m]['s_motion'][] = array(
		'oid' => $row['motion_id'],
		'motion' => $row['motion_title'],
		'vote' => $labels['votes'][$row['vote_value']],
		'value' => strtolower($labels['votes'][$row['vote_value']]),
	);
}
//conver associative $data into indexed template structure
$s_meeting = array();
foreach($data as $mid=>$mdata){
	$s_meeting[] = $mdata;
}

$template->Load('tracker_user_stub');
$template->AssignVars(array(
	'user' => $user['user_name'],
	's_meeting' => &$s_meeting,
	'count' => count($s_meeting),
));
$content = $template->Parse(true);

if(isset($_GET['ajax'])){
	echo $content;
} else {
	include('tracker/common.php');
}
?>