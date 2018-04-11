<?php
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
                    if(User::register($_POST["username"], $_POST["password"], $_POST["email"])) {
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
    } elseif(get_route($config["root_url"]) == "/forgot-password") {
        $error = false;
        $errors = [];

        if($_POST) {
            if(arrGet($_GET, "save", false) == "1") {
                if($token = arrGet($_POST, "token", false)) {
                    if($user_id = User::validatePasswordToken($token)) {
                        // Match passwords
                        if(arrGet($_POST, "password") == arrGet($_POST, "password_repeat")) {
                            echo "passwords match, continue";
                            $new_password = User::Encrypt(arrGet($_POST, "password"));
                            $_user = User::Load($user_id);
                            $_user->set("password", $new_password);
                            $_user->save();
                            redirect($config["root_url"]);
                        }
                    } else {
                        $template_content = "Token invalid";
                    }
                } else {
                    $template_content = "No token provided";
                }
            } else {
                if(User::Exists($_POST["username"])) {
                    $_user = User::LoadByUsername($_POST["username"]);
                    $token = $_user->createPasswordToken();

                    $message = template("content/html/emails/forgot_password.html", ["token" => $token]);
                    if(!empty($_user->get("email"))) {
                        if(mail($_user->get("email"), "Reset password", $message)) {
                            $template_content = "Password reset email is sendt, check your inbox. And probably your spam filter as well";
                        } else {
                            
                        }
                    } else {
                        $template_content = "User is registered without email adress, cannot send email.<br />" . $message;
                    }
                } else {
                    $error = true;
                    $errors[] = "Username not found";
                }
            }
        } else if($token = arrGet($_GET, "token", false)) {
            if(User::validatePasswordToken($token)) {
                $template_content = template("content/html/reset_password.html", ["token" => $token]);
            } else {
                $template_content = "Cannot validate password token";
            }
        } else {
            $template_content = template("content/html/forgot_password.html", ["error" => $error, "errors" => $errors]);
        }
    } else {
        if($_POST) {
            if(User::Login($_POST["username"], $_POST["password"])) {
                redirect($config["root_url"]);
            } else {
                $template_content = "Login feilet";
            }
        } else {
            $template_content = template("content/html/login.html", ["referer" => ""]);
        }
    }
