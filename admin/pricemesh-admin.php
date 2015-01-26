<?php
class PricemeshAdmin extends PricemeshBase{

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	protected function __construct() {
        parent::__construct();
		/*
		 * Call $plugin_slug from public plugin class
		 */
		$plugin = PricemeshPublic::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

		// Add the options page and menu item.
		add_action('admin_menu', array($this, 'add_plugin_admin_menu'));

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename(plugin_dir_path( __DIR__ ).$this->plugin_slug.'.php' );
		add_filter('plugin_action_links_'.$plugin_basename, array($this, 'add_action_links'));

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

    }

    /**
     * Register all custom actions
     *
     * @since    1.0.0
     *
     */
    private function add_actions(){
        add_action('admin_init',array($this, 'meta_box_init'));
        add_action('admin_init',array($this, 'settings_init'));
        add_action('save_post',array($this, 'save_meta_box'));
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
	 * Register and enqueue admin-specific style sheet.
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
        if($this->is_on_supported_screen()){
            wp_enqueue_style( $this->plugin_slug .'pm-metabox-styles', plugins_url('assets/css/metabox.css', __FILE__ ), array(), PricemeshPublic::VERSION );
        }

		if(!isset($this->plugin_screen_hook_suffix)){
			return;
		}

		$screen = get_current_screen();
		if ($this->plugin_screen_hook_suffix == $screen->id) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url('assets/css/admin.css', __FILE__ ), array(), PricemeshPublic::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts(){

        if($this->is_on_supported_screen()){
            wp_enqueue_script($this->plugin_slug.'pm-handlebars', plugins_url('assets/js/handlebars-v1.3.0.js', __FILE__ ), array('jquery'), PricemeshPublic::VERSION );
            wp_enqueue_script($this->plugin_slug.'pm-metabox', plugins_url('assets/js/metabox.js', __FILE__ ), array('jquery'), PricemeshPublic::VERSION );
            wp_enqueue_script($this->plugin_slug.'pm-metabox-tabs', plugins_url('assets/js/tabs.js', __FILE__ ), array('jquery'), PricemeshPublic::VERSION );
        }

		if(!isset($this->plugin_screen_hook_suffix)){
			return;
		}

		$screen = get_current_screen();
		if ($this->plugin_screen_hook_suffix == $screen->id){
            wp_enqueue_script($this->plugin_slug.'pm-default', plugins_url('assets/js/admin.js', __FILE__ ), array('jquery'), PricemeshPublic::VERSION );
        }

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_options_page(
			__('Pricemesh Settings', $this->plugin_slug),
			__('Pricemesh', $this->plugin_slug),
			'manage_options',
			$this->plugin_slug,
			array($this, 'display_plugin_admin_page')
		);

	}

    /**
     * Initializes all settings
     *
     * @since    1.1.0
     *
     */
    public function settings_init(){
        $group = 'pricemesh-settings-group';

        //-----------------------------------------------------------------
        // Token & Secret Section
        //-----------------------------------------------------------------
        $section = "pricemesh_section_auth";
        $section_name = __("Token & Secret", $this->plugin_slug);
        $section_callback = "settings_section_auth_callback";
        add_settings_section(
            $section, $section_name, array($this, $section_callback),$this->plugin_slug
        );

        //token
        $option = "pricemesh_option_token";
        $option_name = __("Token", $this->plugin_slug);
        $option_callback = "settings_auth_token_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);


        //secret
        $option = "pricemesh_option_secret";
        $option_name = __("Secret Key", $this->plugin_slug);
        $option_callback = "settings_auth_secret_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);


        //-----------------------------------------------------------------
        // Basic Section
        //-----------------------------------------------------------------
        $section = "pricemesh_section_basic";
        $section_name = __("Base Settings", $this->plugin_slug);
        $section_callback = "settings_section_basic_callback";
        add_settings_section(
            $section, $section_name, array($this, $section_callback),$this->plugin_slug
        );

        //country
        $option = "pricemesh_option_country";
        $option_name = __("Country", $this->plugin_slug);
        $option_callback = "settings_basic_country_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //debug
        $option = "pricemesh_option_debug";
        $option_name = __("Errors and Warnings", $this->plugin_slug);
        $option_callback = "settings_basic_debug_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //-----------------------------------------------------------------
        // Styling Section
        //-----------------------------------------------------------------
        $section = "pricemesh_section_styling";
        $section_name = __("Design", $this->plugin_slug);
        $section_callback = "settings_section_styling_callback";
        add_settings_section(
            $section, $section_name, array($this, $section_callback),$this->plugin_slug
        );

        //theme
        $option = "pricemesh_option_theme";
        $option_name = __("Theme (Pro)", $this->plugin_slug);
        $option_callback = "settings_styling_theme_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //stylesheet
        $option = "pricemesh_option_stylesheet";
        $option_name = __("Stylesheet (Pro)", $this->plugin_slug);
        $option_callback = "settings_styling_stylesheet_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //-----------------------------------------------------------------
        // Config Section
        //-----------------------------------------------------------------
        $section = "pricemesh_section_config";
        $section_name = __("Configuration", $this->plugin_slug);
        $section_callback = "settings_section_config_callback";
        add_settings_section(
            $section, $section_name, array($this, $section_callback),$this->plugin_slug
        );

        //name
        $option = "pricemesh_option_name";
        $option_name = __("Title (Pro)", $this->plugin_slug);
        $option_callback = "settings_config_name_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //initial_items
        $option = "pricemesh_option_initial_items";
        $option_name = __("Listed Products", $this->plugin_slug);
        $option_callback = "settings_config_initial_items_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //disclaimer
        $option = "pricemesh_option_disclaimer";
        $option_name = __("Disclaimer", $this->plugin_slug);
        $option_callback = "settings_config_disclaimer_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //stock
        $option = "pricemesh_option_stock";
        $option_name = __("Shipping", $this->plugin_slug);
        $option_callback = "settings_config_stock_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //duplicates
        $option = "pricemesh_option_duplicates";
        $option_name = __("Duplicates", $this->plugin_slug);
        $option_callback = "settings_config_duplicates_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //display
        $option = "pricemesh_option_display";
        $option_name = __("Show Pricemesh", $this->plugin_slug);
        $option_callback = "settings_config_display_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //title
        $option = "pricemesh_option_title";
        $option_name = __("Product Title", $this->plugin_slug);
        $option_callback = "settings_config_title_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //link
        $option = "pricemesh_option_link_all";
        $option_name = __("Link on Shop and Price", $this->plugin_slug);
        $option_callback = "settings_config_link_all_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //link_text
        $option = "pricemesh_option_link_text";
        $option_name = __("Link text (Pro)", $this->plugin_slug);
        $option_callback = "settings_config_link_text_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);



        //-----------------------------------------------------------------
        // 3rd Party Integration
        //-----------------------------------------------------------------
        $section = "pricemesh_section_3rdparty";
        $section_name = __("Third Party Integration", $this->plugin_slug);
        $section_callback = "settings_section_3rd_party_callback";
        add_settings_section(
            $section, $section_name, array($this, $section_callback),$this->plugin_slug
        );


        //wp robot
        $option = "pricemesh_option_wp_robot_integration";
        $option_name = __("WP Robot", $this->plugin_slug);
        $option_callback = "settings_3rd_party_wp_robot_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //woocommerce
        $option = "pricemesh_option_woocommerce_integration";
        $option_name = __("WooCommerce", $this->plugin_slug);
        $option_callback = "settings_3rd_party_woocommerce_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //custom post types
        $option = "pricemesh_option_custom_post_types";
        $option_name = __("Custom Post Types", $this->plugin_slug);
        $option_callback = "settings_3rd_party_custom_post_types_callback";
        add_settings_field(
            $option, $option_name, array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

    }

    /**
     * Auth section Callback
     * @since    1.0.0
     */
    public function settings_section_auth_callback(){
        echo __("Create a account on <a href='https://www.pricemesh.io' target='_blank'>pricemesh.io</a>".
             " and add your own token and secret key to".
             " earn sales commission and to use the search function.", $this->plugin_slug);
    }

    /**
     * Auth token Callback
     * @since    1.0.0
     */
    public function settings_auth_token_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["token"];
        $name = "pricemesh_option_token";
        echo "<input type='text' name='$name' id='$name' value='$setting' class='regular-text'/>";
        if(strpos($setting, "demo") === 0){
            echo "<p class='description'>".__("You can't earn commission with the demo token.", $this->plugin_slug)."</p>";
        }
    }

    /**
     * Auth secret Callback
     * @since    1.1.0
     */
    public function settings_auth_secret_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["secret"];
        echo "<input type='text' name='pricemesh_option_secret' value='$setting' class='regular-text'/>";
        if(empty($setting)){
            echo "<p class='description'>".__("To use the search function, please add your secret key..", $this->plugin_slug)."</p>";
        }
    }

    /**
     * basic section Callback
     * @since    1.0.0
     */
    public function settings_section_basic_callback(){
        //no helptext here
    }

    /**
     * Auth secret Callback
     * @since    1.0.0
     */
    public function settings_basic_country_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["country"];
        $available_countries = array("de", "es","fr", "it", "us", "uk");

        echo "<select name='pricemesh_option_country'>";
        foreach($available_countries as $country){
            if($country == $setting){
                echo "<option selected>$country</option>";
            }else{
                echo "<option>$country</option>";
            }
        }
    }

    /**
     * debug Callback
     * @since    1.3.0
     */
    public function settings_basic_debug_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["debug"];
        $options = array("on" => __("Show", $this->plugin_slug), "off" => __("Hide", $this->plugin_slug));
        foreach($options as $value => $string) {
            if($setting == $value){
                $checked = "checked";
            }else{
                $checked = "";
            }
            echo "<p><label><input type='radio' name='pricemesh_option_debug' value='$value' $checked>$string</label></p>";
        }
    }


    /**
     * link_all Callback
     * @since    1.5.2
     */
    public function settings_config_link_all_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["link_all"];
        $options = array("on" => __("On", $this->plugin_slug), "off" => __("Off", $this->plugin_slug));
        foreach($options as $value => $string) {
            if($setting == $value){
                $checked = "checked";
            }else{
                $checked = "";
            }
            echo "<p><label><input type='radio' name='pricemesh_option_link_all' value='$value' $checked>$string</label></p>";
        }
    }

    /**
     * link_text Callback
     * @since    1.6.3
     */
    public function settings_config_link_text_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["link_text"];
        echo "<input maxlength='20' type='text' name='pricemesh_option_link_text' value='$setting' class='regular-text'/>";
    }

    /**
     * styling section Callback
     * @since    1.3.0
     */
    public function settings_section_styling_callback(){

    }

    /**
     * stylesheet Callback
     * @since    1.3.0
     */
    public function settings_styling_stylesheet_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["stylesheet"];
        echo "<input maxlength='200' type='text' name='pricemesh_option_stylesheet' value='$setting' class='regular-text'/>";

    }

    /**
     * name Callback
     * @since    1.5
     */
    public function settings_config_name_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["name"];
        echo "<input maxlength='40' type='text' name='pricemesh_option_name' value='$setting' class='regular-text'/>";

    }

    /**
     * theme Callback
     * @since    1.3.0
     */
    public function settings_styling_theme_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["theme"];
        $options = array("basic" => __("Standard", $this->plugin_slug), "dark" => __("Dark", $this->plugin_slug));
        //$options = array("basic" => __("Standard"));
        foreach($options as $value => $string) {
            if($setting == $value){
                $checked = "checked";
            }else{
                $checked = "";
            }
            echo "<p><label><input type='radio' name='pricemesh_option_theme' value='$value' $checked>$string</label></p>";
        }

    }

    /**
     * config section Callback
     * @since    1.3.0
     */
    public function settings_section_config_callback(){
    }

    /**
     * initial_items Callback
     * @since    1.3.0
     */
    public function settings_config_initial_items_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["initial_items"];
        $available_items = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10");

        echo "<select name='pricemesh_option_initial_items'>";
        foreach($available_items as $items){
            if($items == $setting){
                echo "<option selected>$items</option>";
            }else{
                echo "<option>$items</option>";
            }
        }
        echo "</select>";

    }

    /**
     * disclaimer Callback
     * @since    1.3.0
     */
    public function settings_config_disclaimer_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["disclaimer"];
        $options = array("off" => __("Hide", $this->plugin_slug),
            "top" => __("Above", $this->plugin_slug),
            "bottom" => __("Below", $this->plugin_slug));
        foreach($options as $value => $string) {
            if($setting == $value){
                $checked = "checked";
            }else{
                $checked = "";
            }
            echo "<p><label><input type='radio' name='pricemesh_option_disclaimer' value='$value' $checked>$string</label></p>";
        }
    }

    /**
     * stock Callback
     * @since    1.3.0
     */
    public function settings_config_stock_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["stock"];
        $options = array("on" => __("Show shipping", $this->plugin_slug),
            "off" => __("Hide shipping", $this->plugin_slug));
        foreach($options as $value => $string) {
            if($setting == $value){
                $checked = "checked";
            }else{
                $checked = "";
            }
            echo "<p><label><input type='radio' name='pricemesh_option_stock' value='$value' $checked>$string</label></p>";
        }


    }

    /**
     * duplicates Callback
     * @since    1.3.0
     */
    public function settings_config_duplicates_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["duplicates"];
        $options = array("off" => __("No filter", $this->plugin_slug),
            "all" => __("Filter all duplicates (shop only once)", $this->plugin_slug),
            "same-price" => __("Filter on same price (one shop per price)", $this->plugin_slug));
        foreach($options as $value => $string) {
            if($setting == $value){
                $checked = "checked";
            }else{
                $checked = "";
            }
            echo "<p><label><input type='radio' name='pricemesh_option_duplicates' value='$value' $checked>$string<br></label></p>";
        }

    }

    /**
     * display Callback
     * @since    1.3.0
     */
    public function settings_config_display_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["display"];
        $options = array("always" => __("Show always", $this->plugin_slug),
            "has-products" => __("Hide if no items are found", $this->plugin_slug));
        foreach($options as $value => $string) {
            if($setting == $value){
                $checked = "checked";
            }else{
                $checked = "";
            }
            echo "<p><label><input type='radio' name='pricemesh_option_display' value='$value' $checked>$string</label></p>";
        }
    }

    /**
     * display Callback
     * @since    1.4
     */
    public function settings_config_title_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["title"];
        $options = array("on" => __("Show Title", $this->plugin_slug),
            "off" => __("Hide Title", $this->plugin_slug));
        foreach($options as $value => $string) {
            if($setting == $value){
                $checked = "checked";
            }else{
                $checked = "";
            }
            echo "<p><label><input type='radio' name='pricemesh_option_title' value='$value' $checked>$string</label></p>";
        }
    }



    /**
     * 3rd party section Callback
     * @since    1.0.1
     */
    public function settings_section_3rd_party_callback(){
        echo __("Pricemesh can access other plugins to add functionality.", $this->plugin_slug);
    }

    /**
     * Auth secret Callback
     * @since    1.0.1
     */
    public function settings_3rd_party_wp_robot_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["wp_robot_integration"];
        if($this->is_wp_robot_installed()){
            $checked = checked('1', $setting, false);

            echo "<p class='description'>".
                    "<input name='pricemesh_option_wp_robot_integration' type='checkbox' value='1' $checked/>".
                    " ".__("Adds imported ASINs to the article.", $this->plugin_slug).
                 "</p>";
        }else{
            //echo "<input name='pricemesh_option_wp_robot_integration' type='checkbox' value='1' disabled/>";
            echo "<p class='description'>".__("WPRobot is not installed", $this->plugin_slug)."</p>";
        }
    }

    /**
     * woocommerce callback
     * @since    1.3.1
     */
    public function settings_3rd_party_woocommerce_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["woocommerce_integration"];
        if($this->is_woocommerce_installed()){
            $checked = checked('1', $setting, false);
            echo "<p class='description'>".
                   "<input name='pricemesh_option_woocommerce_integration' type='checkbox' value='1' $checked/>".
                    " ".__("Enables Pricemesh on Product Pages", $this->plugin_slug).
                "</p>";
        }else{
            //echo "<input name='pricemesh_option_wp_robot_integration' type='checkbox' value='1' disabled/>";
            echo "<p class='description'>".__("WooCommerce is not installed", $this->plugin_slug)."</p>";
        }
    }

    /**
     * custom post type callback
     * @since    1.5.1
     */
    public function settings_3rd_party_custom_post_types_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["custom_post_types"];
        $name = "pricemesh_option_custom_post_types";
        echo "<input type='text' name='$name' id='$name' value='$setting' class='regular-text'/>";
        echo "<p class='description'>".
             " ".__("List of custom post types Pricemesh should be enabled on. Format type1,type2,type3", $this->plugin_slug).
             "</p>";
    }

    /**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once('views/admin.php');
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="'.admin_url('options-general.php?page='.$this->plugin_slug).'">'.__('Settings', $this->plugin_slug).'</a>'
			),
			$links
		);
	}

    /**
     * Checks if the current page is of basetype "post"
     *
     * @since    1.0.0
     * @return boolean  true if the current screen is a post screen
     */
    function is_on_post_screen(){
        $screen = get_current_screen();
        if($screen->base == "post"){
            return True;
        }
        return False;
    }

    /**
     * Checks if the current page is one of the supported types: post or page
     *
     * @since    1.3.1
     * @return boolean  true if the current screen is of type post or page
     */
    function is_on_supported_screen(){
        $screen = get_current_screen();
        if($screen->base == "post" || $screen->base == "page"){
            return True;
        }
        return False;
    }

    function meta_box_init(){
        // create our custom meta box
        add_meta_box('pricemesh-meta',__('Pricemesh', 'pricemesh-plugin'), array($this, 'meta_box'),'post','normal','high');
        add_meta_box('pricemesh-meta',__('Pricemesh', 'pricemesh-plugin'), array($this, 'meta_box'),'page','normal','high');
        $opts = self::get_pricemesh_settings();
        if($opts["woocommerce_integration"]){
            add_meta_box('pricemesh-meta',__('Pricemesh', 'pricemesh-plugin'), array($this, 'meta_box'),'product','normal','high');
        }
        //add meta boxes to all custom post types
        $custom_post_types = explode(",", $opts["custom_post_types"]);
        //filter if empty
        $custom_post_types = array_filter($custom_post_types);
        foreach($custom_post_types as $type){
            add_meta_box('pricemesh-meta',__('Pricemesh', 'pricemesh-plugin'), array($this, 'meta_box'),$type, 'normal','high');
        }
    }

    /**
     * Loads the meta box
     *
     * @since    1.0.0
    */
    function meta_box($post,$box) {
        // retrieve our custom meta box values
        $opts = self::get_pricemesh_settings();

        if(!empty($opts["pids"])){
            $pids_arr = explode(",", $opts["pids"]);
        }else{
            $pids_arr = array();
        }

        // custom meta box form elements
        include_once('views/metabox.php');
    }

    /**
     * Saves the input in the meta box
     *
     * @since    1.0.0
     */
    function save_meta_box($post_id, $post = NULL) {
        // if post is a revision skip saving our meta box data
        if(!is_null($post)){
            if($post->post_type == 'revision') { return; }
        }
        // process form data if $_POST is set
        if(isset($_POST['pricemesh_pids'])) {
            // save the meta box data as post meta using the post ID as a unique prefix
            update_post_meta($post_id,'_pricemesh_pids', esc_attr(trim($_POST['pricemesh_pids'],",")));
        }
    }

    /**
     * Checks if WPRobot is installed
     * @since    1.0.1
     * @return boolean  true if wp_robot is installed. false otherwise
     */
    function is_wp_robot_installed(){
        /***
         * checks if WPRobot is installed
         * Note: only works in the admin area.
         */
        if(is_plugin_active("WPRobot3/wprobot.php")){
            return true;
        }
        return false;
    }

    /**
     * Checks if WooCommerce is installed
     * @since    1.0.1
     * @return boolean  true if WooCommerce is installed. false otherwise
     */
    function is_woocommerce_installed(){
        /***
         * checks if WooCommerce is installed
         * Note: only works in the admin area.
         */
        if(is_plugin_active("woocommerce/woocommerce.php")){
            return true;
        }
        return false;
    }



}?>