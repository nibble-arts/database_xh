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


define("DATABASE_ROOT", $pth["folder"]["plugin"]);

define("DATABASE_NOTEMPLATE", $plugin_tx["database"]["fail_notemplate"]);
define("DATABASE_NOQUERY", $plugin_tx["database"]["fail_noquery"]);
define("DATABASE_DBFAIL", $plugin_tx["database"]["fail_database"]);


// plugin to access a mysql database

function database($query, $template, $filter="") {

	global $database, $_SESSION, $onload;
	


	// check for reserved words
	switch ($query) {

		// init database editing
		case "edit":

			// return script include
			$ret .= '<script type="text/javascript" src="' . DATABASE_ROOT . 'script/edit.js"></script>';

			// add to onload
			$onload .= "database_init_edit('" . DATABASE_EDIT_ATTR . "');";
			break;


		// execute query and parse template
		default:
			// check if template is defined
			if (file_exists(DATABASE_ROOT . "templates/" . $template . ".html")) {
				$template = file_get_contents(DATABASE_ROOT . "templates/" . $template . ".html");
			}

			// template not found error
			else {
				$ret .= '<div class="xh_fail">' . str_replace("%s", $template, DATABASE_NOTEMPLATE) . '</div>';
				return $ret;
			}

			// check if query is defined
			if (file_exists(DATABASE_ROOT . "queries/" . $query . ".sql")) {
				$query = file_get_contents(DATABASE_ROOT . "queries/" . $query . ".sql");
			}

			// no file and no select query
			elseif (strpos(strtolower($query), "select") === false) {
				$ret .= "<div class='xh_fail'>" . str_replace("%s", $query, DATABASE_NOQUERY) . "</div>";
			}


			//================================
			// add filter if present
			if ($filter) {
				$query .= " ".$filter;
			}


			//================================
			// get database data
			$result = database_query($query);
			
			if ($result) {

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
				$ret .= '<div class="xh_fail">' . str_replace("%s", DATABASE_NAME, DATABASE_DBFAIL) . '</div>';
			}

			// output footer
			$ret .= $templateArray["footer"];

			break;
	}

	return $ret;
}


// send query and return result
function database_query($query) {

	$db = new mysqli(DATABASE_SERVER,DATABASE_USER,DATABASE_PASSWORD,DATABASE_NAME);

	if ($db) {
		$db->set_charset("utf8");

		$result = $db->query($query);

		$db->close();

		return $result;
	}
	else
		return false;
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

	preg_match_all("|{([a-zA-Z0-9\_\:]+)}|", $part, $matches);

	if ($matches) {

		$fields = $matches [1];


		// iterate fields
		foreach ($fields as $field) {

			// parse field name for function call
			$func = database_parse_field($field);


			// replace placeholder by database data
			if (array_key_exists($field, $record)) {

				// get database data for field
				$data = $record[$field]; //'<span field="{$field}">' . $record[$field] . '</span>';


				// call function
				switch($func) {

					// convert iso date to human readable
					case "date":
						$data = date( "j.n.Y", strtotime($data) );
						$field = $func.":".$field;
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

				// replace with value -> replace \n with <br>
				$part = str_replace("{".$field."}", str_replace("\n", "<br>", $data), $part);

				if ($data != "") {
					$cnt++;
				}
			}
		}
	}
//echo($field."# ".$cnt.": ".$func."-".$part."<br>");

	return $cnt;
}


// parse snippet string
// perform action, if {action:field} is found
// return function
function database_parse_field(&$field) {

	$fieldArray = explode(":", $field);

	if (count($fieldArray) > 1) {

		$field = $fieldArray[1];

		return $fieldArray[0];
	}

	return false;
}


?>