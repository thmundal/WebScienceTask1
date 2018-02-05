<?php

switch(get_route($config["root_url"])) {
    case "/":
    default:
        $template_content = template("content/html/user_index.html", ["username" => $user->get("username")]);
    break;

    case "/logout":
        $template_content = "Logging out...";
        User::Logout();
        redirect($config["root_url"]);
    break;

    case "/users":
        $template_content = "Test";

        $users_list = User::GetList();
        $template_content = template("content/html/user_list.html", ["list" => $users_list]);
    break;

    case "/chat":
        $template_content = "Chat";
    break;
}
