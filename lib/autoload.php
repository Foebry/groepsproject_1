<?php
session_start();

require_once "pdo.php";
require_once "html_functions.php";
require_once "form_elements.php";
require_once "sanitize.php";
require_once "validate.php";
require_once "security.php";

$errors = [];
$status = [];

if ( key_exists( 'errors', $_SESSION ) )
{
    $errors = $_SESSION['errors'];
}
if(key_exists("status", $_SESSION)){
    $status = $_SESSION["status"];
}
if (!isset($_SESSION["boodschappen"])){
    $_SESSION["boodschappen"] = [];
}


$_SESSION['errors'] = [];
$_SESSION["status"] = [];
$boodschappen = $_SESSION["boodschappen"];
