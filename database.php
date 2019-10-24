<?php


/* database save action */
function database_save($action, $admin, $plugin, $get) {

	$abort = [];

	$id = database_get_value($get, "id");
	$table = database_get_value($get, "table");
	$field = database_get_value($get, "field");
	$value = database_get_value($get, "value");
	$unique = database_get_value($get, "unique");

	switch ($action) {

		case 'database_update':

			if (isset($get["id"])) $id = $get["id"];
			else array_push($abort, "no id");

			if (isset($get["field"])) $field = $get["field"];
			else array_push($abort, "no field name");

			if (isset($get["table"])) $table = $get["table"];
			else array_push($abort, "no table name");

			if (isset($get["value"])) $value = $get["value"];
			else array_push($abort, "no value");

			if (!count($abort)) {

				if (database_update($table, $id, $field, $value))
					echo "success";
				else
					echo "database fail";

				exit();
			}

			else
				echo implode("|", $abort);

			break;
		

		case 'database_insert':

			if ($table !== false && $field !== false && $value != false) {
				if ($insert_id = database_insert($table, $field, $value, $unique)) {
					echo "success:" . $insert_id;
				}
				else {
					echo "database fail";
				}

				exit();
			}
			
			break;


		default:
			echo "no action";
			break;
	}


	exit();
}


function database_get_value($get, $name) {
	if (isset($get[$name])) {

		if ($get[$name] == "") {
			return "true";
		}
		else {
			return $get[$name];
		}
	}
	else {
		return false;
	}
}


// update fields
function database_update ($table, $id, $field, $value) {

	$query = "UPDATE $table";

	if (is_array($field)) {
/* ToDo change from field/value array */


	}

	else {
		$query .= " SET $field='$value'";
	}

	$query .= " WHERE id=$id";


	// send mysql query update
	return database_mysql_query ($query, $result);

}


function database_insert ($table, $field, $value, $unique=false) {

//TODO add unique check

	$query = "INSERT IGNORE INTO $table";

	$query .= " ($field)";
	$query .= " VALUES ($value)";

	// send mysql query update

	return database_mysql_query ($query, $result);

}


// connect databaase and send query
// return result or false
function database_mysql_query ($query, &$result) {

	$ret = false;
//debug($query);
	$db = new mysqli(DATABASE_SERVER,DATABASE_USER,DATABASE_PASSWORD,DATABASE_NAME);

	if ($db) {
		$db->set_charset("utf8");

		$result = $db->query($query);

		// get mysqli error
		$ret = mysqli_error($db);
//debug($db);
		$db->close();

		return $ret;
	}
	else
		return false;

}

?>