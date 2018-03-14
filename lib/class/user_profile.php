<?php
/**
 * Model for a profile belonging to a user of this system
 * @extends db_object
 */
class UserProfile extends db_object {
    protected static $table = "profiles";
    public $error = false;

    /**
     * Saves a profile image for this profile
     * @param  array $file Array containing $_FILES request data from client
     * @return void
     */
    public function saveProfileImage($file) {
        global $config;

        $valid_mime = ["image/png" => "png", "image/jpg" => "jpg", "image/jpeg" => "jpeg"];

        if(in_array($file["type"], array_keys($valid_mime))) {
            $extension = $valid_mime[$file["type"]];
            $savename = time() . "-" . $this->get("first_name") . "." . $extension;
            $savepath =  $config["upload_path"] . $savename;

            $this->set("profile_image", $savename);
            move_uploaded_file($file["tmp_name"], $savepath);
        } else {
            $this->error = "Fileformat not recognized";
        }

    }

    /**
     * Get correct image url for profile image for dispaying in html at the client
     * @return string   Correct profile image url
     */
    public function image() {
        global $config;
        return $config["upload_url"] . $this->get("profile_image");
    }

    /**
     * Find a user profile based on custom profile URL. Falling back to check ID if url is not present
     * @example UserProfile::FindByUrl("thomas.mundal");
     * @param string $url Custom URL or ID for this profile
     */
    public static function FindByUrl($url) {
        $query = static::$connection->prepare("SELECT id FROM ".static::$table." WHERE url=? OR id=?");
        $_id = str_replace("/", "", $url);
        $query->bind_param("ss", $url, $_id);
        $query->execute();
        $query->store_result();
        $query->bind_result($id);
        $query->fetch();

        if(!is_null($id)) {
            return static::Load($id);
        }

        return false;
    }

    /**
     * Return the URL to access this user profile, using custom URL if present
     * @return string URL to access this user profile.
     */
    public function url() {
        global $config;
        if(empty($this->get("url")))
            $this->set("url", "/" . $this->get("id"));

        return $config["root_url"] . $this->get("url");
    }
}
