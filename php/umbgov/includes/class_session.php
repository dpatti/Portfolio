<?php
//class_session.php: controls cookies and session interaction
if(!defined("IN_UMB"))
	die("Error: Cannot access file directly");

class Session {
	var $sid, 			//current session id
		$ip,			//get with ->GetIP()
		$user,			//user in current session 
		$auth=false,	//if the session is new, the user is not authenticated yet
		$super=false;	//super user access
		
	function Session(){
		global $umb;
		$sid = form_hex($_COOKIE['umb_session'], NULL);
		if(!$sid){
			$sid = form_hex($_GET['session'], NULL);
		}
		$this->sid = $sid;
		$this->user = -1;
		$this->Validate();
		$this->Prune($umb->opt['sessionlen'], $umb->opt['sessionlog']);
	}
	
	// Create: creates a new session
	function Create(){
		global $umb;
		$ip = $this->GetIP();
		$time = time();
		
		$this->sid = md5(sprintf("%u:%d", $ip, $time));
		$this->UpdateDB();
		
		return $this->sid;
	}
	
	// Validate: checks current session ip and time against db; also updates timestamp
	function Validate(){
		global $umb, $db;
		
		$sid = $this->sid;
		if($sid == NULL){
			$this->Create();
			return false;
		}
		
		$db->Query("SELECT * FROM umb_users_session
					WHERE session_sid='$sid'");
		if($db->rows == 0){
			$this->Create();
			return false;
		}
		
		$ip 	= (int)($db->Result('session_ip')+0);
		$uid 	= $db->Result('session_user');
		$time 	= $db->Result('session_time');
		$auth 	= $db->Result('session_auth');
		$super 	= $db->Result('session_super');
		$uid	= ($uid == NULL) ? -1 : $uid;
		
		/*if($this->user != $uid){
			if($this->user < 0){
				//update anonymous
				$this->user = $uid;
			} else {
				$umb->Error("Session user id did not match ($uid)", ERROR_SESSION|ERROR_UNEXPECTED);
				$this->Create();
				return false;
			}
		}*/
		$this->user = $uid;
		if($this->GetIP() != $ip){
			$umb->Error("Session ip did not match (".long2ip($ip)." vs ".$this->GetIP(true).")", ERROR_SESSION|ERROR_UNEXPECTED);
			$this->Create();
			return false;
		}
		if(time()-$time > $umb->opt['sessionlen']){
			$this->Create();
			return false;
		}
		if($this->user>0 && !$auth){
			// no need to re-create or update, just invalidate for auth
			return false;
		}
		
		//update timestamp
		$this->auth = true;
		$this->super = $super;
		$this->UpdateDB();
		return true;
	}
	
	// Prune: deletes old entries in the db
	function Prune($timeout, $expire){
		global $db;
		$t_timeout = time()-$timeout;
		$t_expire = time()-$timeout-$expire;
		//pruning sessions table rows of...
		//	1. a non-user's session timed out
		//	2. a user's session has exceeded log time ($expire)
		//  3. a user's previous, timed out session with the same ip
		$db->Query("DELETE FROM umb_users_session
					WHERE 	(ISNULL(session_user) AND session_time<$t_timeout) OR 
							(NOT ISNULL(session_user) AND session_time<$t_expire) OR
							(session_user='".$this->user."' AND session_ip='".sprintf("%u", $this->GetIP())."' AND session_time<$t_timeout)");
		/*if($db->Count('umb_users_session')==0)
			die("I ACCIDENTALLY THE WHOLE SESSIONS TABLE ($time = ".time()." - $timeout)");*/
	}
	
	// GetIP: gets and caches the ip as an unsigned long
	function GetIP($asString=false){
		if(!isset($this->ip)){
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
				$this->ip = form_string($_SERVER['HTTP_X_FORWARDED_FOR']);
			} else {
				$this->ip = form_string($_SERVER['REMOTE_ADDR']);
			}
			$this->ip = ip2long($this->ip);
		}
		
		return $asString ? long2ip($this->ip) : $this->ip;
	}
	
	// UpdateDB: adds to database
	function UpdateDB(){
		global $db, $umb;
		$sid	= $this->sid;
		$ip		= $this->GetIP();
		$uid	= ($this->user>0) ? sprintf("'%s'", $this->user) : "NULL";
		$auth	= ($this->auth) ? "TRUE" : "FALSE";
		$super  = ($this->super) ? "TRUE" : "FALSE";
		$time 	= time();
		
		$umb->SetCookie('umb_session', $this->sid);	
		//need to sprintf for unsigned $ip (%u)
		$db->Query(sprintf("INSERT INTO umb_users_session
							(session_sid, session_ip, session_user, session_time) VALUES
							('$sid', '%u', $uid, '$time')
							ON DUPLICATE KEY UPDATE 
								session_ip='%u',
								session_user=$uid,
								session_time='$time',
								session_auth=$auth,
								session_super=$super", $ip, $ip));
		if($this->user > 0)
			$db->Query("UPDATE umb_users SET user_last_online='$time'
						WHERE user_id=$uid");
	}

}

?>