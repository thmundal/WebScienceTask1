<?php
/**
 * Deprecated functionality after implementing Node.js chat handling
 * These backends may not provide data as expected
 */
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

            if($chathandle) {
                $response->respond($chathandle->attributes());
            } else {
                $chathandle = $user->createChatHandle($_POST["partner"]);
                $response->respond($chathandle->attributes());
                $response->console("Could not find chat handle, created one");
            }
        break;

        case "/chat-handle-poll":
            $chathandle = $user->verifyChatHandle($_POST["id"]);

            if($chathandle) {
                $response->respond($chathandle->sendMessages());
            } else {
                $response->console("not a valid user chathandle");
            }
        break;

        case "/chat-handle-receive":
            $chathandle = $user->verifyChatHandle($_POST["handle"]["id"]);

            if($chathandle) {
                // The following variable does not hold a message object?
                $msg = $chathandle->receiveMessage($_POST["message"], $user->get("id"));
                $response->console("Saved message");
                $response->respond(["handle" => $chathandle->attributes(), "message" => $msg->attributes()]);
            }
        break;
    }
} catch(Exception $e) {
    ob_end_clean();
    $response->error([$e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace()]);
}

$response->flush();
