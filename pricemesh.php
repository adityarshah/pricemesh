<?php
/*
Plugin Name: Pricemesh - Preisvergleich für Wordpress
Plugin URI: https://www.pricemesh.io/plugins/wordpress/
Description: Mit diesem Plugin ist es möglich Wordpress um einen eigenen Preisvergleich zu erweitern.
Version: 1.0
Author: pricemesh
Author URI: https://www.pricemesh.io
*/

class Render {
    /**
     * Basic ENUM which holds the information, where the HTML part should be injected to the page.
     */

    const IN_CONTENT = 1;
    const AFTER_CONTENT = 2;
    const WIDGET = 3;
}

//*** LOCALIZATION INIT ***//
add_action('init', 'pricemesh_init');

function pricemesh_init() {
    load_plugin_textdomain('pricemesh', false, plugin_basename(dirname(__FILE__).'/locale'));
}

//*** ACTIVATION ***/
register_activation_hook(__FILE__,"pricemesh_install");

function pricemesh_install() {
    global $wp_version;
    if(version_compare($wp_version, "2.9", "<")) {
        deactivate_plugins(basename(__FILE__));
        wp_die(__("Dieses Plugin benötigt Wordpress 2.9, oder neuer.", "pricemesh"));
    }
}

//*** POST HOOK ***//
add_filter("the_content", "add_pricemesh");
function add_pricemesh($content){
    /**
     * Loads the html part of the plugin to be displayed on the current page.
     */

    if(is_single() && is_injection_needed()){
        $injection_point = get_injection_point();
        if($injection_point == Render::IN_CONTENT){
            $content = str_replace("[pricemesh]", inject_html(), $content);
        }else if($injection_point == Render::AFTER_CONTENT){
            $content.= inject_html();
        }
    }else{
        $content = str_replace("[pricemesh]", "", $content);
    }
    return $content;
}

//*** SETTINGS PAGE ***//
add_action("admin_menu", "pricemesh_create_menu");

function pricemesh_create_menu(){
    /**
     * Adds a menu point for the pricemesh settings page
     */

    //create new top-level menu
    add_menu_page(__("Pricemesh Einstellungen"), __("Pricemesh Einstellungen"), "administrator", __FILE__, "pricemesh_settings_page");
    //plugins_url("/images/wordpress.png", __FILE__
    //call register settings function
    add_action( "admin_init", "pricemesh_register_settings" );
}


function pricemesh_register_settings() {
    /**
     * register settings
     */

    register_setting("pricemesh-settings-group", "pricemesh_option_token");
    register_setting("pricemesh-settings-group", "pricemesh_option_country" );
}

function pricemesh_settings_page() {
    /**
     * Loads the settings page
     */
    ?>
    <div class="wrap">
        <h2><?php _e("Pricemesh Plugin", "pricemesh-plugin") ?></h2>
        <p>Erstellen Sie einen Account auf <a href="https://www.pricemesh.io" target="_blank">pricemesh.io</a> und tragen Sie dann ihr eigenes Token ein, um Provisionen für Verkäufe zu erhalten.</br>
            Das Demo-Token ist voll funktionsfähig, es können damit allerdings keine Provisionen verdient werden</p>
        <form method="post" action="options.php">
            <?php settings_fields( "pricemesh-settings-group" ); ?> <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e("Token", "pricemesh-plugin") ?></th>
                    <td>
                        <input type="text" name="pricemesh_option_token" value="<?php echo get_option("pricemesh_option_token", "demo-abcde-demo-12345-demo-abcde1234"); ?>" size="40"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e("Land", "pricemesh-plugin") ?></th>
                    <td>
                        <select name="pricemesh_option_country">
                            <?php $countries = array("de");?>
                            <?php foreach($countries as $country):?>
                                <option <?php if(get_option("pricemesh_option_country", "de") == $country){echo "selected";} ?>>
                                    <?php echo $country;?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            ￼<p class="submit">
                <input type="submit" class="button-primary"
                       value="<?php _e("Änderungen Speichern", "pricemesh-plugin") ?>" /> </p>
        </form> </div> <?php
    }

//*** META_BOX ***//
add_action('admin_init','pricemesh_meta_box_init');
// meta box functions for adding the meta box and saving the data
function pricemesh_meta_box_init(){
    // create our custom meta box
    add_meta_box('pricemesh-meta',__('Pricemesh', 'pricemesh-plugin'), 'pricemesh_meta_box','post','side','default');
    // hook to save our meta box data when the post is saved
    add_action('save_post','pricemesh_save_meta_box');
}

function pricemesh_meta_box($post,$box) {
    /**
     * Loads the meta box
     */

    // retrieve our custom meta box values
    $pids = rtrim(get_post_meta($post->ID,'_pricemesh_pids',true), ",");
    if(!empty($pids)){
        $pids_arr = explode(",", $pids);
    }else{
        $pids_arr = array();
    }

    if(count($pids_arr) > 0){
        $pids = $pids.",";
    }

    // custom meta box form elements
    ?>
    <p>
        <input type="text" id="pricemesh_new_pid" name="pricemesh_new_pid" class="" autocomplete="off" value="">
        <input type="text" id="pricemesh_pids" name="pricemesh_pids" value="<?php echo $pids;?>" style="display:none;visibility: hidden;">
        <input type="button" class="button tagadd" value="Add" id="pricemesh_add_new_pid_btn">
    </p>

    <div class="tagchecklist" id="pricemesh_pids_field">
        <?php foreach($pids_arr as $pid):?>
            <span><a class="pricemesh_remove" id="pricemesh_pid_<?php echo $pid;?>" class="ntdelbutton">X</a>&nbsp;<?php echo $pid;?></span>
        <?php endforeach;?>
    </div>
    <script type="text/javascript">
        window.onload = function(){
            <?php foreach($pids_arr as $pid):?>
                document.getElementById("pricemesh_pid_<?php echo $pid;?>").onclick = Pricemesh.remove_pid;
            <?php endforeach;?>
        }
    </script>
    <?php
}

function pricemesh_save_meta_box($post_id, $post = NULL) {
    /**
     * Saves the input in the meta box
     */
    // if post is a revision skip saving our meta box data
    if($post->post_type == 'revision') { return; }
    // process form data if $_POST is set
    if(isset($_POST['pricemesh_pids'])) {
        // save the meta box data as post meta using the post ID as a unique prefix
        update_post_meta($post_id,'_pricemesh_pids', esc_attr(rtrim($_POST['pricemesh_pids'],",")));
        //update_post_meta($post_id,'_pricemesh_price', esc_attr($_POST['pricemesh_price']));
    }else if(isset($_POST['pricemesh_new_pid'])){
        update_post_meta($post_id,'_pricemesh_pids', esc_attr(rtrim($_POST['pricemesh_new_pid'],",")));
    }
}

function pricemesh_load_scripts($hook){
    /**
     * Loads the custom.js if the current page is of basetype "post"
     */
    if(is_on_post_screen()){
        wp_enqueue_script('custom-js',plugins_url('js/custom.js', __FILE__));
    }
}

function is_on_post_screen(){
    /**
     * Checks if the current page is of basetype "post"
     */
    $screen = get_current_screen();

    if($screen->base == "post"){
        return True;
    }
    return False;
}
add_action('admin_enqueue_scripts', 'pricemesh_load_scripts');

//** WIDGET */

class PricemeshWidget extends WP_Widget{

    function PricemeshWidget(){
        $widget_ops = array('classname' => 'PricemeshWidget', 'description' => 'Zur Anzeige des Preisvergleichs als Widget.' );
        $this->WP_Widget('PricemeshWidget', 'Pricemesh Widget', $widget_ops);
    }

    function form($instance){
        $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
        $title = $instance['title'];
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /> </label></p>
        <?php
    }

    function update($new_instance, $old_instance){
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }

    function widget($args, $instance){
        extract($args, EXTR_SKIP);
        if(get_injection_point() == Render::WIDGET){
            echo $before_widget;
            $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

            if (!empty($title))
                echo $before_title . $title . $after_title;;

            echo "<div id='pricemesh'></div>";

            echo $after_widget;
        }
    }

}
add_action( 'widgets_init', create_function('', 'return register_widget("PricemeshWidget");') );


//*** HELPER FUNCTIONS ***//

function is_pricemesh_widget_active(){
    /**
     * Checks if the widget is active
     */
    return is_active_widget(false, false, "pricemeshwidget", true);
}

function is_shortcode_in_content(){
    /**
     * Checks if the current post has a [pricemesh] shortcode
     */
    if(strpos($GLOBALS['post']->post_content, "[pricemesh]") === false){
        return False;
    }
    return True;
}

function get_injection_point(){
    /**
     * Determines where the Plugin should be displayed. IN_CONTENT, in form of a WIDGET or AFTER_CONTENT
     */
    if(is_shortcode_in_content()){
        return Render::IN_CONTENT;
    }else if(is_pricemesh_widget_active()){
        return Render::WIDGET;
    }else{
        return Render::AFTER_CONTENT;
    }
}

function is_injection_needed(){
    /**
     * Checks if the plugin should be loaded and injected for the current page.
     */
    if(is_single()){
        $opts = get_pricemesh_settings();
        if(strlen($opts["pids"])>=8 && strlen($opts["token"])>5){
            return true;
        }
    }
    return False;
}

function get_pricemesh_settings(){
    /***
     * Gets the settings for the pricemesh plugin.
     */
    return array(
        "pids" => rtrim(get_post_meta($GLOBALS['post']->ID,'_pricemesh_pids',true), ","),
        "token" => get_option("pricemesh_option_token", "demo-abcde-demo-12345-demo-abcde1234"),
        "country" => get_option("pricemesh_option_country", "de"),
    );
}
add_action('wp_head','inject_js');
function inject_js(){
    /***
     * Injects the pricemesh JS into the <head> of the current page
     */
    if(is_injection_needed()){
       $opts = get_pricemesh_settings();
       if(current_user_can('edit_post', get_post_meta($GLOBALS['post']->ID))){
           $debug = "true";
       }else{
           $debug = "false";
       }
       echo "<script type='text/javascript'>
                var pricemesh_token = '".$opts["token"]."';
                var pricemesh_country = '".$opts["country"]."';
                var pricemesh_pids = '".$opts["pids"]."';
                var pricemesh_debug = $debug;
                var pricemesh_initialItems = 5;
                var pricemesh_load = true;
                (function() {
                    var pricemesh = document.createElement('script'); pricemesh.type = 'text/javascript'; pricemesh.async = true;
                    pricemesh.src = 'https://www.pricemesh.io/static/external/js/pricemesh.min.js';
                    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(pricemesh);
                })();

            </script>";
   }
}

function inject_html(){
    return "<div id='pricemesh'></div>";
}?>