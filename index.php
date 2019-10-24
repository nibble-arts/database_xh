<?php

/*if (!defined('CMSIMPLE_VERSION') || preg_match('#/database/index.php#i',$_SERVER['SCRIPT_NAME'])) 
{
    die('no direct access');
}*/


define("DATABASE_SERVER",$plugin_cf["database"]["database_server"]);
define("DATABASE_USER",$plugin_cf["database"]["database_user"]);
define("DATABASE_PASSWORD",$plugin_cf["database"]["database_password"]);
define("DATABASE_NAME",$plugin_cf["database"]["database_name"]);
define("DATABASE_EDIT_ATTR",$plugin_cf["database"]["edit_attribute"]);

define("DATABASE_EDIT_LEVEL",$plugin_cf["database"]["edit_accesslevel"]);

define("DATABASE_BASE", $pth["folder"]["plugin"]);
define("DATABASE_ROOT",$pth['folder']['base']);

define("DATABASE_NOTEMPLATE", $plugin_tx["database"]["fail_notemplate"]);
define("DATABASE_NOQUERY", $plugin_tx["database"]["fail_noquery"]);
define("DATABASE_DBFAIL", $plugin_tx["database"]["fail_database"]);
define("DATABASE_QUERYFAIL", $plugin_tx["database"]["fail_query"]);


include_once(DATABASE_BASE."database.php");


// check for ajax activity
if ($admin == "database_save") {
    $o .= database_save($action, $admin, $plugin, $_REQUEST);
    exit();
}



// plugin to access a mysql database

function database($query="", $template="", $filter="") {

	global $get, $admin, $action, $database, $_SESSION, $onload, $sn, $su, $f;


	if (class_exists("ma\Access")) {
		ma\Access::user();
	}


	$accesslevel = 0;
	$ret = "";

	// check for reserved words
	switch ($query) {

		// init database editing
		case "edit":

			// get accesslevel
			if (isset($_SESSION["accesslevel"])) $accesslevel = $_SESSION["accesslevel"];

			// if access, start js
			if ($accesslevel >= DATABASE_EDIT_LEVEL) {

				// return script include
				$ret .= '<script type="text/javascript" src="' . DATABASE_BASE . 'script/edit.js"></script>';

				// add to onload
				$onload .= "database.init('" . CMSIMPLE_URL . "', '" . DATABASE_EDIT_ATTR . "');";
			}
			break;

		case "select":

			// return script include
			$ret .= '<script type="text/javascript" src="' . DATABASE_BASE . 'script/select.js"></script>';

			// add to onload
			$onload .= "select.init('" . CMSIMPLE_URL . "', '" . DATABASE_EDIT_ATTR . "');";


			break;


		// execute query and parse template
		default:

			// check if template is defined
			if (file_exists(DATABASE_BASE . "templates/" . $template . ".html")) {
				$template = file_get_contents(DATABASE_BASE . "templates/" . $template . ".html");
			}

			// template not found error
			else {
				$ret .= '<div class="xh_fail">' . str_replace("%s", $template, DATABASE_NOTEMPLATE) . '</div>';
				return $ret;
			}

			// check if query is defined
			if (file_exists(DATABASE_BASE . "queries/" . $query . ".sql")) {
				$query = file_get_contents(DATABASE_BASE . "queries/" . $query . ".sql");
			}

			// no file and no select query
			elseif (strpos(strtolower($query), "select") === false) {
				$ret .= "<div class='xh_fail'>" . str_replace("%s", $query, DATABASE_NOQUERY) . "</div>";
			}


			//================================
			// add filter if present
// TODO add filter to existing where string of query






			if ($filter) {
				$query .= " ".$filter;
			}


			//================================
			// add parameters from $_GET in template
			preg_match_all("$\{([^\}]+)\}$", $template, $matches);

			foreach ($matches[1] as $id=>$param) {

				if (isset($_GET[$param])) {
					$template = str_replace($matches[0][$id], $_GET[$param], $template);
				}
			}


			//================================
			// add parameters from $_GET in query
			preg_match_all("$\{([^\}]+)\}$", $query, $matches);

			foreach ($matches[1] as $id=>$param) {

				if (isset($_GET[$param])) {
					$query = str_replace($matches[0][$id], $_GET[$param], $query);
				}

				else {
					$query = str_replace($matches[0][$id], "''", $query);
				}
			}

/*debug($query);
debug($template);
*/

			//================================
			// get database data
			$error = database_mysql_query($query, $result);

			// sql error
			if ($error) {
				$ret .= '<div class="xh_fail">' . str_replace("%s", $error, DATABASE_QUERYFAIL) . '</div>';
			}


			// parse result
			else {

				if ($result) {

					$structure = [];

					database_parse_template($template, $structure);

//debug(array_reverse($structure), false, "html");
//die();


					//================================
					// get header/loop/footer sections
					$templateArray = database_parse_loop($template);


					// output header
					$ret .= $templateArray["header"];

					// iterate results

					while ($record = $result->fetch_assoc()) {
						$ret .= database_parse_fields($templateArray["loop"], $record);
					}
				}
				else {
//					$ret .= '<div class="xh_fail">' . str_replace("%s", DATABASE_NAME, DATABASE_DBFAIL) . '</div>';
				}

				// output footer
				$ret .= $templateArray["footer"];
			}

			break;
	}

	return $ret;
}



function database_parse_template($template, array &$res, $type = "") {

	$part = new Part();
	$part->parse($template);

	$pre = $part->pre();
	$mid = "";
	$post = "";

	if (!$type) $type = "content";

	if ($part->type()) {
		database_parse_template($part->post(), $res, $part->type());
	}

	array_push($res, ["content"=>$part->pre(), "type"=>$type]);
}


class Part {

	var $pre = "";
	var $post = "";
	var $type = "";

	public function __construct($string = "") {

		if ($string != "") {
			return $this->parse($string);
		}
	}

	// parse string
	// false if nothing done
	// true if split
	public function parse($string) {

		preg_match("$\[([a-zA-Z]+)\]$", $string, $matches,PREG_OFFSET_CAPTURE);

		// if matches -> split up
		if (count($matches)) {
			$this->pre = trim(substr($string, 0, $matches[0][1]));
			$this->post = trim(substr($string, $matches[0][1] + strlen($matches[0][0])));
			$this->type = trim($matches[1][0]);

			return true;
		}
		else {
			$this->pre = $string;
			return false;
		}

	}

	// return pre string
	public function pre() {
		return $this->pre;
	}

	// return post string
	public function post() {
		return $this->post;
	}

	// return type
	public function type() {
		return $this->type;
	}

	public function __toString () {
		return print_r($this, true);
	}
}

// get data from field
function database_field($field) {

	global $database;

	if (isset($database["data"][$field])) {
		return $database["data"][$field];
	}
}


// parse template for loop sequence
// set loop between [startList] and [endList]
// return array with header, loop and footer part
function database_parse_loop($template) {

	$ret = [];

	$startString = "[startList]";
	$endString = "[endList]";

	$startPos = strpos($template, $startString);
	$endPos = strpos($template, $endString);

	// loop found
	if ($startPos !== false) {

		$startLoop = $startPos + strlen($startString);
		$loopLength = $endPos - $startLoop;

		$endPos = strpos($template, "[endList]");

		$ret["header"] = substr($template, 0, $startPos);
		$ret["loop"] = substr($template, $startLoop, $loopLength);
		$ret["footer"] = substr($template, $endPos + strlen($endString));
	}

	// no loop
	else {
		$ret["loop"] = $template;
		$ret["header"] = "";
		$ret["footer"] = "";
	}

	return $ret;
}


// parse the template
// replace {field} in template by fields in record
// remove area between [ ] if all database fields are empty
function database_parse_fields($template, $record) {

	// areas between [ ] are removed, if all database fields are empty
	// check for [ ] delete areas
	preg_match_all("/\[([^\]]*)\]/", $template, $matches);

	// iterate delete areas
	if ($matches) {

		foreach ($matches[0] as $snippet) {

			$modified = $snippet;

			// parse snippet
			$cnt = database_replace($modified, $record);

			// database fields not empty -> remove [ ]
			if ($cnt > 0) {
				$modified = substr($modified,1,strlen($modified)-2);
				$template = str_replace($snippet, $modified, $template);
			}

			// all database fields empty -> remove whole snippet
			else {
				$template = str_replace($snippet, "", $template);
			}

		}
	}

	// parse rest of template
	$cnt = database_replace($template, $record);


	return $template;
}


// replace {fieldname} against database record content
// return count of replaced fields not empty
function database_replace(&$part, $record) {

	$cnt = 0;

	preg_match_all("|{([a-zA-Z0-9\_\:\>\.]+)}|", $part, $matches);

	if ($matches) {

		$fields = $matches [1];

		// iterate fields
		foreach ($fields as $field) {

			// parse field name for function call

			$fieldArray = database_parse_field($field);

			$table = $fieldArray["table"];
			$func = $fieldArray["func"];
			$pipe = $fieldArray["pipe"];

			// replace placeholder by database data
			if (array_key_exists($field, $record)) {

				// get database data for field
				$data = $record[$field]; //'<span field="{$field}">' . $record[$field] . '</span>';


				// call function
				switch($func) {

					// convert iso date to human readable
					case "date":
						if($data) {
						$data = date( "j.n.Y", strtotime($data) );
						$field = $func.":".$field;
						}
						break;

					// convert to year only
					case "year":
						$data = date( "Y", strtotime($data) );
						$field = $func.":".$field;
						break;

					// convert to time
					case "time":
						$data = date( "G:i", strtotime($data) );
						$field = $func.":".$field;
						break;

					// display only if not null
					case "notnull":
						if ($data == 0) $data = "";
						$field = $func.":".$field;
						break;

				}


				// increment counter if not empty
				if ($data != "") {
					$cnt++;
				}
				else {
					$data = "&nbsp;";
				}


				// call pipe
				switch($pipe) {

					// activate edit
					case "edit":
//						debug($record);

						$data = '<span class="" id="'.$record["id"].'" table="'.$table.'" field="'.$field.'">'.$data.'</span>';
						$field = $table.".".$field.">".$pipe;
						break;
				}


				// replace with value -> replace \n with <br>
				$part = str_replace("{".$field."}", str_replace("\n", "<br>", $data), $part);

			}
		}
	}

	return $cnt;
}


// parse snippet string
//   format: {function:table.field>pipe}
//   function and pipe are optional
//   table.field is mandatory
//
//   functions alter the field data by function
//   pipe alters the final string, i.e. adding a edit span tag
function database_parse_field(&$field) {

	$table = false;
	$func = false;
	$pipe = false;

	// get pipe
	$pipeArray = explode(">", $field);

	if (count($pipeArray) > 1) {
		$field = $pipeArray[0];
		$pipe = $pipeArray[1];
	}

	// get function
	$fieldArray = explode(":", $field);

	if (count($fieldArray) > 1) {
		$func = $fieldArray[0];
		$field = $fieldArray[1];
	}

	// get table
	$fieldArray = explode(".", $field);

	if (count($fieldArray) > 1) {
		$table = $fieldArray[0];
		$field = $fieldArray[1];
	}

	return ["func" => $func, "pipe" => $pipe, "table" => $table];
}


?>
