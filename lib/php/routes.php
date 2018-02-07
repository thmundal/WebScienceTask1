<?php

// Route API requests different
if(strpos(get_route($config["root_url"]), "/api") > -1) {
    require_once("lib/php/api.php");
    exit;
}

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
        $users_list = User::GetList();
        $template_content = template("content/html/user_list.html", ["list" => $users_list]);
    break;

    case "/chat":
        $partner = User::Load($_GET["user"]);
        $partner_profile = $partner->getProfile();

        $template_content = template("content/html/chat_layout.html", ["partner_profile" => $partner_profile]);
    break;

    case "/profile":
        $profile = $user->getProfile();

        if(is_null($profile)) {
            redirect("create-profile");
        }
        $template_content = template("content/html/user_profile.html", ["profile" => $profile]);
    break;

    case "/create-profile":
        if($_POST) {
            $profile = new UserProfile();
            $profile->set($_POST);
            $profile->set("user", $user->get("id"));
            $profile->save();
            redirect("profile");
        } else {
            $template_content = template("content/html/create_profile.html", ["form_action" => "create-profile"]);
        }
    break;

    case "/edit-profile":
        if($_POST) {
            $profile = $user->getProfile();
            $profile->set($_POST);
            $profile->save();

            redirect("profile");
        } else {
            $template_content = template("content/html/create_profile.html", ["form_action" => "edit-profile", "profile" => $user->getProfile()]);
        }
    break;
}
