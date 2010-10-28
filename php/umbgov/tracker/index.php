<?php
// tracker/index.php
chdir("../");
include("global.php");
include("includes/functions_fiscal.php");

// List meetings
$db->Query("SELECT m.*, COUNT(DISTINCT motion_id) as `meeting_motions` FROM umb_tracker_meetings m
			LEFT JOIN umb_tracker_motions o ON m.meeting_id=o.motion_meeting
			GROUP BY meeting_id
			ORDER BY meeting_timestamp DESC");	
while($row = $db->Fetch()){
	$meetings[get_fiscal($row['meeting_timestamp'])][] = array(
		'id' => $row['meeting_id'],
		'date' => $umb->FormatTime($row['meeting_timestamp'], 'meetingtime'),
		'title' => $row['meeting_title'],
		'count' => $row['meeting_motions']+0,
	);
}
// expand meetings to double structure format
krsort($meetings);
foreach($meetings as $year=>$meeting){
	$years[] = array(
		'year' => "Fiscal Year $year",
		's_meeting' => array(),
	);
	foreach($meeting as $m){
		$years[count($years)-1]['s_meeting'][] = $m;
	}
}

$template->Load('tracker_meetings_stub');
$template->AssignVars(array(
	's_year' => &$years,
	'meeting_count' => count($meetings),
));
$content = $template->Parse(true);

include('tracker/common.php');
?>