<?php

define (DATABASE_QUERY_PATH, $plugin_cf["database"]["path_queries"]);
define (DATABASE_TEMPLATE_PATH, $plugin_cf["database"]["path_templates"]);

function database_settings($action, $admin, $plugin) {

	global $plugin_tx;

	$actionArray = explode(":", $action);

	switch ($actionArray[0]) {

		case 'plugin_text':

			$o .= "<h1>" . $plugin_tx["database"]["title_settings"] . "</h1>";

			// check template path
			if (file_exists(DATABASE_BASE.DATABASE_TEMPLATE_PATH)) {
				$o .= "<h4>Queries</h4>";
				$o .= database_list_files(DATABASE_BASE, DATABASE_TEMPLATE_PATH);
			}
			else {
				$o .= '<div class="xh_fail">' . $plugin_tx["database"]["fail_templatepath"] . '</div>';
			}

			// check query path
			if (file_exists(DATABASE_BASE.DATABASE_QUERY_PATH)) {
				$o .= "<h4>Templates</h4>";
				$o .= database_list_files(DATABASE_BASE, DATABASE_QUERY_PATH);
			}
			else {
				$o .= '<div class="xh_fail">' . $plugin_tx["database"]["fail_querypath"] . '</div>';
			}

			break;


		case 'plugin_edit':

			$type = pathinfo($actionArray[1], PATHINFO_EXTENSION);

			switch (trim($type)) {

				case 'html':
					$editType = "Template";
					break;

				case 'sql':
					$editType = "Query";
					break;
				
			}

			// title
			$o .= "<h1>" . str_replace("%s", "'".$editType."'", $plugin_tx["database"]["title_edit"]) . "</h1>";

			$o .= '<p><a href="?database&admin=plugin_main&action=plugin_text&normal">' . $plugin_tx["database"]["back"] . '</a></p>';

			$o .= database_edit_data($actionArray[1]);
			break;
	}


	return $o;
}


function database_list_files($path, $name) {

	if ($handler = opendir($path.$name)) {

		$ret .= "<ul>";

		while (($entry = readdir($handler)) != false) {

			if ($entry != "." && $entry != "..") {

				$ret .= '<li><a href="?database&admin=plugin_main&action=plugin_edit:' . $path.$name.$entry . ' &normal"> ' . $entry . '</a></li>';
			}
		}

		$ret .= "</ul>";
	}

	return $ret;
}


function database_edit_data($file) {

	$data = file_get_contents(trim($file));

	$ret .= '<form method="post" action="/database/" accept-charset="UTF-8">';
		$ret .= '<input class="submit" value="Sichern" type="submit">';

		$ret .= '<textarea rows="24" class="xh_file_edit" autofocus>' . $data . '</textarea>';

		$ret .= '<input class="submit" value="Sichern" type="submit">';
	$ret .= '</form>';

	return $ret;

}

?>