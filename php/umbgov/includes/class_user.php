<?php
//class_user.php: controls authenticating and fetching of users
if(!defined("IN_UMB"))
	die("Error: Cannot access file directly");

define("PERMISSION_UNREGISTERED",	0);
define("PERMISSION_INACTIVE",		0);
define("PERMISSION_MEMBER", 		1);
define("PERMISSION_MANAGER", 		2);
define("PERMISSION_ADMINISTRATOR", 	3);

define("MANAGE_USERS", 				0x01);
define("MANAGE_HOME",				0x02);
define("MANAGE_BLOG",				0x04);
define("MANAGE_CALENDAR",			0x08);
define("MANAGE_TRACKER",			0x10);
define("MANAGE_PORTAL",				0x20);
define("MANAGE_RSO",				0x40);

class User {
	var $id,
		$password,	//full hash with salt
		$salt,		//user specific salt
		$name,
		$email,
		$group,		//permissions level
		$groupmask, //detailed permissions level
		$activated,	//flag indicating user has activated
		$liaison,   //whether the user is a liaison of any club
		$cookies,	//options
		$showemail,
		//...
		$stuff;
		
	function User(){
		$this->id = -1;
		$this->name = "Anonymous";
		$this->cookies = true;
		$this->group = 0;
		$this->liaison = false;
	}
	
	function InitializeUser($session){
		global $umb, $db;
		if(!$session){
			$umb->Error("Missing Session", ERROR_SESSION|ERROR_TERMINATE);
		}
		
		$uid = $session->user;
		if($uid < 0){
			$uid = form_number($_COOKIE['umb_user']);	
			if($uid <= 0)
				return;
			$session->auth = false;
			$session->user = $uid;
		}
		$this->GetUserInfo($uid);
		
		//check to make sure it's an active account
		if($this->group == 0 || $this->activated == 0){
			$umb->Message("This account has been locked. Contact an administrator for assistance.");
			exit; //just to be sure
		}
		$auth = $session->auth;
		if(!$auth && $uid>0){
			//need to re-auth with cookies
			$pass = $_COOKIE['umb_password'];
			if($pass != $this->password){
				$session->auth = false;
				$session->user = -1;
				$this->User(); //reset
				$umb->SetCookie("umb_user", "", -1, TRUE);
				$umb->SetCookie("umb_password", "", -1, TRUE);
			} else {
				//authenticated
				$session->auth = true;
				$session->UpdateDB();			
			}
		}
		
		//get liaison info
		$this->liaison = $this->PermissionLevel(PERMISSION_MANAGER, MANAGE_RSO) || $db->Count('umb_seoc_clubs', "WHERE club_liaison='".$uid."'") > 0;
	}
	
	function GetUserInfo($user){
		global $umb, $db;
		
		$db->Query("SELECT * FROM umb_users WHERE user_id='$user'");
		if($db->rows > 0){
			$this->id = $db->Result('user_id');
			$this->password = $db->Result('user_password');
			$this->salt = $db->Result('user_salt');
			$this->group = $db->Result('user_group');
			$this->groupmask = $db->Result('user_group_mask');
			$this->activated = $db->Result('user_activated');
			$this->name = $db->Result('user_name');
			$this->email = $db->Result('user_email');
			$this->cookies = $db->Result('user_opt_cookies');
			$this->showemail = $db->Result('user_opt_email');
		} else {
			$umb->Error("Invalid user id: $user", ERROR_UNEXPECTED|ERROR_TERMINATE);
		}
	}
	
	//PermissionLevel: returns whether the current user is of level (or higher, if exact is false),
	//					mask tests for specific manager perms, silent controls requesting login
	function PermissionLevel($level, $mask=0x00, $exact=false){
		global $umb;
		if($exact){
			if($this->group == $level){
				return ($this->groupmask & $mask)>0 || !$mask;
			}
		} else {
			if($this->group >= $level){
				// the weird inequality basically forces it to only check if you're trying $level = manager
				return ($this->groupmask & $mask)>(PERMISSION_MANAGER-$level) || ($this->group > $level) || !$mask;
			}
		}
		
		return false;
	}
	
	//PermissionRestrict: will request login if access is not met
	function PermissionRestrict($level, $mask=0x00){
		global $umb;
		
		$redirect = urlencode($_SERVER['REQUEST_URI']);
		$url = "";
		//TODO: redirect=$THIS_PAGE_BRO
		if(($level >= PERMISSION_MANAGER) && ($this->group >= $level) && (!$umb->session->super) && $umb->opt['superuser']){
			$url = "/login.php?action=super&redirect=$redirect";
		}
		if(($level >= PERMISSION_MEMBER) && (($this->group < PERMISSION_MEMBER) || (!$umb->session->auth))){
			$url = "/login.php?redirect=$redirect";
		}
		if(!empty($url)){
			if(isset($_REQUEST['ajax'])){
				//ajax
				echo json_encode(array(
					'auth' => $url . "&ajax",
				));
				exit;
			} else {
				$umb->Redirect($url);
			}
		}
		return $this->PermissionLevel($level, $mask);
	}
	
	function IsLiaison(){
		return $this->liaison;	
	}
}

?>