<?php

class ChatHandle extends db_object {
    protected static $table = "chat_handles";

    public static function getByParticipants($a, $b) {
        $sql = "SELECT id FROM ".static::$connection->real_escape_string(static::$table)." WHERE a IN (?) OR b IN (?)";
        $query = static::$connection->prepare($sql);
        $condition = $a.",".$b;

        $query->bind_param("ss", $condition, $condition);

        $query->execute();
        $query->store_result();
        $query->bind_result($id);
        $query->fetch();

        if($id !== null) {
            return ChatHandle::Load($id);
        }
        return $id;
    }
}
