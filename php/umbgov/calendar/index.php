<?php
chdir("../");
include("global.php");
include("includes/functions_calendar.php");

$month = form_number($_GET['m'], date('n'));
$year = form_number($_GET['y'], date('Y'));
$date = form_number($_GET['d'], 1000); //weird value so nothing gets highlighted
$today = date('j');
$thisYear = date('Y');

//maxing zero to these because i somehow had a user get an SQL error due to an empty variable
$anchor = max(mktime(0, 0, 0, $month, 1, $year),0);		
$leftBound = max(mktime(0, 0, 0, $month, -7, $year),0);
$rightBound = max(mktime(0, 0, 0, $month+1, 7, $year),0);

$calendar = array(
	's_css' => array(
		array('href'=>'/css/calendar.css'),
	),
	's_js' => array(
		array('href'=>'/js/calendar.js'),
	),
	'month' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
	's_calendar_row' => array(),
	'prev' => str_replace("&y=$thisYear", '', date('\?\m\=n\&\y\=Y', $leftBound)),
	'next' => str_replace("&y=$thisYear", '', date('\?\m\=n\&\y\=Y', $rightBound)),
	'prev_month' => date('M', $leftBound),
	'next_month' => date('M', $rightBound),
);

if($umb->user->PermissionLevel(PERMISSION_MANAGER, MANAGER_CALENDAR)){
	$calendar['s_js'][] = array('href'=>'/js/admin/calendar.js');
	$calendar['s_css'][] = array('href'=>'/css/form.css');
}

//get events
$db->Query(("SELECT *
			FROM umb_calendar_events
			WHERE calendar_event_timestamp>=$leftBound
			AND calendar_event_timestamp<=$rightBound"));
$events = array();
while($row = $db->Fetch()){
	$key = date('Y-n-j', $row['calendar_event_timestamp']);
	//building to-be template structs here
	$events[$key][] = array(
		'id' => $row['calendar_event_id'],
		'sched' => calendar_format_event(strip_prefix($row)),
	);
	//TODO: sort/limit events
}

//build calendar
$now = getdate($anchor);
$last = getdate(mktime(0, 0, 0, $month, 0, $year));	// last month's last day
$last = $last['mday'];
$end = getdate(mktime(0, 0, 0, $month+1, 0, $year)); // this month's last day
$end = $end['mday'];
$wday = $now['wday'];
$cur = 1-$wday;

while(checkdate($month, ($cur<1)?1:$cur, $year)){
	//row
	$row = array(
		's_calendar_cell' => array(),
	);
	for($i=0;$i<7;$i++){
		//cells
		$classes = '';
		if ($cur == $date)
			$classes .= ' highlight';
		if ($cur == $today && $year == date('Y') && $month == date('n'))
			$classes .= ' today';
		if ($cur<1 || $cur>$end)
			$classes .= ' outer';
			
		//check db
		$tKey = ($cur<1) ? $leftBound :
				($cur>$end) ? $rightBound :
				$anchor;
		$yKey = date('Y', $tKey);
		$mKey = date('n', $tKey);
		$dKey = ($cur<1) ? $last+$cur : (($cur>$end) ? $cur-$end : $cur);
		$key = "$yKey-$mKey-$dKey";
		
		$row['s_calendar_cell'][] = array(
			'cell' => $classes,
			'cell_m' => $mKey,
			'cell_y' => $yKey,
			'date' => $dKey,
			's_sched' => &$events[$key],
			'url' => (count($events[$key])>0) ? $events[$key][0]['id'] : false,
			/*'url' => (count($events[$key])>0) ? str_replace("m=$month&", '', str_replace("y=$year&", '', "?m=$mKey&y=$yKey&d=$cur")) : NULL,*/
		);	
		$cur++;
	}
	$calendar['s_calendar_row'][] = $row;
}

//build mini-box
$months = array();
for($i=1;$i<=12;$i++){
	$months[] = array(
		'n' => $i,
		'month' => date('F', mktime(0, 0, 0, $i, 1, 0)),
		'sel' => $i == $month,
	);
}
$years = array();
$thisYear = date('Y');
for($i=$thisYear-4;$i<=$thisYear+4;$i++){
	$years[] = array(
		'year' => $i,
		'sel' => $i == $year,
	);
}

$calendar['s_sel_month'] = &$months;
$calendar['s_sel_year'] = &$years;

$template->Load('calendar_index');
$template->AssignVars($calendar);
$template->Parse();	
?>