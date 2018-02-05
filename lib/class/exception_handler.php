<?php
Class ExceptionHandler extends Exception {
    public function __construct($message, $code = 0, $line = 0, $file = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->line = $line;
        $this->file = $file;
    }
}
