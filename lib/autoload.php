<?php
session_start();

require_once "pdo.php";
require_once "html_functions.php";
require_once "form_elements.php";
require_once "sanitize.php";
require_once "validate.php";
require_once "security.php";

$next_gro_id_sql = "SELECT AUTO_INCREMENT next_gro_id FROM information_schema.tables WHERE table_name = 'grocery';";
$_SESSION["next_gro_id"] = getData($next_gro_id_sql)[0]["next_gro_id"];

$errors = [];
$status = [];
$info = [];

if ( key_exists( 'errors', $_SESSION ) )
{
    $errors = $_SESSION['errors'];
}
if(key_exists("status", $_SESSION)){
    $status = $_SESSION["status"];
}
if (key_exists("info", $_SESSION)){
    $info = $_SESSION["info"];
}
if (!isset($_SESSION["boodschappen"])){
    $_SESSION["boodschappen"] = [];
}


$_SESSION['errors'] = [];
$_SESSION["status"] = [];
$_SESSION["info"] = [];
$boodschappen = $_SESSION["boodschappen"];
