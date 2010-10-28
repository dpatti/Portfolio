<?php
// tracker/meeting.php
chdir("../");
include("global.php");
include("includes/functions_bbcode.php");

$m = form_number($_GET['m']);

// Get meeting info
$db->Query("SELECT * FROM umb_tracker_meetings
			WHERE meeting_id='$m'");
if($db->rows == 0)
	$umb->Message("Meeting #$m does not exist");
$meeting = $db->Fetch();
	
// Get motions
$db->Query("SELECT o.*, COUNT(DISTINCT vote_id) as `motion_votes` FROM umb_tracker_motions o
			LEFT JOIN umb_tracker_votes v ON o.motion_id=v.vote_motion
			WHERE motion_meeting='$m'
			GROUP BY motion_id
			ORDER BY motion_order ASC");
while($row = $db->Fetch()){
	$motions[] = array(
		'id' => $row['motion_id'],
		'form_id' => $row['motion_form_id'],
		'title' => $row['motion_title'],
		'desc' => bbcode_parse($row['motion_description'], PERMISSION_MANAGER),
		'count' => $row['motion_votes'],
		'passed' => $row['motion_passed'],
	);
}

$template->Load('tracker_motions_stub');
$template->AssignVars(array(
	'title' => $meeting['meeting_title'],
	'date' => $umb->FormatTime($meeting['meeting_timestamp'], 'meetingtime'),
	'motion_count' => count($motions),
	's_motion' => &$motions,
	'page_view' => !isset($_GET['ajax']),
));
$content = $template->Parse(true);

if(isset($_GET['ajax'])){
	echo $content;
} else {
	include('tracker/common.php');
}
?>