<?php
/**
 * A parent class to represent an object that is saved to or loaded from the database
 * @author Thomas Mundal<thmundal@gmail.com>
 */
abstract class db_object {
    public static $connection;
    protected static $table;
    protected $attributes = [];
    protected $state;

    /**
     * Constructor
     */
    public function __construct() {
        $this->state = db_object_state::NEW_OBJECT;
    }

    /**
     * Load item from database
     * @param int $id The ID as a reference to the object in database
     */
    public static function Load($id) {
        $object = new static();
        $object->state = db_object_state::EXISTING_OBJECT;

        $query = static::$connection->prepare("SELECT * FROM " . static::table() . " WHERE id=? LIMIT 1");

        if($query) {
            $query->bind_param("i", $id);
            $query->execute();
            $result = $query->get_result();

            foreach($result as $row) {
                $object->set($row);
            }
        } else {
            throw new Exception(static::$connection->error);
        }

        return $object;
    }

    /**
     * Initialize a new database object
     * @param Mysqli $connection Database connection handle
     * @return db_object A new instance of this class, or class that inherits from this
     */
    public static function Init() {
        return new static();
    }

    /**
     * Get a list of all objects of this type saved in database
     */
    public static function GetList() {
        $query = static::$connection->prepare("SELECT id FROM " . static::table());
        $query->execute();
        $result = $query->get_result();

        $out = [];
        foreach($result as $row) {
            $out[] = static::Load($row["id"]);
        }

        return $out;
    }

    /**
     * Set an attribute, or a set of attributes
     * @param mixed $attr   Either the name of the attribute to set, or a key => value pair list of attributes
     * @param string $val   Only required when $attr is a string, and will represent the value for the attribute
     */
    public function set($attr, $val = "") {
        if(is_array($attr)) {
            foreach($attr as $key => $val) {
                $this->attributes[$key] = $val;
            }
        } else {
            $this->attributes[$attr] = $val;
        }
    }

    /**
     * Get the value of a given attribute
     * @param  string $attr Name of the attribute whos value is to be returned
     * @return mixed        The value of the named attribute
     */
    public function get($attr) {
        if(array_key_exists($attr, $this->attributes)) {
            return htmlspecialchars($this->attributes[$attr]);
        }

        return null;
    }

    /**
     * Save the object's state to the database
     * @return bool True if save succeeded, false otherwise
     * @throws Exception if mysql query error occurs
     */
    public function save() {
        $attrs = [];

        foreach($this->attributes as $key => $val) {
            $attrs[] = "`".$key."`"."=\"".$val."\"";
        }

        if($this->state == db_object_state::NEW_OBJECT) {
            // TODO: Potential SQL injection in parameter names part of following query
            $sql = "INSERT INTO ".static::$connection->real_escape_string(static::$table)."(".implode(",", array_keys($this->attributes)).") VALUES(".implode(",", array_fill(0, sizeof($this->attributes), "?")).")";
            $query = static::$connection->prepare($sql);

            if($query) {
                $query->bind_param(implode("", array_fill(0, sizeof($this->attributes), "s")), ...array_values($this->attributes));
                $query->execute();

                if($query->error) {
                    throw new Exception($query->error);
                }
                $this->set("id", static::$connection->insert_id);
                return $query;
            } else {
                throw new Exception("Error preparing query: " . static::$connection->error . "\n" . '"'.implode("\",\"", array_values($this->attributes)).'"' . "\n" . $sql);
            }
        } else {
            $attrs = $this->attributes;
            $id = $this->get("id");
            unset($attrs["id"]);
            $vals = [];
            foreach($attrs as $key => $val) {
                $vals[] = static::$connection->real_escape_string($key)."= ? ";
            }

            $sql = "UPDATE ".static::$connection->real_escape_string(static::$table)." SET ".implode(",", $vals)." WHERE id=".$id;
            $query = static::$connection->prepare($sql);

            if($query) {
                $query->bind_param(implode("", array_fill(0, sizeof($attrs), "s")), ...array_values($attrs));
                $query->execute();

                if($query->error) {
                    throw new Exception($query->error);
                }

                return $query;
            } else {
                throw new Exception("Error preparing query: " . static::$connection->error . "\n");
            }
        }

        return false;
    }

    /**
     * Return table name of this object
     * @return string Table name
     */
    public static function table() {
        return static::$connection->real_escape_string(static::$table);
    }

    /**
     * Return the attributes array for this object
     * @return array
     */
    public function attributes() {
        return $this->attributes;
    }
}

/**
 * Abstract class containing constant values for the different save states of an object
 */
abstract class db_object_state {
    const NEW_OBJECT = 0;
    const EXISTING_OBJECT = 1;
    const MODIFIED = 2;
}
