<?php

class db_object {
    public static $connection;
    protected static $table;
    protected $attributes = [];
    protected $state;

    public function __construct() {
        $this->state = db_object_state::NEW_OBJECT;
    }

    public static function Load($id) {
        $object = new static();
        $object->state = db_object_state::EXISTING_OBJECT;
    }

    public static function Init(Mysqli $connection) {
        static::$connection = $connection;
    }

    protected function set($attr, $val = "") {
        if(is_array($attr)) {
            foreach($attr as $key => $val) {
                $this->attributes[$key] = $val;
            }
        } else {
            $this->attrbutes[$attr] = $val;
        }
    }

    public function get($attr) {
        if(array_key_exists($attr, $this->attributes)) {
            return $this->attributes[$attr];
        }

        return null;
    }

    public function save() {
        $attrs = [];

        foreach($this->attributes as $key => $val) {
            $attrs[] = "`".$key."`"."=\"".$val."\"";
        }

        if($this->state == db_object_state::NEW_OBJECT) {
            $sql = "INSERT INTO ".static::$connection->real_escape_string(static::$table)."(".implode(",", array_keys($this->attributes)).") VALUES(".implode(",", array_fill(0, sizeof($this->attributes), "?")).")";
            $query = static::$connection->prepare($sql);

            if($query) {
                $query->bind_param(implode("", array_fill(0, sizeof($this->attributes), "s")), ...array_values($this->attributes));
                return $query->execute();
            } else {
                throw new Exception("Error preparing query: " . static::$connection->error . "\n" . '"'.implode("\",\"", array_values($this->attributes)).'"' . "\n" . $sql);
            }
        } else {
            $query = static::$connection->prepare("UPDATE ? SET ?;");

        }

        return false;
    }
}

abstract class db_object_state {
    const NEW_OBJECT = 0;
    const EXISTING_OBJECT = 1;
}
