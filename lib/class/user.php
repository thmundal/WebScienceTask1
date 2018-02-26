<?php
Class User extends db_object {
    protected static $table = "users";

    public function __construct() {

    }

    public static function Login($username, $password) {
        global $memcached;

        $query = static::$connection->prepare("SELECT id,password FROM ".static::$connection->real_escape_string(static::$table)." WHERE username = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $query->store_result();

        $query->bind_result($id, $pw);
        $query->fetch();

        if(password_verify($password, $pw)) {
            $_SESSION["login"] = $id;
            $cached_users = $memcached->get("usn:php:user");

            if(empty($cached_users)) {
                $cached_users = json_encode([session_id() => $id]);
            } else {
                $cached_users = json_decode($cached_users);
                $cached_users->{session_id()} = $id;
                $cached_users = json_encode($cached_users);
            }
            $memcached->set("usn:php:user", $cached_users);
            return true;
        }

        return false;
    }

    public static function LoggedIn() {
        return isset($_SESSION["login"]);
    }

    public static function Logout() {
        $_SESSION["login"] = null;
        unset($_SESSION["login"]);
    }

    public static function Encrypt($str) {
        return password_hash($str, PASSWORD_BCRYPT);
    }

    public static function Exists($username) {
        $query = static::$connection->prepare("SELECT id FROM ".static::$connection->real_escape_string(static::$table)." WHERE username = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $query->store_result();

        return $query->num_rows > 0;
    }

    public static function Register($username, $password) {
        echo "REGISTER PROCEDURE";

        $user = new static();
        $user->set(["username" => $username, "password" => User::Encrypt($password)]);
        return $user->save();
    }

    public function getProfile() {
        $query = static::$connection->prepare("SELECT id FROM ".UserProfile::table()." WHERE user=?");
        $query->bind_param("i", $this->attributes["id"]);
        $query->execute();
        $query->store_result();
        $query->bind_result($id);
        $query->fetch();

        if(!is_null($id))
            return UserProfile::Load($id);

        return null;
    }

    public function getChatHandle($partner) {
        return ChatHandle::getByParticipants($this->get("id"), $partner);
    }

    public function verifyChatHandle($id) {
        $handle = ChatHandle::Load($id);

        if($handle) {
            if($handle->get("a") == $this->get("id") OR $handle->get("b") == $this->get("id")) {
                return $handle;
            }
        }

        return false;
    }

    public function createChatHandle($partner) {
        $handle = new ChatHandle();
        $handle->set(["a" => $this->get("id"), "b" => $partner]);
        $handle->save();

        $thathandle = $this->getChatHandle($partner);
        return $thathandle;
    }
}
