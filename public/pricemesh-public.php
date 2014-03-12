<?php

class PricemeshRender{
    /**
     * Basic ENUM which holds the information, where the HTML part should be injected to the page.
     * @since 1.1.0
     * @var enum
     */

    const IN_CONTENT = 1;
    const AFTER_CONTENT = 2;
    const WIDGET = 3;
}

class PricemeshPublic extends PricemeshBase{

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.3';

	/**
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'Pricemesh';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	protected function __construct() {
        parent::__construct();

		// Load plugin text domain
		add_action('init', array($this, 'load_plugin_textdomain'));

		// Activate plugin when new blog is added
		add_action('wpmu_new_blog', array($this, 'activate_new_site'));

		// Load public-facing style sheet and JavaScript.
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        $this->add_filter();
        $this->add_actions();

	}

    /**
     * Register all custom filters
     *
     * @since    1.0.0
     *
     */
    private function add_filter(){
        add_filter("the_content", array($this, "add_pricemesh"));
    }

    /**
     * Register all custom actions
     *
     * @since    1.0.0
     *
     */
    private function add_actions(){
        add_action('wp_head', array($this, 'inject_js'));
    }

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if (function_exists('is_multisite') && is_multisite()){

			if($network_wide){

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach($blog_ids as $blog_id) {

					switch_to_blog($blog_id);
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate($network_wide) {

		if(function_exists('is_multisite') && is_multisite()){

			if($network_wide){

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach($blog_ids as $blog_id){

					switch_to_blog($blog_id);
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site($blog_id) {

		if (1 !== did_action('wpmu_new_blog')) {
			return;
		}

		switch_to_blog($blog_id);
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col($sql);

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters('plugin_locale', get_locale(), $domain );

		load_textdomain($domain, trailingslashit(WP_LANG_DIR).$domain.'/'.$domain.'-'.$locale.'.mo');
		load_plugin_textdomain($domain, FALSE, basename(plugin_dir_path(dirname(__FILE__))).'/languages/');

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		//wp_enqueue_style($this->plugin_slug.'-plugin-styles', plugins_url('assets/css/public.css', __FILE__ ), array(), self::VERSION);
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		//wp_enqueue_script($this->plugin_slug.'-plugin-script',plugins_url('assets/js/public.js', __FILE__ ), array('jquery'), self::VERSION);
	}

	/**
	 * Loads the html part of the plugin to be displayed on the current page.
	 * @since    1.0.0
	 */
    public function add_pricemesh($content){
        if(is_single() && $this->is_injection_needed()){
            $injection_point = $this->get_injection_point();
            if($injection_point == PricemeshRender::IN_CONTENT){
                $content = str_replace("[pricemesh]", $this->inject_html(), $content);
            }else if($injection_point == PricemeshRender::AFTER_CONTENT){
                $content.= $this->inject_html();
            }
        }else{
            $content = str_replace("[pricemesh]", "", $content);
        }
        return $content;
    }

    /**
     * Checks if the user activated the widget.
     * @since    1.0.0
     * @return boolean  true, if activated. false otherwise
     */
    public static function is_widget_active(){
        return is_active_widget(false, false, "pricemeshwidget", true);
    }

    /**
     * Checks if Pricemesh should be injected on this page
     * @since    1.0.0
     * @return boolean  true, if injection needed. false otherwise
     */
    public static function is_injection_needed(){

        if(is_single()){
            $opts = self::get_pricemesh_settings();
            if(strlen($opts["pids"])>=8 && strlen($opts["token"])>5){
                return true;
            }elseif($opts["wp_robot_integration"]){

                //check if we already added the pids for this post
                if(get_post_meta($GLOBALS['post']->ID,'_pricemesh_wp_robot_pids_added',true) == "1"){
                    //return false since we added the pids, but they were empty (the first if failed)
                    return false;
                }else{
                    //if we integrate wp robot, we need to convert the pids at a early point
                    self::add_wp_robot_pids();

                    if(strlen($opts["pids"])>=8 && strlen($opts["token"])>5){
                        //if we have some pids now, return true
                        return true;
                    }

                }
            }
        }
        return False;
    }

    /**
     * Extracts the PIDs from a WPRobot Post and adds them to POST meta
     * @since    1.0.1
     */
    private static function add_wp_robot_pids(){

        //get this posts content and extract the asin from the shortcode
        $post = $GLOBALS['post']->post_content;
        $post_id = $GLOBALS['post']->ID;

        if(strpos($post, "[wprama")){
            //get current pids for this post
            $settings = self::get_pricemesh_settings();
            $current_pids = $settings["pids"];

            //extract the ASIN from WPRobot
            preg_match('/asin="\w+"/',$post, $pids);
            foreach($pids as $pid){
                $pid = str_replace("asin=", "", $pid);
                $pid = str_replace('"', "", $pid);
                if(!strpos($current_pids, $pid)){
                    $current_pids.= ",".$pid;
                }
            }

            $current_pids = trim($current_pids, ",");

            //add the ASIN to post meta
            update_post_meta($post_id, '_pricemesh_pids', $current_pids);
        }

        //update post meta to reflect that we extracted the ASIN
        update_post_meta($post_id,'_pricemesh_wp_robot_pids_added', "1");
    }

    /**
     * Injects the pricemesh JS into the <head> of the current page
     * @since    1.0.0
     */
    public function inject_js(){
        if($this->is_injection_needed()){
            $opts = self::get_pricemesh_settings();
            if(current_user_can('edit_post', get_post_meta($GLOBALS['post']->ID)) && $opts["debug"]){
                $debug = "on";
            }else{
                $debug = "off";
            }
            echo "<script type='text/javascript'>
                var pricemesh_token = '".$opts["token"]."';
                var pricemesh_country = '".$opts["country"]."';
                var pricemesh_pids = '".$opts["pids"]."';
                var pricemesh_debug = '$debug';
                var pricemesh_initialitems = '".$opts['initial_items']."';
                var pricemesh_disclaimer = '".$opts["disclaimer"]."';
                var pricemesh_stock = '".$opts["stock"]."';
                var pricemesh_duplicates = '".$opts["duplicates"]."';
                var pricemesh_stylesheet = '".$opts["stylesheet"]."';
                var pricemesh_load = true;
                var pricemesh_plugin = 'wp';
                (function() {
                    var pricemesh = document.createElement('script'); pricemesh.type = 'text/javascript'; pricemesh.async = true;
                    pricemesh.src = 'https://www.pricemesh.io/static/external/js/pricemesh.min.js?v=".self::VERSION."';
                    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(pricemesh);
                })();

            </script>";
        }
    }

    /**
     * Returns a string containing the HTML that should be injected.
     * @since    1.0.0
     * @return string
     */
    function inject_html(){
        return "<div id='pricemesh'></div>";
    }

    /**
     * Determines where the Plugin should be displayed. IN_CONTENT, in form of a WIDGET or AFTER_CONTENT
     * @since    1.0.0
     * @return PricemeshRender enum
     */
    public static function get_injection_point(){
        /**
         *
         */
        if(self::is_shortcode_in_content()){
            return PricemeshRender::IN_CONTENT;
        }else if(self::is_widget_active()){
            return PricemeshRender::WIDGET;
        }else{
            return PricemeshRender::AFTER_CONTENT;
        }
    }

    /**
     * Checks if the current post has a [pricemesh] shortcode
     * @since    1.0.0
     * @return boolean
     */
    public static function is_shortcode_in_content(){
        if(isset($GLOBALS["post"])){
            if(strpos($GLOBALS['post']->post_content, "[pricemesh]") === false){
                return False;
            }
        }
        return True;
    }

}

class PricemeshWidget extends WP_Widget{

    function PricemeshWidget(){
        $widget_ops = array('classname' => 'PricemeshWidget', 'description' => 'Zur Anzeige des Preisvergleichs als Widget.' );
        $this->WP_Widget('PricemeshWidget', 'Pricemesh Widget', $widget_ops);
    }

    function form($instance){
        $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
        $title = $instance['title'];
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /> </label></p>
    <?php
    }

    function update($new_instance, $old_instance){
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }

    function widget($args, $instance){
        extract($args, EXTR_SKIP);
        //if we have the correct render point
        if(PricemeshPublic::get_injection_point() == PricemeshRender::WIDGET){
            if(PricemeshPublic::is_injection_needed()){
                echo $before_widget;
                $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

                if (!empty($title)){
                    echo $before_title . $title . $after_title;
                }

                echo "<div id='pricemesh'></div>";

                echo $after_widget;
            }
        }
    }

}?>