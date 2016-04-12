<?php defined('SYSPATH') OR die('No direct access allowed.');
header('Content-Type: application/json');

$http_status_code = $success ? 200 : 500;
header("HTTP/1.0 $http_status_code");

echo json_encode($value);
