<?php

// Route API requests different
if(strpos(get_route($config["root_url"]), "/api") > -1) {
    require_once("lib/php/api.php");
    exit;
}

switch(get_route($config["root_url"])) {
    case "/":
        $template_content = template("content/html/user_index.html", ["username" => $user->get("username")]);
    break;

    default:
        $url = get_route($config["root_url"]);

        if($profile = UserProfile::FindByUrl($url)) {
            $template_content = template("content/html/user_profile.html", ["profile" => $profile]);
        } else {
            $template_content = "User not found: " . $url;
        }

    break;

    case "/logout":
        $template_content = "Logging out...";
        User::Logout();
        redirect($config["root_url"]);
    break;

    case "/users":
        $users_list = User::GetList();

        foreach($users_list as $key => $val) {
            if($val->get("id") == $user->get("id")) {
                unset($users_list[$key]);
            }
        }
        
        $template_content = template("content/html/user_list.html", ["list" => $users_list]);
    break;

    case "/chat":
        $partner = User::Load($_GET["user"]);
        $partner_profile = $partner->getProfile();
        $chat_handle = ChatHandle::getByParticipants($user->get("id"), $partner->get("id"));

        $template_content = template("content/html/chat_layout.html", ["partner_profile" => $partner_profile, "chat_handle" => $chat_handle->get("id")]);
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

            $profile_image = $_FILES["profile_image"];

            if(!empty($profile_image["name"])) {
                $profile->saveProfileImage($profile_image);
            }


            if(!$profile->error) {
                $profile->save();
                redirect("profile");
            } else {
                echo $profile->error;
            }

        } else {
            $profile = $user->getProfile();

            if($profile) {
                $template_content = template("content/html/create_profile.html", ["form_action" => "edit-profile", "profile" => $profile]);
            } else {
                redirect("create-profile");
            }
        }
    break;
}
