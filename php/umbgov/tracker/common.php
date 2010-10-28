<?php
// tracker/common.php - this is called indirectly to finalize page

$template->Load('tracker_index');
$template->AssignVars(array(
	's_css' => array(
		array('href'=>'/css/tracker.css'),
	),
	's_js' => array(
		array('href'=>'/js/tracker.js'),
	),
	'content' => $content,
));
$template->Parse();


?>