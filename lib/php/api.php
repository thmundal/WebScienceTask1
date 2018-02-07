<?php
require_once("lib/class/api.php");
header("content-type: application/json; charset=utf-8");
$response = Api::Init();

if(!User::loggedIn()) {
    exit;
}

try {
    switch(get_route($config["root_url"] . "/api")) {
        default:
        break;

        case "/chat-session":
            $chathandle = $user->getChatHandle($_POST["partner"]);
            $response->console($chathandle);
        break;
    }
} catch(Exception $e) {
    ob_end_clean();
    $response->error([$e->getMessage(), $e->getFile(), $e->getLine()]);
}

$response->flush();
