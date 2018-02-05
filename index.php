<?php

require_once("lib/php/init.php");

if(User::LoggedIn()) {
    require_once("lib/php/routes.php");
} else {
    require_once("lib/php/authentication.php");
}

echo template("content/html/layout.html", ["template_content" => $template_content, "user" => $user]);
