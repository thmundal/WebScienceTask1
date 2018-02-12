<?php

class ChatHandle extends db_object {
    protected static $table = "chat_handles";

    public static function getByParticipants($a, $b) {
        //$sql = "SELECT id FROM ".static::$connection->real_escape_string(static::$table)." WHERE a IN (?) AND b IN (?)";
        //$query = static::$connection->prepare($sql);
        $condition = static::$connection->real_escape_string($a).",".static::$connection->real_escape_string($b);

        /*$query->bind_param("ss", $condition, $condition);

        $query->execute();
        //$query->store_result();
        $query->bind_result($id);
        //$query->fetch();
        //pre_print_r($query);
        //return;

        if($query->error) {
            throw new Exception($query->error);
        }


        while($query->fetch()) {
            pre_print_r($id);
            if($id != null) {
                return ChatHandle::Load($id);
            }
        }

        $query->close();*/

        $b = static::$connection->query("SELECT * FROM ".static::$table." WHERE a IN (".$condition.") AND b IN (".$condition.") LIMIT 1;");

        if($b->num_rows > 0) {
            $row = $b->fetch_array();
            return ChatHandle::Load($row["id"]);
        }

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
        $msg->set("sender", $msg->sender());
        return $msg;
    }
}

class ChatMessage extends db_object {
    protected static $table = "chat_messages";

    public function sender() {
        if($this->get("sender") != 0) {
            $sender = User::Load($this->get("sender"));
            $profile = $sender->getProfile();
            if($profile)
                return $profile->get("first_name");
        }

        return null;
    }
}
