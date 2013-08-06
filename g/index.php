<?php
header('content-type: application/json; charset=utf-8');
header("access-control-allow-origin: *");
require_once "data.php";


if (isset($_GET['seed'])) {
	echo genProfile(null, stripslashes($_GET['seed']));
} else {
	echo genProfile($_GET['gender']);
}

?>