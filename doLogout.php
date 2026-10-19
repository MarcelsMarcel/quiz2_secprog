<?php
session_start();
$_SESSION = array();

if (isset($_COOKIE['remember_email'])) {
    setcookie('remember_email', '', time() - 3600, "/");
}

session_destroy();
session_start();
session_regenerate_id(true);

header("location: ./login.php");
exit;
?>