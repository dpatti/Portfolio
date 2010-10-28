<?php
chdir("../");
include("global.php");
include("includes/definitions_seoc_maintenance.php");
include("includes/definitions_seoc_event.php");
include("includes/functions_fiscal.php");

$action = form_string($_GET['action']);
$type = form_string($_GET['type']);
$id = form_number($_GET['id']);
$user = $umb->user->id;

if(!$umb->user->IsLiaison()){
	$umb->Error("Liaison Action Center", ERROR_PERMISSIONS);
}

$template->AssignVars(array(
	's_css' => array(
		array('href'=>'/css/liaison.css'),
	),
	's_js' => array(
		array('href'=>'/js/liaison.js'),
	),
));


if($action == ""){
	//get all of my clubs
	$viewAll = $umb->user->PermissionLevel(PERMISSION_MANAGER, MANAGE_RSO);
	$thisYear = get_fiscal();
	/*$db->Query("SELECT c.*, COUNT(IF(ISNULL(amendment_status) OR amendment_status != 2, 1, NULL)) as 'attn' FROM umb_seoc_clubs c
					LEFT JOIN ((SELECT event_id as 'budget_id', event_club as 'budget_club',  'event' as 'budget_type' FROM umb_seoc_event)
								UNION 
								(SELECT maintenance_id as 'budget_id', maintenance_club as 'budget_club', 'maintenance' as 'budget_type' FROM umb_seoc_maintenance)) b
						ON budget_club=club_id
					LEFT JOIN umb_seoc_amendment a ON ((amendment_type='event' AND budget_type='event' AND amendment_budget=budget_id) OR (amendment_type='maintenance' AND budget_type='maintenance' AND amendment_budget=budget_id))
					WHERE club_year='$thisYear'" . ($viewAll ? "" : " AND club_liaison='$user'") . "
					GROUP BY club_id");*/ //turns out i didn't need this whole query, but it was too cool to delete.
	//TODO: do not show if zero budgets
	$db->Query("SELECT * FROM umb_seoc_clubs
				LEFT JOIN umb_seoc_maintenance ON maintenance_club=club_id
				WHERE club_year='$thisYear'" . ($viewAll ? "" : " AND club_liaison='$user'") . "
				GROUP BY club_id HAVING COUNT(maintenance_id)>0
				ORDER BY club_name ASC");
	while($row = $db->Fetch()){
		/*$temp = array();
		foreach($row as $key=>$value){
			if(!is_numeric($key))
				$temp[$key] = $value;
		}*/
		$clubs[] = $row;//$temp;
	}
	
	//get budget info for each club
	foreach($clubs as $k=>$data){
		$clubid = $data['club_id'];
		
		$db->Query("SELECT * FROM umb_seoc_maintenance 
					LEFT JOIN umb_seoc_amendment a ON (amendment_type='maintenance' AND maintenance_id=amendment_budget)
					WHERE maintenance_club='$clubid'
					ORDER BY maintenance_submitted ASC");
		$total = 0;
		$amend = 0;
		$attn  = 0;
		$budgets = array();
		while($row = $db->Fetch()){
			$budgets[] = array(
				'type' 	=> 'maintenance',
				'name' 	=> 'Maintenance Budget',
				'id'	=> $row['maintenance_id'],
				'status'	=> $labels['club_status'][0+$row['amendment_status']][0],
				'status_c'	=> $labels['club_status'][0+$row['amendment_status']][1],
				'time' 	=> $umb->FormatTime($row['maintenance_submitted'], 'compacttime'),
				'ip'	=> long2ip($row['maintenance_ip']),
				'total'	=> sprintf("$%.2f", $row['maintenance_total_amt']),
				'amended'	=> $row['amendment_total_amt']>0 ? sprintf("$%.2f", $row['amendment_total_amt']) : '---',
				'event' => '',
				'do_link' => $row['amendment_status'] != 2,
			);
			if ($row['amendment_status'] != 2 && $row['amendment_status'] != 1)
				$attn++;
			$total += $row['maintenance_total_amt'];
			$amend += $row['amendment_total_amt'];
		}
		$db->Query("SELECT * FROM umb_seoc_event
					LEFT JOIN umb_seoc_amendment a ON (amendment_type='event' AND event_id=amendment_budget)
					WHERE event_club='$clubid' 
					ORDER BY event_submitted ASC");
		while($row = $db->Fetch()){
		//die(print_r($row));
			$budgets[] = array(
				'type' 	=> 'event',
				'name'	=> 'Event: '.$row['event_title'],
				'id'	=> $row['event_id'],
				'status'	=> $labels['club_status'][0+$row['amendment_status']][0],
				'status_c'	=> $labels['club_status'][0+$row['amendment_status']][1],
				'time' 	=> $umb->FormatTime($row['event_submitted'], 'compacttime'),
				'ip'	=> long2ip($row['event_ip']),
				'total'	=> sprintf("$%.2f", $row['event_total_amt']),
				'amended'	=> $row['amendment_total_amt']>0 ? sprintf("$%.2f", $row['amendment_total_amt']) : '---',
				'event' => $umb->FormatTime($row['event_date'], 'compacttime'),
				'do_link' => $row['amendment_status'] != 2,
			);
			//if($row['event_title'] == 'lolfwuat') die(print_r($row));
			if ($row['amendment_status'] != 2 && $row['amendment_status'] != 1)
				$attn++;
			$total += $row['event_total_amt'];
			$amend += $row['amendment_total_amt'];
		}		
		
		$clubs[$k]['s_budget_list'] = $budgets;
		$clubs[$k]['total'] = sprintf("$%.2f", $total);
		$clubs[$k]['amend'] = $amend>0 ? sprintf("$%.2f", $amend) : '---';
		$clubs[$k]['attn'] = $attn;
	}
	/*$clubs = array(
		array(
			'club_name' => "A",
			'club_id' => 0,
		),
		array(
			'club_name' => "B",
			'club_id' => 1,
		),
	);
	die(print_r($clubs));*/
	$template->Load("liaison_index");
	$template->AssignVars(array(
		's_club_listing' 	=> $clubs,
		's_club_details'	=> $clubs,
	));
	$template->Parse();	
} else if ($action == "edit") {
	if($type != "maintenance" && $type != "event"){
		$umb->Error("Invalid type specified");
	}
	$db->Query("SELECT * FROM umb_seoc_$type
				LEFT JOIN umb_seoc_amendment ON (amendment_type='$type' AND amendment_budget=${type}_id)
				LEFT JOIN umb_seoc_clubs ON ${type}_club=club_id
				WHERE ${type}_id='$id'");
	$row = $db->Fetch();
	
	//die(print_r($row));
	
	//check that budget exists
	if($db->rows == 0)
		$umb->Error("Error retrieving budget information.");
	
	//die("!" . $umb->user->id . " " . ($umb->user->PermissionLevel(PERMISSION_MANAGER, MANAGE_RSO)?"1":"0") . "!");
	//check that this is your club
	if($row['club_liaison'] != $umb->user->id && !$umb->user->PermissionLevel(PERMISSION_MANAGER, MANAGE_RSO))
		$umb->Error("You cannot amend budgets from other clubs.");
	
	//check that this isn't finalized
	if($row["amendment_status"] == 2)
		$umb->Error("You cannot edit finalized budgets.");
	
	$resources = ($type == "event") ? $event_resources : $maintenance_resources;
	///die(print_r($row));
	//building three arrays:
	// items[] - the old items for outputting (custom form data will need to be injected later)
	// dynamic_defs[] - definitions for the field{n}_* fields
	// options[] - the actual data from the database for the current state of the amendment
	$old_total = 0;
	foreach($resources as $i=>$data){
		$items[] = array(
			'n'					=> $i,
			'title' 			=> $data[1],
			'old_field_desc'	=> $row[$type.'_'.$data[0].'_desc'],
			'old_field_amt'		=> sprintf("$%.2f", $row[$type.'_'.$data[0].'_amt']),
		);
		$old_total += $row[$type.'_'.$data[0].'_amt'];
		
		$dynamic_defs[] = array(
			id => "field${i}_desc",
			name => $data[1].' Description',
			type => 'text',
		);
		$dynamic_defs[] = array(
			id => "field${i}_amt",
			name => $data[1].' Amount',
			type => 'text',
			format => FORMAT_NUMBER,
			flags => OPTION_REQUIRED,
			defaultv => '0.00',
		);
		
		$options["field${i}_desc"] = $row["amendment_field${i}_desc"];
		$options["field${i}_amt"] = $row["amendment_field${i}_amt"];
		$options["total_amt"] += $row["amendment_field${i}_amt"];
	}
	$dynamic_defs[] = array(
		id => "total_amt",
		name => 'Total Amount',
		type => 'text',
		format => FORMAT_NUMBER,
		flags => OPTION_REQUIRED,
		defaultv => '0.00',
	);
	
	
	$options = map_post($dynamic_defs, $options, $_POST);
	$options = map_defaults($dynamic_defs, $options);
	
	if(isset($_POST['submit'])){
		if(!($error=validate_form($dynamic_defs, $options))){
			//no error
			$options = sanitize_input($announcement_defs, $options);
			
			// prepare db data
			foreach($options as $key=>$value){
				$dbData['amendment_'.$key] = $value;
			}
			
			if(isset($row['amendment_id'])){
				//update
				$db->Update('umb_seoc_amendment', 'amendment_id', $row['amendment_id'], array_merge($dbData, array(
					'amendment_status' => 1,
				)));
			} else {
				// insert
				$db->Insert('umb_seoc_amendment', array_merge($dbData, array(
					'amendment_type' => $type,
					'amendment_budget' => $row[$type.'_id'],
					'amendment_status' => 1,
				)));
			}
			
			$message = "Budget amendment saved successfully";
		}
	}
	
	//merge data into $items[]
	$total_amt = 0;
	for($i=0;isset($options["field${i}_amt"]);$i++){
		$items[$i]['field_desc'] = $options["field${i}_desc"];
		$items[$i]['field_amt'] = sprintf("%.2f", $options["field${i}_amt"]);
		$total_amt += $options["field${i}_amt"];
	}
	
	$template->Load("liaison_edit");
	$template->AssignVars(array(
		'id'			=> $id,
		'type' 			=> $type,
		'b_type' 		=> ucfirst($type),
		'club'			=> $row['club_name'],
		'clubid'		=> $row['club_id'],
		'error'			=> $error,
		'message'		=> $message,
		'submitted'		=> $umb->FormatTime($row[$type.'_submitted'], 'compacttime'),
		'event_name'	=> $row['event_title'],
		'event_date'	=> $umb->FormatTime($row['event_date'], 'printedtime'),
		'event_location'=> $row['event_space'],
		's_budget_item' => $items,
		'old_total_amt' => sprintf("%.2f", $old_total),
		'total_amt'		=> sprintf("%.2f", $total_amt),
	));
	$template->Parse();
}


?>