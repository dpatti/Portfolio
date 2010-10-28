<?php
// class_database.php: controls all interaction with the database
if(!defined("IN_UMB"))
	die("Error: Cannot access file directly");

require("credentials.php");
include_once("functions_error.php");

class Database {
	var $connection, //current connection
		$res, 		//last result
		$rows,		//number of rows selected/affected
		$fetch;		//cached copy of a single-row return
	
	function Database(){
		$this->Connect();	
	}
	
	// Connect: connects to database using credentials.php
	function Connect(){
		global $credentials;
		$this->connection = @mysql_connect($credentials['host'], $credentials['username'], $credentials['password']);
		if(!$this->connection){
			$this->Error("mysql_connect");
		}
		if(!mysql_select_db($credentials['database'])){
			$this->Error("mysq_select_db");
		}
		unset($credentials);
		return true;
	}
	
	// Query: executes a query and resets the fetch
	function Query($sql){
		unset($this->fetch);
		$this->res = @mysql_query($sql);
		if(!$this->res){
			$this->Error("mysql_query", $sql);
			$this->rows = 0;
			$this->insert_id = 0;
		} else {
			$this->rows = 0 + @mysql_num_rows() + @mysql_affected_rows();
			$this->insert_id = 0 + @mysql_insert_id();
		}
		return $this->res;
	}
	
	// Insert: inserts an associative array to a table
	function Insert($table, $data){
		foreach($data as $key=>$value){
			if($value == NULL){
				$data[$key] = "NULL";
			} else {
				$data[$key] = "'$value'";
			}
		}

		return $this->Query("INSERT INTO $table \n(" . 
							implode(", ", array_keys($data)) . ") VALUES \n(" .
							implode(", ", array_values($data)) . ")");
	}
	
	// Update: inserts an associative array to a table
	function Update($table, $col, $id, $data){
		foreach($data as $key=>$value){
			if($value == NULL){
				$data[$key] = "NULL";
			} else if (preg_match('/^#.+#$/', $value)){
				//sql command
				$data[$key] = substr($value, 1, -1);
			} else {
				$data[$key] = "'$value'";
			}
			$combine[] = "$key = ".$data[$key];
		}
	
		return $this->Query("UPDATE $table SET\n" .
							implode(",\n", $combine) .
							"\nWHERE $col='$id'\nLIMIT 1");
	}
	
	// Delete: deletes a single record
	function Delete($table, $col, $id){
		return $this->Query("DELETE FROM $table\nWHERE $col='$id'");
	}
	
	// Count: counts number of rows
	function Count($table, $cond=""){
		$this->Query("SELECT COUNT(*) FROM $table $cond");
		return $this->Result();
	}
	
	// Fetch: fetches and returns an entire row
	function Fetch($res=NULL){
		$res = ($res) ? $res : $this->res;
		return mysql_fetch_array($res);
	}
	
	// Result: fetches a single (or only) column from a single result and resets
	function Result($col=NULL, $res=NULL){
		$res = ($res) ? $res : $this->res;
		if(mysql_num_rows($res) == 0)
			return;
		if(!isset($this->fetch) || ($res != $this->res)){
			$row = $this->Fetch($res);
			mysql_data_seek($res, 0);
			if($res == $this->res){
				//only cache if it's current result
				$this->fetch = $row;
			}
		} else {
			$row = $this->fetch;
		}
		
		return $row[($col ? $col : 0)];
	}
	
	//Error: bypasses World, because it has a different stack and error message
	function Error($func, $extra=NULL, $type=ERROR_DATABASE){
		$error = mysql_error() . ($extra ? "\n[$extra]" : "");
		$message = sprintf("%s: %s", $func, $error);
		$stack = debug_backtrace();
		$i = 1;
		for(;$i<count($stack);$i++){
			if(!stristr($stack[$i]['file'], 'class_database.php')){
				break;
			}
		}
		$file = str_replace(LOCAL_ROOT, '', $stack[$i]['file']);
		$line = $stack[$i]['line'];
		$scope = sprintf("[%s]%s%s()", $stack[$i]['class'], $stack[$i]['type'], $stack[$i]['function']);
		error_handler($file, $line, $scope, $message, $type);	
	}
}


?>