<?php
header("Access-Control-Allow-Origin: *");

function getSQL() {
	$host_name = 'db###.hosting-data.io';
	$database = 'dbs###';
	$user_name = 'dbu###';
	$password = '###';

	$mysqli = new mysqli($host_name, $user_name, $password, $database);

	if ($mysqli->connect_error) {
		$error = array('success' => false, 'message' => $mysqli->connect_error);
		die(json_encode($error));
	}

	return $mysqli;
}

function get($mysqli, $query)
{
	$stmt = $mysqli->stmt_init();
	if(!$stmt->prepare($query)) {
		$error = array('success' => false, 'message' => 'Failed to prepare statement');
		die(json_encode($error));
	}

	$stmt->execute();
	$result = $stmt->get_result();
	$data = [];
	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		foreach ($row as $key => $value) {
			if (is_string($value) && (strlen(trim($value)) >= 1) && (in_array(trim($value)[0], ['{', '[']))) {
				$row[$key] = json_decode($row[$key]);
			}
		}
		$data[] = $row;
	}
	$stmt->close();

	return $data;
}

	$filter = htmlspecialchars($_GET['filter']);
	$database = getSQL();

	$count = get($database, 'SELECT COUNT(id) as count FROM items')[0]['count'];
	$facets = get($database, 'SELECT * FROM facets');
	$items = get($database, 'SELECT * FROM items');
	$database->close();

	if ($facets === null) {
		$facets = [];
	}

	$mdk = [];
	foreach ($items as $item) {
		$mdk[] =  array(
			'top' => $item['mdc1'],
			'sub' => $item['mdc2'], 
			'org' => $item['country']->title . ': ' . $item['country']->portal, 
			'name' => $item['title']->de, 
			'id' => $item['id']
		);
	}
	
	die(json_encode($mdk));
?>