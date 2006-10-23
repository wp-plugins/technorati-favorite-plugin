<?php
/*
Plugin Name: Technorati Blog Roll
Plugin URI: http://blog.mericson.com/technorati-favorite-plugin/
Description: This will include your favorites from Technorati
Author: Matt Ericson
Version: 2.00
Author URI: http://blog.mericson.com/

INSTRUCTIONS
============

This version uses wordpress sidebar widget

Just place this file in your plugins directory then enable it

Click on "Presentation" and then "Sidebar Widgets"

Drag this over to your side bar hit the configure button 
enter your user name and you are done

This plugin will use the Wordpress 2.0 Caching system

*/

function faves_list_init() {
    // Check for the required API functions
    if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') ) {
        return;
    }

    function faves_list_contol () {
        $options = $newoptions = get_option('widget_faves_list');
        if ( $_POST['faves-list-submit'] ) {
            $newoptions['user']   = strip_tags(stripslashes($_POST['faves-list-user']));
            $newoptions['type']   = strip_tags(stripslashes($_POST['faves-list-type']));
            $newoptions['title']  = strip_tags(stripslashes($_POST['faves-list-title']));
        }
        if ( $options != $newoptions ) {
            $options = $newoptions;
            update_option('widget_faves_list', $options);
        }
       ?>
        <div style="text-align:right">
             <label for="faves-list-title" style="line-height:35px;display:block;">Title: <input type="text" id="faves-list-title" name="faves-list-title" value="<?php echo htmlspecialchars($options['title']); ?>" /></label>
             <label for="faves-list-user" style="line-height:35px;display:block;">User: <input type="text" id="faves-list-user" name="faves-list-user" value="<?php echo htmlspecialchars($options['user']); ?>" /></label>
             <label for="faves-list-type" style="line-height:35px;display:block;">Display Type: 
             <select id="faves-list-type" name="faves-list-type"> 
             <option value="ul"  <? if ($options['type'] == 'ul' ) {?> selected  <? } ?> >Unorderd List</option>
             <option value="ol"  <? if ($options['type'] == 'ol' ) {?> selected  <? } ?> >Orderd List</option>
             </select>
             </label>
             <input type="hidden" name="faves-list-submit" id="faves-list-submit" value="Save" />
             <input type="submit" value="Save" />
        </div>
   <?php
    }
    function technoratiFavoriteList() {


        $options = (array) get_option('widget_faves_list');

        $technorati_user   = $options['user'];
        $type              = $options['type'];
        $title             = $options['title'];
        $cache_time        = $options['cache_time'];


        if (!$technorati_user) {
            return;
        }

        if (!$type) {
            $type = "ul";
        }

        if (! isset($cache_time)) {
            $cache_time = 600;
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
        echo "<h2>$title</h2>\n";
        echo "<ul>";

        echo $data;
        echo "</ul>";

    }
    register_sidebar_widget('Technorati Faves', 'technoratiFavoriteList');
    register_widget_control('Technorati Faves', 'faves_list_contol');
}

add_action('plugins_loaded', 'faves_list_init');
?>
