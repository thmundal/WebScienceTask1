<?php

function exhandle($e, $fatal = false) {
    $file = $_SERVER["DOCUMENT_ROOT"] . "/config.ini";
    if(file_exists($file) AND !$fatal) {
        if (function_exists("production") && production()) {
            $css = "<style>body{background: url('content/img/blue_purple_overlay_web.jpg') no-repeat center center fixed; background-size: cover;}h1,p{color: #fff;font-family:sans-serif;}</style>";
            echo $css;

            echo "<h1>500 - Internal server error</h1>";
            echo "<p>En melding har blitt sendt til administrator.</p>";

            exit();
        }
    }

    echo "<h1>Uncaught exception</h1>";

    echo '<script>function toggle(id){var e=document.getElementById(id);if(e.style.display==""){e.style.display="none";}else{e.style.display="";}}</script>';
    echo "<pre>";
    echo "Execution point: ";
    echo $e->getFile() . " line ".$e->getLine()."\n\nMessage: ";
    echo $e->getMessage();

    $trace = $e->getTrace();

    foreach($trace as $i => $t) {
        if (isset($t["file"]) AND $t["file"] != __FILE__) {
            echo "\n#".$i." ".$t["file"]." at line ".$t["line"];
            echo " <a href='#' onclick=\"toggle('ex_arg_".$i."')\" >".$t["function"]."</a>";

            echo "<pre id='ex_arg_".$i."' style='margin-left:100px;display:none;'>";
            if(isset($t["args"]) AND $i > 1) {
                echo "\nArguments: ";
                print_r($t["args"]);
            }
            echo "</pre>";
        }
    }

    echo "\n\n";
    //print_r($trace);
    echo "</pre>";
}

function pt_error_handler($type, $data) {
    if (ob_get_level()) {
        ob_clean();
    }

    echo "<!DOCTYPE html><html><body>";

    switch ($type) {
        case "exception":
            exhandle($data);
        break;

        case "error":
            $ex = new ExceptionHandler($data->errstr, $data->errno, $data->errline, $data->errfile);
            throw $ex;
        break;

        case "fatal_error":
            echo "<h1>Fatal error</h1>";
            $ex = new ExceptionHandler($data->errstr, $data->errno, $data->errline, $data->errfile);
            exhandle($ex, true);
            exit();
        break;

        default:
            exit("An unknown error occured " . $type);
        break;
    }

    echo "</html></body>";
    exit();
}

function errhandle($errno, $errstr, $errfile, $errline) {
    throw new Exception($errstr);
}

set_exception_handler(function($e) {
   pt_error_handler("exception", $e);
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    pt_error_handler("error", (object) [
        "errno" => $errno,
        "errstr" => $errstr,
        "errfile" => $errfile,
        "errline" => $errline
    ]);
});

register_shutdown_function(function() {
    $err = error_get_last();

    if ($err) {
        pt_error_handler("fatal_error", (object) [
            "errno" => $err["type"],
            "errstr" => $err["message"],
            "errfile" => $err["file"],
            "errline" => $err["line"]
        ]);

        return false;
    }

    return false;
});
