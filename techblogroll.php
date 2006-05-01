<?php
/*
Plugin Name: Technorati Blog Roll
Plugin URI: http://blog.mericson.com/technorati-favorite-plugin/
Description: This will inlude your favorites from Technorati
Author: Matt Ericson
Version: 1.00
Author URI: http://blog.mericson.com/

INSTRUCTIONS
============

Add the following code to your template where you want your favorites to show up

<?php  technoratiFavoriteList("<username>"); ?>

Or you can do this if you want an un ordered list

<?php  technoratiFavoriteList("<username>", "ul"); ?>

This plugin will use the Wordpress 2.0 Caching system 
*/

function technoratiFavoriteList($technorati_user, $type="ol", $cache_time = 600) {

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

    $cacheKey = "techblogroll.$technorati_user.$type";

    wp_cache_init();
    $data = wp_cache_get($cacheKey);
    if (!$data) {

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
            $data = $response;
            wp_cache_set($cacheKey,$data,'',$cache_time);
            wp_cache_close();
        } elseif ($curl_error_code) {
            //do something here
        } else {
            //do something here
        }
    }
    echo $data;
}

?>
