<?php
class Api {
    private $response;

    public function __construct() {
        $this->response = [
            "status" => 403,
            "body" => "Forbidden",
            "error" => false,
            "errmsg" => "",
            "console" => ""
        ];
    }

    public static function Init() {
        return new self();
    }

    public function set($attr, $value = null) {
        if(is_array($attr)) {
            foreach($attr as $key => $val) {
                $this->response[$key] = $val;
            }
        } else {
            $this->response[$attr] = $value;
        }
    }

    public function respond($data) {
        $this->set(["status" => 200, "body" => $data]);
    }

    public function console($data) {
        if(is_a($data, "db_object")) {
            $data = $data->attributes();
        }

        $this->set(["console" => json_encode($data)]);
    }

    public function error($data) {
        $this->set(["error" => true, "errmsg" => $data]);
    }

    public function flush() {
        echo json_encode($this->response);
    }
}
