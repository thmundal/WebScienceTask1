<?php

/**
 * Model containing data about a chat handle.
 * A chat handle is a resource connecting chat messages to a session between two users or a group
 *
 * @extends db_object
 */

class ChatHandle extends db_object {
    protected static $table = "chat_handles";

    /**
     * Returns the chat handle between two users
     * @param  int $a User A
     * @param  int $b User B
     * @return ChatHandle
     */
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

    /**
     * Retrieves all messages for this chat handle with intention of sending them to client
     * @deprecated
     * @return ChatMessage[] Array of chat messages
     */
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

    /**
     * Receive and save a chat message to the database
     * @deprecated
     * @param  string       $message    The message text
     * @param  int          $sender     ID of the user that sent the message
     * @return ChatMessage              The saved message
     */
    public function receiveMessage($message, $sender) {
        $msg = new ChatMessage();
        $msg->set(["chat_handle" => $this->get("id"), "message" => htmlspecialchars($message), "sender" => $sender]);
        $msg->save();
        return $msg;
    }
}

/**
 * A model for a chat message
 * @extends db_object
 */
class ChatMessage extends db_object {
    protected static $table = "chat_messages";

    /**
     * Get the name of the user that sent this message
     * @return string Name of sender
     */
    public function sender() {
        if($this->get("sender") != 0) {
            $sender = User::Load($this->get("sender"));
            $profile = $sender->getProfile();
            return $profile->get("first_name");
        }

        return null;
    }
}
