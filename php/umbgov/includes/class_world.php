<?php
// class_world.php: general class for initializing a page and storing site settings
if(!defined("IN_UMB"))
	die("Error: Cannot access file directly");
	
require("includes/functions_error.php");
include("definitions_options.php");
include("includes/labels.php");

define(MESSAGE_INFO, 'info');
define(MESSAGE_WARNING, 'warning');
define(MESSAGE_ERROR, 'error');

class World {
	var $user, 			
		$session,		
		$opt,			//array to hold all settings
		$stuff;
	
	function World(){

		
	}
	
	function InitializeSettings(){
		global $db, $global_defs;
		
		$db->Query("SELECT * FROM umb_global_settings");
		while($row = $db->Fetch()){
			$this->opt[$row['global_identifier']] = $row['global_value'];
		}
		foreach($global_defs as $group){
			foreach($group['args'] as $data){
				if(!isset($this->opt[$data['id']])){
					$this->opt[$data['id']] = $data['defaultv'];
					/*$db->Query("INSERT IGNORE INTO umb_global_settings 
								(global_identifier, global_value) VALUES
								('$data[id]', '$data[defaultv]')");*/
				}
			}
		}
		if(!$this->opt['masterswitch']){
			if(strstr($_SERVER['PHP_SELF'], '/admin/') === FALSE && strstr($_SERVER['PHP_SELF'], '/login.php') === FALSE){
				die(MAINTENANCE_MESSAGE);
			}
		}
	}

	// FormatTime: formats time by option, detail adds <span> tips for relative
	function FormatTime($time, $group='defaulttime', $detail=FALSE){
		global $labels;
		
		if($group == 'relative') //for passing 'relative' directly
			$display = 2;
		else
			$display = $this->opt[$group];
			
		if($group == 'defaulttime')
			$display += 2; // array_sliced 2 in options
		if($display == 0)
			$display = $this->opt['defaulttime']+2;
		if($display == 1)
			$display = $this->opt['defaulttime']+2+3; //minus time
			
			//die("$display+");
		if($display > 3){
			//die($labels['dateFmt'][$this->opt['timedisplay']]);
			$date = $labels['dateFmt'][$display];
			return date($date, $time);
		}
		
		//relative (2 <= $display <= 3)
		if($time==0) return "Never";
		if($display == 3)
			$detail = TRUE;
		$diff = time() - $time;
		//return $diff;
		/*$str = date("Y n j G i", $diff);
		$data = explode(" ", $str);
		$base_str = date("Y n j G i", 0);
		$base_data = explode(" ", $base_str);
		$data[0] = $data[0] - $base_data[0];
		$data[1] = $data[1] - $base_data[1];
		$data[2] = $data[2] - $base_data[2];
		$data[3] = $data[3] - $base_data[3];
		$data[4] = $data[4] - $base_data[4];*/
		//this is a new data-collection to work with the old print structure below
		$data = array(
			floor($diff / (60*60*24*365)),
			floor($diff / (60*60*24*30)),
			/*floor($diff / (60*60*24*7)),*/ //weeks
			floor($diff / (60*60*24)),
			floor($diff / (60*60)),
			floor($diff / (60)),		
		);
		
		if($detail){
			$ret = "<span class='tip' title='".date($labels['dateFmt'][$this->opt['defaulttime']+2], $time)."'>%s</span>";
		} else {
			$ret = "%s";
		}
		
		//echo print_r($data);
		
		if($diff < 0){
			//future tense
			if($data[0] < -1) return sprintf($ret, "in " . -$data[0] . " years");
			if($data[0] == -1) return sprintf($ret, "in 1 year");
			
			if($data[1] < -1) return sprintf($ret, "in " . -$data[1] . " months");
			if($data[1] == -1) return sprintf($ret, "in 1 month");
			
			if($data[2] < -1) return sprintf($ret, "in " . -$data[2] . " days");
			if($data[2] == -1) return sprintf($ret, "in 1 day");
			
			if($data[3] < -1) return sprintf($ret, "in " . -$data[3] . " hours");
			if($data[3] == -1) return sprintf($ret, "in 1 hour");
			
			if($data[4] < -1) return sprintf($ret, "in " . -$data[4] . " minutes");
			if($data[4] == -1) return sprintf($ret, "in 1 minute");
		
			return sprintf($ret, "within 1 minute");
		} else {
			//past tense
			if($data[0] > 1) return sprintf($ret, $data[0] . " years ago");
			if($data[0] == 1) return sprintf($ret, "1 year ago");
			
			if($data[1] > 1) return sprintf($ret, $data[1] . " months ago");
			if($data[1] == 1) return sprintf($ret, "1 month ago");
			
			if($data[2] > 1) return sprintf($ret, $data[2] . " days ago");
			if($data[2] == 1) return sprintf($ret, "1 day ago");
			
			if($data[3] > 1) return sprintf($ret, $data[3] . " hours ago");
			if($data[3] == 1) return sprintf($ret, "1 hour ago");
			
			if($data[4] > 1) return sprintf($ret, $data[4] . " minutes ago");
			if($data[4] == 1) return sprintf($ret, "1 minute ago");
			
			return sprintf($ret, "less than 1 minute ago");
		}
	}
	
	// SetCookie: sets a cookie if the user allows it
	function SetCookie($identifier, $value, $len=NULL, $force=TRUE){ // setting force to true for now; i think we're requiring them
		$len = time()+(($len) ? $len : $this->opt['sessionlen']);
		if($this->user->cookies || $force){
			setcookie($identifier, $value, $len, '/', ".".HOST_DOMAIN);
			//echo sprintf("%s, %s, %d(%d), %s, %s", $identifier, $value, $len, time(), '/', HOST_DOMAIN);
		}
		//die($identifier.$value.($this->user->cookies ? "true" : "false"));
	}
	
	// Redirect: uses header to instantly redirect to a page
	function Redirect($url){
		//TODO: account for non-cookie session
		header("Location: $url");	
	}
	
	// Message: a plain page with a simple message and possible redirect
	function Message($message, $redirect=NULL, $timeout=2){
		global $template;
		if($redirect === true)
			$redirect = $_SERVER["HTTP_REFERER"];
		$redirect = $redirect ? "<meta http-equiv='refresh' content='$timeout;url=$redirect'>" : '';
		if($template == NULL)
			die($message);
		$template->Load("message_basic");
		$template->AssignVars(array(
			'message' => $message,
			'redirect' => $redirect,
		));
		$template->Parse();
		exit;
	}
	
	//InlineMessage: returns a template parsed bit for inline insertion
	/*
	function InlineMessage($message, $type=MESSAGE_INFO){
		$template->Load('message_inline');
		$template->AssignVars(array(
			'type' => $type,
			'message' => $message,
		));
		return $template->Parse(true);
	}*/
	
	// Error: collects information and passes to general error handler
	function Error($message, $type=ERROR_TERMINATE){
		$stack = debug_backtrace();
		//die(print_r($stack));
		$file = str_replace(LOCAL_ROOT, '', $stack[0]['file']);
		$line = $stack[0]['line'];
		//$scope i.e., [World]->Message()
		$stack[1]['class'] = isset($stack[1]['class']) ? '['.$stack[1]['class'].']' : '';
		$stack[1]['function'] = isset($stack[1]['function']) ? $stack[1]['function'].'()' : '';
		$scope = $stack[1]['class'] . $stack[1]['type'] . $stack[1]['function'];
		error_handler($file, $line, $scope, $message, $type);
	}
}
?>