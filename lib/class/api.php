<?php
/**
 * Model for handling API requests
 */
class Api {
    private $response;

    /**
     * Constructor, initializes default response format
     */
    public function __construct() {
        $this->response = [
            "status" => 403,
            "body" => "Forbidden",
            "error" => false,
            "errmsg" => "",
            "console" => ""
        ];
    }

    /**
     * Static alias for constructor
     */
    public static function Init() {
        return new self();
    }

    /**
     * Set a parameter of response data object
     * @param string $attr  Name of the parameter
     * @param mixed $value Value for the parameter
     * @return void
     */
    public function set($attr, $value = null) {
        if(is_array($attr)) {
            foreach($attr as $key => $val) {
                $this->response[$key] = $val;
            }
        } else {
            $this->response[$attr] = $value;
        }
    }

    /**
     * Set response body
     * @param  mixed $data The value of response body
     * @return void
     */
    public function respond($data) {
        $this->set(["status" => 200, "body" => $data]);
    }

    /**
     * Set a value intended to be logged as a console message on the client
     * @param  mixed $data Data to be logged
     * @return void
     */
    public function console($data) {
        if(is_a($data, "db_object")) {
            $data = $data->attributes();
        }

        $this->set(["console" => $data]);
    }

    /**
     * Set error information intended to express an error on the client
     * @param  string $data Error message
     * @return void
     */
    public function error($data) {
        $this->set(["error" => true, "errmsg" => $data]);
    }

    /**
     * Flush response data to client
     * @return void
     */
    public function flush() {
        echo json_encode($this->response);
    }
}
