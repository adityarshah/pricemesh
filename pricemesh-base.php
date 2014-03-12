<?php
class PricemeshBase {

    protected $opts = null;

    protected  function __construct(){
        $this->opts = $this->get_pricemesh_settings();
    }

    /**
     * Gets the settings for the pricemesh plugin.
     * @since    1.0.0
     * @return array    holding the settings
     */
    static function get_pricemesh_settings(){
        return array(
            "pids" => self::get_pids(),
            "secret" => get_option("pricemesh_option_secret", ""),
            "token" => get_option("pricemesh_option_token", "demo-abcde-demo-12345-demo-abcde1234"),
            "country" => get_option("pricemesh_option_country", "de"),
            "initial_items" => get_option("pricemesh_option_initial_items", "5"),
            "disclaimer" => get_option("pricemesh_option_disclaimer", "off"),
            "stock" => get_option("pricemesh_option_stock", "on"),
            "duplicates" => get_option("pricemesh_option_duplicates", "all"),
            "display" => get_option("pricemesh_option_display", "always"),
            "stylesheet" => get_option("pricemesh_option_stylesheet", ""),
            "theme" => get_option("pricemesh_option_theme", "basic"),
            "debug" => get_option("pricemesh_option_debug", "off"),

            "wp_robot_integration" => get_option("pricemesh_option_wp_robot_integration", 0),
        );
    }

    /**
     * Returns the pids for a given postid
     * @since    1.0.0
     * @return string    holding pids
     */
    static function get_pids(){
        if(isset($GLOBALS["post"])){
            return trim(get_post_meta($GLOBALS['post']->ID,'_pricemesh_pids',true), ",");
        }else{
            return false;
        }
    }
}?>