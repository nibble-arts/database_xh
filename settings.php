<?php

define ('DATABASE_QUERY_PATH', $plugin_cf["database"]["path_queries"]);
define ('DATABASE_TEMPLATE_PATH', $plugin_cf["database"]["path_templates"]);

define ('DATABASE_FAIL_READ', $plugin_tx['database']['fail_fileread']);
define ('DATABASE_FAIL_WRITE', $plugin_tx['database']['fail_filewrite']);
define ('DATABASE_FAIL_EXISTS', $plugin_tx['database']['fail_fileexists']);

define ('DATABASE_QUERIES', $plugin_tx['database']['queries_sql']);
define ('DATABASE_TEMPLATES', $plugin_tx['database']['queries_templates']);
define ('DATABASE_SAVED', $tx['message']['saved']);






function database_settings($action, $admin, $plugin) {

	global $plugin_tx, $o;

	$type = false;
	$file = false;
	$data = "";

	$actionArray = explode(":", $action);

	$command = $actionArray[0];

	if (count($actionArray) > 1) {

		$typeFile = explode("|", $actionArray[1]);

		$type = database_set_slash($typeFile[0], false);
		$file = trim($typeFile[1]);
	}


	//=========================
	// save action
	if ($command == "save") {

		// set action array for edit display
		$command = "plugin_edit";

		$type = $_POST["type"];

		$extenstion = pathinfo($_POST["file"], PATHINFO_EXTENSION);
		$file = pathinfo($_POST["file"], PATHINFO_FILENAME);

		$data = $_POST["data"];


		// add extension by type
		switch ($type) {

			case 'templates':
				$file .= ".html";
				break;

			case 'queries':
				$file .= ".sql";
				break;
		}


		// create path
		$path = DATABASE_BASE . database_set_slash($type, true) . $file;


		// file already exists => dont save
		if (file_exists($path) && $data == "") {
				$o .= '<div class="xh_warning">' . str_replace("%s", $file, DATABASE_FAIL_EXISTS) . '</div>';
		}


		// save file
		else {

			// save data
			if (file_put_contents($path, $data) !== false) {
				$o .= '<div class="xh_success">' . str_replace("%s", $file, DATABASE_SAVED) . '</div>';
			}

			// error writing the file
			else {
				$o .= '<div class="xh_fail">' . str_replace("%s", $file, DATABASE_FAIL_WRITE) . '</div>';
			}
		}

	}



	//=========================
	// switch action
	switch ($command) {

		case 'plugin_text':

			if ($file) {

				$delPath = explode("_", $type)[0] . "/" . $file;

				if (file_exists(DATABASE_BASE.$delPath)) {
					unlink(DATABASE_BASE.$delPath);
				}

			}

			$o .= "<h1>" . $plugin_tx["database"]["title_settings"] . "</h1>";

			$o .= "<p>" . $plugin_tx["database"]["description_settings"] . "</p>";

			// check template path
			if (file_exists(DATABASE_BASE.DATABASE_TEMPLATE_PATH)) {
				$o .= "<h4>" . DATABASE_TEMPLATES . "</h4>";
				$o .= database_list_files(DATABASE_TEMPLATE_PATH);
			}
			else {
				$o .= '<div class="xh_fail">' . $plugin_tx["database"]["fail_templatepath"] . '</div>';
			}

			// check query path
			if (file_exists(DATABASE_BASE.DATABASE_QUERY_PATH)) {
				$o .= "<h4>" . DATABASE_QUERIES . "</h4>";
				$o .= database_list_files(DATABASE_QUERY_PATH);
			}
			else {
				$o .= '<div class="xh_fail">' . $plugin_tx["database"]["fail_querypath"] . '</div>';
			}

			break;


		//=========================
		// start editing
		case 'plugin_edit':

			// title
			$o .= "<h1>" . str_replace("%s", $file, str_replace("%t", ucfirst($type), $plugin_tx["database"]["title_edit"])) . "</h1>";

			$o .= '<p><a href="?database&admin=plugin_main&action=plugin_text&normal">' . $plugin_tx["database"]["back"] . '</a></p>';

			$o .= database_edit_data($type, $file);
			break;

	}


	return $o;
}


function database_list_files($type) {

	global $c, $h;

	$ret = "";

	if ($handler = opendir(DATABASE_BASE . $type)) {

		// new file
		$ret .= '<form method="post" action="?database" accept-charset="UTF-8">';

			$ret .= '<input type="text" name="file">';

			$ret .= '<input name="type" value="' . $type . '" type="hidden">';
			$ret .= '<input name="action" value="save" type="hidden">';
			$ret .= '<input name="admin" value="plugin_main" type="hidden">';

			$ret .= '<input class="submit" value="New anlegen" type="submit">';

		$ret .= '</form>';


		// list of files
		$ret .= "<ul>";

		// get database usage
		$used = new Database_usage($c, $h);


		while (($file = readdir($handler)) != false) {

			if ($file != "." && $file != "..") {

				// get usage
				$u = $used->is_used($type, $file);

				$ret .= '<li><a href="?database&admin=plugin_main&action=plugin_edit:' . $type . "|" . $file . ' &normal"> ' . $file . '</a> ';

				if ($u) {
					$ret .= "(" . $u . ")";
				}

				else {
				 	$ret .= ' <a href="?database&admin=plugin_main&action=plugin_text:' . $type . '_del|' . $file . '&normal"><img src="' . DATABASE_BASE . '/images/del.gif"></a></li>';
				}

			}
		}

		$ret .= "</ul>";
	}

	return $ret;
}


function database_edit_data($type, $file) {

	$ret = "";
	
	$data = file_get_contents(DATABASE_BASE . database_set_slash($type, true) . $file);

	if ($data === false) {
		$ret .= '<div class="xh_fail">' . str_replace("%s", database_set_slash($type, true) . $file, DATABASE_FAIL_READ) . '</div>';
	}

	else {

		$ret .= '<form method="post" action="?database" accept-charset="UTF-8">';
			$ret .= '<input class="submit" value="Sichern" type="submit">';

			$ret .= '<textarea name="data" rows="24" class="xh_file_edit" autofocus>' . $data . '</textarea>';

			$ret .= '<input name="type" value="' . database_set_slash($type, false) . '" type="hidden">';
			$ret .= '<input name="file" value="' . $file . '" type="hidden">';
			$ret .= '<input name="action" value="save" type="hidden">';
			$ret .= '<input name="admin" value="plugin_main" type="hidden">';

			$ret .= '<input class="submit" value="Sichern" type="submit">';
		$ret .= '</form>';
	}

	return $ret;

}


function database_set_slash($path, $type) {

	// set slash
	if ($type) {
		if (substr($path, strlen($path)-1) != "/")
			$path .= "/";
	}

	// remove slash
	else {
		if (substr($path, strlen($path)-1) == "/")
			$path = substr($path, 0, strlen($path) - 1);
	}

	return $path;
}






class Database_usage {

	private $query;
	private $template;


	public function __construct($content, $headers) {

		$this->get_used($content, $headers);

	}


	// get all used database templates and queries
	public function get_used($content, $headers) {

		foreach ($content as $pg=>$pageData) {

			preg_match_all("#{{{[\ ]?database[\ ]?\((.*)\)[;]?}}}#i", $pageData, $matches);

			if ($matches[1]) {

				// iterate matches
				foreach ($matches[1] as $entry) {

					$paramArray = explode(",", $entry);
					$temp = [];

					// switch by parameters
					if (count($paramArray) > 1) {

						$temp["page"] = $pg;
						$temp["title"] = $headers[$pg];

						$queryName = str_replace("\"", "", $paramArray[0]);
						$templateName = str_replace("\"", "", $paramArray[1]);

						// is query file definition
						if (!$this->is_query($queryName)) {

							// add query if not existing
							if (!isset($this->query[$queryName]))
								$this->query[$queryName] = [];

							array_push($this->query[$queryName], $temp);
						}

						// add template if not existing
						if (!isset($this->template[$templateName]))
							$this->template[$templateName] = [];

						array_push($this->template[$templateName], $temp);

					}

				}
			}
		}
	}


	public function is_used($type, $name) {

		// remove extension
		$name = explode(".", $name)[0];


		if ($type == DATABASE_QUERY_PATH) {
			return $this->has_query($name);
		}

		if ($type == DATABASE_TEMPLATE_PATH) {
			return $this->has_template($name);
		}
	}


	// return count of used queries or false
	public function has_query($name) {

		if (isset($this->query[$name])) {
			return count($this->query[$name]);
		}

		else {
			return false;
		}
	}


	// return count of used templates or false
	public function has_template($name) {

		if (isset($this->template[$name])) {
			return count($this->template[$name]);
		}

		else {
			return false;
		}
	}


	public function get_query($name) {

		if ($this->has_query($name)) {
			return $this->query[$name];
		}

		else {
			return false;
		}

	}


	private function is_query($string) {

		if (substr(trim($string), 1, 6) == "select")
			return true;
		else
			return false;
	}


}


?>