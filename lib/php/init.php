<?php

error_reporting(E_ALL);
session_start();

require_once("lib/class/exception_handler.php");
require_once("lib/php/error_handler.php");
require_once("lib/php/config.php");
require_once("lib/smarty/smarty-2.6.31/libs/Smarty.class.php");
require_once("lib/class/db_object.php");
require_once("lib/class/user.php");
require_once("lib/class/user_profile.php");
require_once("lib/class/chat_handle.php");
require_once("lib/php/functions.php");

$memcached = new Memcached;
$memcached->addServer("localhost", 11211);

$database = new mysqli(
        $config["mysql"]["host"],
        $config["mysql"]["username"],
        $config["mysql"]["password"],
        $config["mysql"]["database"]);

db_object::$connection = $database;

$template_content = "";
$smarty = new Smarty();
$smarty->template_dir = "/var/www/usn/websciencetask1/";

$user = null;
$user_id = null;

if(User::LoggedIn()) {
    $user = User::Load($_SESSION["login"]);
    $user_id = $user->get("id");
}

$smarty->assign("user_id", $user_id);
