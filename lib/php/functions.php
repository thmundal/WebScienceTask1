<?php

function template($file, Array $attrs = []) {
    $content = file_get_contents($file);

    foreach($attrs as $key => $val) {
        $content = str_replace("%".$key."%", $val, $content);
    }

    return $content;
}

function pre_print_r($content) {
    echo "<pre>".print_r($content, true)."</pre>";
}

function get_route($root = "") {
    $path = str_replace($root, "", $_SERVER["REQUEST_URI"]);

    return $path;
}

function redirect($url) {
    header("location: " . $url);
}
