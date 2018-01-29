<?php

switch(get_route($config["root_url"])) {
    case "/":
    default:
        $template_content = template("content/html/user_index.html");
    break;

    case "/logout":
        $template_content = "Logging out...";
        User::Logout();
        redirect($config["root_url"]);
    break;

}
