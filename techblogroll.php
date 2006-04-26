<?php
/*
Plugin Name: Technorati Blog Roll
Plugin URI: http://blog.mericson.com/technorati-favorite-plugin/
Description: This will inlude your favorites from Technorati
Author: Matt Ericson
Version: 0.01
Author URI: http://blog.mericson.com/

INSTRUCTIONS
============

Add the following code to your template where you want your favorites to show up

<?php  technoratiFavoriteList("<username>"); ?>

Or you can do this if you want an un ordered list

<?php  technoratiFavoriteList("<username>", "ul"); ?>

*/

function technoratiFavoriteList($technorati_user, $type="ol", $cache_time = 600, $cache_file = null) {

    if (!$technorati_user) {
        return;
    }
    if (strtolower($type) == "ol") {
        $t =  "ol";
    } elseif (strtolower($type) == "ul") {
        $t = "ul";
    } else {
        $t = "ol";
    }
    $api_url = "http://feeds.technorati.com/faves/" . $technorati_user . "?type=list&format=xoxo&t=$t";
    if ($cache_file == null) {
        $cache_file  =  "/tmp/techblogroll.$technorati_user.$cache_time.cache";
    }

    $cache_file_tmp = "$cache_file.tmp";

    $time = split(" ", microtime());
    srand((double)microtime()*1000000);

    $cache_time_rnd = 30 - rand(0, 60);
    if (
    !file_exists($cache_file)
    || !filesize($cache_file) > 20
    || ((filemtime($cache_file) + $cache_time - $time[1]) + $cache_time_rnd < 0)
    || (filemtime(__FILE__) > filemtime($cache_file))
    ) {
        $c = curl_init($api_url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($c, CURLOPT_TIMEOUT, 4);
        curl_setopt($c, CURLOPT_USERAGENT, "Technorati blogroll plugin");
        $response = curl_exec($c);
        $info = curl_getinfo($c);

        $curl_error_code = $info['http_code'];
        curl_close($c);
        if ($curl_error_code == 200) {
            $fpwrite = fopen($cache_file_tmp, 'w');
            if ($fpwrite){
                fputs($fpwrite, $response);
                fclose($fpwrite);
                rename($cache_file_tmp, $cache_file);
            }
        }
        if ((file_exists($cache_file)) && filesize($cache_file) > 20)  {
            echo $response;

        } elseif ($curl_error_code) {
            //do something here
        } else {
            //do something here
        }
    } else {
        echo file_get_contents($cache_file);
    }
}

?>
