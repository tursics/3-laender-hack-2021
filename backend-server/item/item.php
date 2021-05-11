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

function insert($mysqli, $query)
{
	if ($mysqli->query($query) === TRUE) {
		echo ".";
	} else {
		echo "Error: " . $query . "<br>" . $mysqli->error;
	}
}

function insertDataset($mysqli, $obj) {
	$id = uniqid();
	$date = new DateTime();
	$timestamp = gmdate("Y-m-d\TH:i:s\Z", $date->getTimestamp());
	$countryId = 'de';
	$countryTitle = 'Deutschland';
	if ($obj->source === 'SBB') {
		$countryId = 'ch';
		$countryTitle = 'Schweiz';
	} elseif ($obj->source === 'Opentransportdata') {
		$countryId = 'ch';
		$countryTitle = 'Schweiz';
	} elseif ($obj->source === 'opendataportalat') {
		$countryId = 'at';
		$countryTitle = 'Österreich';
	} elseif (substr($obj->source, -2) === 'BB') {
		$countryId = 'at';
		$countryTitle = 'Österreich';
	}
	if (trim($obj->description) === '') {
		$obj->description = '-';
	}
	$obj->title = str_replace('"', "", $obj->title);
	$obj->title = str_replace("'", "", $obj->title);
	$obj->description = str_replace('"', "", $obj->description);
	$obj->description = str_replace("'", "", $obj->description);
	$portal = $obj->source;
	if ($portal === 'DB') {
		$portal = 'DB Open Data';
	} else if ($portal === 'OpendataÖPNV') {
		$portal = 'OpenData-Portal des ÖPNV';
	} else if ($portal === 'ÖBB') {
		$portal = 'ÖBB Open Data Portal';
	} else if ($portal === 'opendataportalat') {
		$portal = 'Open Data Portal Österreich';
	} else if ($portal === 'SBB') {
		$portal = 'Open-Data-Plattform SBB';
	} else if ($portal === 'Opentransportdata') {
		$portal = 'Open-Data-Plattform Mobilität Schweiz';
	}

	insert($mysqli, "INSERT INTO items (id, identifier, title, description,release_date, modification_date, country, keywords,mdc1,mdc2,mdc3)
		VALUES (
			'".$id."',
			'[\\\"".$id."\\\"]',
			'{\\\"de\\\": \\\"".trim(substr($obj->title, 0 , 250))."\\\"}',
			'{\\\"de\\\": \\\"".trim(substr(strip_tags($obj->description), 0, 2500))."\\\"}',
			'".$timestamp."',
			'".$timestamp."',
			'{\\\"id\\\": \\\"".$countryId."\\\",\\\"title\\\": \\\"".$countryTitle."\\\",\\\"portal\\\": \\\"".$portal."\\\"}',
			'{\\\"id\\\": \\\"".$obj->tags."\\\",\\\"title\\\": \\\"".$obj->tags."\\\"}',
			'".$obj->mdc1."',
			'".$obj->mdc2."',
			'".$obj->mdc3."'
		)");
}

function importCSVData($data) {
	$SOURCE = 0;
	$TITLE = 1;
	$LINK = 2;
	$DESCRIPTION = 3;
	$TAGS = 4;
	$GROUP1 = 5;
	$GROUP2 = 6;
	$GROUP3 = 7;

	$length = count($data);
	$database = getSQL();

	insert($database,'DELETE FROM items');

	for ($d = 0; $d < $length; ++$d) {
		$item = $data[$d];
		$obj = (object) array(
			'source' => $item[$SOURCE],
			'title' => $item[$TITLE],
			'LINK' => $item[$LINK],
			'description' => $item[$DESCRIPTION],
			'tags' => $item[$TAGS],
			'mdc1' => $item[$GROUP1],
			'mdc2' => $item[$GROUP2],
			'mdc3' => $item[$GROUP3],
		);

		if (($obj->source === '') && ($obj->title === '')) {
			continue;
		}
		insertDataset($database, $obj);
	}

	$database->close();
}

function readCSV($path, $separator) {
	$csvData = [];
	$row = 0;

	if (($handle = fopen($path, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
			if ($row > 0) {
				$csvData[] = $data;
			}
			++$row;
		}
		fclose($handle);
	}

	importCSVData($csvData);
}

readCSV('Musterdatenkatalog.csv', ';');
?>