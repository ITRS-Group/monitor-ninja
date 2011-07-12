<?php
@session_name('ninjasession');
session_start();
$_SESSION['username'] = $_SERVER['REMOTE_USER'];
header('Location: /ninja/');
?>