<?php

class ChatHandle extends db_object {
    protected static $table = "chat_handles";

    public static function getByParticipants($a, $b) {
        $query = static::$connection->prepare("SELECT id FROM chat_handles WHERE (a=? AND b=?) XOR (a=? AND b=?) LIMIT 1");
        $query->bind_param("dddd", $b, $a, $a, $b);

        $query->execute();
        $query->bind_result($id);
        $query->fetch();
        $query->close();

        return ChatHandle::load($id);


        return false;
    }

    public function sendMessages() {
        $sql = "SELECT id FROM ".ChatMessage::table()." WHERE chat_handle = ?";
        $query = static::$connection->prepare($sql);
        $query->bind_param("i", $this->attributes["id"]);
        $query->execute();
        $result = $query->get_result();

        $out = [];
        foreach($result as $row) {
            $m = ChatMessage::Load($row["id"]);
            $attrs = $m->attributes();
            $attrs["sender"] = $m->sender();
            $out[] = $attrs;
        }

        return $out;
    }

    public function receiveMessage($message, $sender) {
        $msg = new ChatMessage();
        $msg->set(["chat_handle" => $this->get("id"), "message" => htmlspecialchars($message), "sender" => $sender]);
        $msg->save();
        return $msg;
    }
}

class ChatMessage extends db_object {
    protected static $table = "chat_messages";

    public function sender() {
        if($this->get("sender") != 0) {
            $sender = User::Load($this->get("sender"));
            $profile = $sender->getProfile();
            return $profile->get("first_name");
        }

        return null;
    }
}
