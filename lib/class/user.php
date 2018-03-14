<?php
/**
 * Model for a user of this system
 * @extends db_object
 */
Class User extends db_object {
    protected static $table = "users";

    public function __construct() {

    }

    /**
     * Validate a user for logging in to this system
     * @param string $username User's username
     * @param string $password User's password
     */
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

            // Caching of user session id's was used in a previous version of the node.js chat server
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

    /**
     * Check if a user is currently logged in using the client's session
     * @return boolean
     */
    public static function LoggedIn() {
        return isset($_SESSION["login"]);
    }

    /**
     * Perform logout procedure on this user
     */
    public static function Logout() {
        $_SESSION["login"] = null;
        unset($_SESSION["login"]);
    }

    /**
     * Rules for encrypting user password
     * @param string $str Password to be encrypted
     */
    public static function Encrypt($str) {
        return password_hash($str, PASSWORD_BCRYPT);
    }

    /**
     * Check if a user with the given username exists
     * @param string $username Username to check
     */
    public static function Exists($username) {
        $query = static::$connection->prepare("SELECT id FROM ".static::$connection->real_escape_string(static::$table)." WHERE username = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $query->store_result();

        return $query->num_rows > 0;
    }

    /**
     * Save a user to the database
     * @param string $username Username
     * @param string $password User password
     */
    public static function Register($username, $password) {
        $user = new static();
        $user->set(["username" => $username, "password" => User::Encrypt($password)]);
        return $user->save();
    }

    /**
     * Get the user profile for this user
     * @return UserProfile
     */
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

    /**
     * Get the chat handler between this user and a given partner user id
     * @deprecated
     * @param  int          $partner User ID of partner
     * @return ChatHandle
     */
    public function getChatHandle($partner) {
        return ChatHandle::getByParticipants($this->get("id"), $partner);
    }

    /**
     * Veridy that this user is one of the owners of the chathandle with given id
     * @deprecated
     * @param  int $id ID of the chat handle
     * @return ChatHandle
     */
    public function verifyChatHandle($id) {
        $handle = ChatHandle::Load($id);

        if($handle) {
            if($handle->get("a") == $this->get("id") OR $handle->get("b") == $this->get("id")) {
                return $handle;
            }
        }

        return false;
    }

    /**
     * Create a new chat handle between this user and partner
     * @deprecated
     * @param  int $partner User ID for the partner
     * @return void
     */
    public function createChatHandle($partner) {
        $handle = new ChatHandle();
        $handle->set(["a" => $this->get("id"), "b" => $partner]);
        $handle->save();

        $thathandle = $this->getChatHandle($partner);
        return $thathandle;
    }
}
