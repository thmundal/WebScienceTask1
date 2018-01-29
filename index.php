<?php

require_once("lib/php/init.php");

if(User::LoggedIn()) {
    require_once("lib/php/routes.php");
} else {
    if(get_route($config["root_url"]) == "/register") {
        if($_POST) {
            $required = ["username", "password", "pw_repeat"];
            $valid = true;

            foreach($_POST as $key => $val) {
                if(in_array($key, $required) AND empty($val)) {
                    $valid = false;
                }
            }

            if($_POST["password"] != $_POST["pw_repeat"]) {
                $valid = false;
            }

            if($valid) {
                if(!User::Exists($_POST["username"])) {
                    if(User::register($_POST["username"], $_POST["password"])) {
                        User::login($_POST["username"], $_POST["password"]);
                        redirect($config["root_url"]);
                    }
                } else {
                    $template_content = "Bruker eksisterer allerede";
                }
            }
        } else {
            $template_content = template("content/html/register.html", ["test" => "variable"]);
        }
    } else {
        if($_POST) {
            if(User::Login($_POST["username"], $_POST["password"])) {
                redirect($config["root_url"]);
            } else {
                $template_content = "Login feilet";
            }
        } else {
            $template_content = template("content/html/login.html", ["test" => "variable"]);
        }
    }
}

echo template("content/html/layout.html", ["template-content" => $template_content]);
