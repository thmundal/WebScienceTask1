<?php

function template($file, Array $attrs = []) {
    /*$content = file_get_contents($file);

    foreach($attrs as $key => $val) {
        $content = str_replace("%".$key."%", $val, $content);
    }

    return $content;*/
    global $smarty;
    $smarty->assign($attrs);
    return $smarty->fetch($file);
}

function pre_print_r($content) {
    echo "<pre>".print_r($content, true)."</pre>";
}

function get_route($root = "") {
    $fixed = str_replace($root, "", $_SERVER["REQUEST_URI"]);

    $query = "";
    if(strpos($fixed, "?") > -1) {
        $query = substr($fixed, strpos($fixed, "?"), strlen($fixed));
        $fixed = str_replace($query, "", $fixed);
    }

    return $fixed;
}

function redirect($url, $force = false) {
    global $config;

    if(get_route($config["root_url"]) != "/".$url OR $force)
        header("location: " . $url);
}

function json($o) {
    return json_encode($o);
}
