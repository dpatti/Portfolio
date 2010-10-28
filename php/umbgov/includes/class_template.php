<?php
//class_template.php: controls templating
if(!defined("IN_UMB"))
	die("Error: Cannot access file directly");

class Template {
	var $vars,		//array of variables
		$structs,	//structures parsed from the file
		$file,		//stack of template files to process
		$session,	//info about session parsing
		$globals;	//vars for all templates (permissions)
		
	function Template(){		
		$this->file = array();
		$this->vars = array();
		$this->globals = array();
		/* Not doing this anymore
		//: fix this session thing <div class="&session=0b8be58f7d7419cc8d39405491f48bcc&session=0b8be58f7d7419cc8d39405491f48bcc"clear""></div>
		$this->session['enabled'] = FALSE;//!$umb->user->cookies;
		//regex for links without parameters
		$this->session['regex'][1] = '/("|\')('.str_replace('/', '\/', ROOT_PATH).'[^\1]+\.php)(?!\?)\1/';
		$this->session['replace'][1] = '$1$2?session='.$umb->session->sid.'$1';
		$this->session['regex'][2] = '/("|\')('.str_replace('/', '\/', ROOT_PATH).'[^\1]+\.php)\?(?!session)([^"]+)\1/';
		$this->session['replace'][2] = '$1$2?$3&session='.$umb->session->sid.'$1';
		//die(print_r($this->session));
		*/
	}
		
	//AssignVars: takes an array of keys and values which will replace {KEY} in the template
	//			  if values is an array or array of arrays, key will be assumed to be a struct
	//			  if called before a template is loaded, variables are assigned to global namespace
	function AssignVars($vars){
		foreach ($vars as $key=>$value) {
			if(count($this->vars) == 0)
				$this->globals[$key] = $value;
			else
				$this->vars[count($this->vars)-1][$key] = $value;
		}
	}
	
	//Load: gets a template file and stores its contents
	function Load($file){
		array_push($this->file, $this->_gettemplate($file));
		array_push($this->vars, (count($this->vars)>0) ? $this->vars[count($this->vars)-1] : array());
	}
	
	//Parse: breaks down structures and replaces variables
	function Parse($return=false){
		global $umb;
		//setup default group flags: F_MEMBER, F_MANAGER, F_ADMIN (here, becasue ->user isn't initialized yet
		//TODO: F_MANAGER smart check current type
		if(isset($umb)){
			$this->globals = array_merge($this->globals, array(
				'F_UNREGISTERED' => $umb->user->PermissionLevel(PERMISSION_UNREGISTERED, 0, TRUE),
				'F_MEMBER' => $umb->user->PermissionLevel(PERMISSION_MEMBER),
				'F_MANAGER' => $umb->user->PermissionLevel(PERMISSION_MANAGER),
				'F_ADMIN' => $umb->user->PermissionLevel(PERMISSION_ADMINISTRATOR),
				'F_LIAISON' => $umb->user->IsLiaison(),
			));
		}
		//parse file recursively for structs and replace with vars
		$parsed = $this->_destruct(array_pop($this->file));
		
		//continue to segment parser with the full segment
		$parsed = $this->_pseg($parsed, array_merge($this->globals, array_pop($this->vars)));
		
		if($return)
			return $parsed;
		echo $parsed;
	}
	
	//private members
	//_destruct: finds structures recursively, saves them, and replaces their existence with {STRUCT_NAME}
	function _destruct($segment){
		return preg_replace_callback('/[\t ]*<!-- BEGIN (\S+) -->\n?(.*?)\t*<!-- END \1 -->\n?/s', array($this, '_callback_destruct'), $segment, -1, $count);
	}
	
	//_pseg: recursively handles different blocks/variables within a segment
	function _pseg($segment, $vars){
		/*print_r($vars);
		echo "\n\n\n\n\n\n\n\n\n";*/
		//See end of file to understand this piece of shit:
		$temp = $this->param_vars;
		$this->param_vars = &$vars;

		//find and deal with INCLUDE blocks
		$segment = preg_replace_callback('/[\t ]*<!-- INCLUDE (\S+) -->/', array($this, '_callback_include'), $segment);

		//find and deal with IF blocks
		$segment = preg_replace_callback('/[\t ]*<!-- IF (\S+) -->(.*?)<!-- ENDIF \1 -->/s', array($this, '_callback_if'), $segment);
		
		//find and deal with variables
		$segment = preg_replace_callback('/\{(\S+?)\}/', array($this, '_callback_var'), $segment);
	
		//find and augment links if we're not using cookies
		if($this->session['enabled']){
			$segment = preg_replace($this->session['regex'][1], $this->session['replace'][1], $segment);
			$segment = preg_replace($this->session['regex'][2], $this->session['replace'][2], $segment);
		}
		
		$this->param_vars = &$temp;
		return $segment;
	}
	
	//_struct: gets a struct's contents or returns a variable-formed error
	function _struct($name){
		if(isset($this->structs[$name]))
			return $this->structs[$name];
		return "{invalid_template:$name}";
	}
	
	//_gettemplate: gets a template file from the default template directory as string or errors
	function _gettemplate($file){	
		global $umb;
		$path = TEMPLATE_PATH=="TEMPLATE_PATH" ? "templates/" : TEMPLATE_PATH;
		$file = $path . $file;
		if (preg_match('/\.(xml|html)$/', $file) == FALSE)
			$file .= ".html";
		if (!file_exists($file) && isset($umb)) {
			$umb->Error("Template does not exist: $file", ERROR_TEMPLATE|ERROR_TERMINATE);
		}
		return file_get_contents($file);
	}
	
	//_getphpfile: return a php file's output as a string
	function _getphpfile($file){
		global $umb;
		if (is_file($file)) {
			ob_start();
			include($file);
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
		} else if(isset($umb)) {
			$umb->Error("PHP file does not exist: $file", ERROR_TEMPLATE|ERROR_TERMINATE);
		}
		return "";
	}
	
	
	//Note: This shit is stupid as fuck. All because web servers don't want to upgrade to PHP 5.3, where I can use closure in preg_replace_callback.
	//To simulate closue, I am just storing the locals to the class and then referencing them from the functions which are now members
	var $param_vars;
	
	function _callback_destruct($matches){ //$name, $struct
		//save and return var replacement
		$this->structs[$matches[1]] = trim($this->_destruct($matches[2]), "\n\r");
		return '{'.$matches[1].'}';
	}
	
	function _callback_include($matches){
		if(preg_match('/\.php$/', $matches[1])){
			//include php file output
			return $this->_getphpfile($matches[1]);
		} else {
			//include raw data
			return $this->_pseg($this->_destruct($this->_gettemplate($matches[1])), $this->param_vars);
		}
	}
	
	function _callback_if($matches){ //$state, $content
		/*print_r($matches[1]);
		print_r($this->param_vars)
		echo "\n\n\n\n\n\n\n\n";*/
		$vars = $this->param_vars;
		$content = $this->_pseg($matches[2], $vars);
		$parts = explode('<!-- ELSE -->', $content);
		if (isset($vars[$matches[1]]) && $vars[$matches[1]]) {
			return $parts[0];
		} else if(isset($parts[1])) {
			return $parts[1];
		}
		return "";
	}
	
	function _callback_var($matches){
		$var = $matches[1];
		$vars = $this->param_vars;
		
		if(isset($vars[$var])){
			$val = &$vars[$var];
			if(is_array($val)){
				if(!empty($val)){
					reset($val);
					if(key($val) === 0){
						//sequence of structs
						foreach($val as $sval){
							$sub[] = $this->_pseg($this->_struct($var), array_merge($vars, $sval));
						}
						return implode("\n", $sub);
					} else {
						//single struct
						return $this->_pseg($this->_struct($var), array_merge($vars, $val));
					}
				}
			} else {
				//single var
				return $val;
			}
		} else {
			//couldn't find
			return '';//'<span class="error" style="color:red">{' . $var . '}</span>';
		}		
	}
}

?>