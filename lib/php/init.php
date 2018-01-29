<?php

error_reporting(E_ALL);
session_start();

require_once("lib/class/exception_handler.php");
require_once("lib/php/error_handler.php");
require_once("lib/php/config.php");
require_once("lib/class/db_object.php");
require_once("lib/class/user.php");
require_once("lib/php/functions.php");

$database = new mysqli(
        $config["mysql"]["host"],
        $config["mysql"]["username"],
        $config["mysql"]["password"],
        $config["mysql"]["database"]);

db_object::$connection = $database;

$template_content = "";
