<?php
chdir("../");
include("global.php");
include("includes/functions_calendar.php");

$event = form_number($_GET['e']);

$db->Query("SELECT * FROM umb_calendar_events
			WHERE calendar_event_id='$event'");
if($db->rows == 0)
	$umb->Message("Calendar Event #$event does not exist");
$details = $db->Fetch();

if(!isset($_GET['ajax'])){
	// get other
	$time = $details['calendar_event_timestamp'];
	$basetime = mktime(0, 0, 0, date('n', $time), date('j', $time), date('Y', $time));
	$endtime = $basetime+60*60*24-1;
	$db->Query("SELECT * FROM umb_calendar_events
				WHERE calendar_event_timestamp>$basetime
				AND calendar_event_timestamp<$endtime");
	while($row = $db->Fetch()){
		$other[] = array(
			'title' => $row['calendar_event_title'],
			'time' => $row['calendar_event_time'] ? date('(g:i a)', $row['calendar_event_timestamp']) : false,
			'id' => $row['calendar_event_id'],
		);
	}

	// get next, prev
	$db->Query("SELECT * FROM umb_calendar_events
				WHERE calendar_event_timestamp<$basetime
				ORDER BY calendar_event_timestamp DESC
				LIMIT 1");
	$prev = $db->Fetch();

	$db->Query("SELECT * FROM umb_calendar_events
				WHERE calendar_event_timestamp>$endtime
				ORDER BY calendar_event_timestamp ASC
				LIMIT 1");
	$next = $db->Fetch();
}

$template->Load('calendar_view_stub');
$template->AssignVars(array(
	'title' => $details['calendar_event_title'],
	'date' => $umb->FormatTime($details['calendar_event_timestamp'], 'calendartime'),
	'time' => $details['calendar_event_time'] ? date('g:i a', $details['calendar_event_timestamp']) : false,
	'id' => $details['calendar_event_id'],
	'description' => bbcode_parse($details['calendar_event_description'], PERMISSION_MANAGER),
	'ajax' => isset($_GET['ajax']),
));
$view_stub = $template->Parse(true);

if(isset($_GET['ajax'])){
	echo $view_stub;
	exit;
}

$template->Load('calendar_view');
$template->AssignVars(array(
	's_css' => array(
		array('href'=>'/css/calendar.css'),
	),
	'id' => $details['calendar_event_id'],
	'prev' => $prev['calendar_event_title'],
	'prev_id' => $prev['calendar_event_id'],
	'prev_date' => $umb->FormatTime($prev['calendar_event_timestamp'], 'compacttime'),
	'next' => $next['calendar_event_title'],
	'next_id' => $next['calendar_event_id'],
	'next_date' => $umb->FormatTime($next['calendar_event_timestamp'], 'compacttime'),
	'cur_param' => preg_replace('/\?m='.date('n').'$/', '', preg_replace('/&y='.date('Y').'/', '', sprintf("?m=%d&y=%d", date('n', $time), date('Y', $time)))),
	'view_stub' => $view_stub,
	's_other' => &$other,
));
$template->Parse();	
?>