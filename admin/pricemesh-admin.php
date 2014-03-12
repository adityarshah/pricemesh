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

        if($this->is_on_post_screen()){
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

        if($this->is_on_post_screen()){
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
			__('Pricemesh Einstellungen', $this->plugin_slug),
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
        $section_name = "Token & Secret";
        $section_callback = "settings_section_auth_callback";
        add_settings_section(
            $section, __($section_name, $this->plugin_slug), array($this, $section_callback),$this->plugin_slug
        );

        //token
        $option = "pricemesh_option_token";
        $option_name = "Token";
        $option_callback = "settings_auth_token_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);


        //secret
        $option = "pricemesh_option_secret";
        $option_name = "Secret Key";
        $option_callback = "settings_auth_secret_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);


        //-----------------------------------------------------------------
        // Basic Section
        //-----------------------------------------------------------------
        $section = "pricemesh_section_basic";
        $section_name = "Grundeinstellungen";
        $section_callback = "settings_section_basic_callback";
        add_settings_section(
            $section, __($section_name, $this->plugin_slug), array($this, $section_callback),$this->plugin_slug
        );

        //country
        $option = "pricemesh_option_country";
        $option_name = "Land";
        $option_callback = "settings_basic_country_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //debug
        $option = "pricemesh_option_debug";
        $option_name = "Hinweise und Warnungen anzeigen";
        $option_callback = "settings_basic_debug_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //-----------------------------------------------------------------
        // Styling Section
        //-----------------------------------------------------------------
        $section = "pricemesh_section_styling";
        $section_name = "Design";
        $section_callback = "settings_section_styling_callback";
        add_settings_section(
            $section, __($section_name, $this->plugin_slug), array($this, $section_callback),$this->plugin_slug
        );

        //theme
        $option = "pricemesh_option_theme";
        $option_name = "Theme";
        $option_callback = "settings_styling_theme_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //stylesheet
        $option = "pricemesh_option_stylesheet";
        $option_name = "Stylesheet";
        $option_callback = "settings_styling_stylesheet_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //-----------------------------------------------------------------
        // Config Section
        //-----------------------------------------------------------------
        $section = "pricemesh_section_config";
        $section_name = "Konfiguration";
        $section_callback = "settings_section_config_callback";
        add_settings_section(
            $section, __($section_name, $this->plugin_slug), array($this, $section_callback),$this->plugin_slug
        );

        //initial_items
        $option = "pricemesh_option_initial_items";
        $option_name = "Angezeigte Produkte";
        $option_callback = "settings_config_initial_items_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //disclaimer
        $option = "pricemesh_option_disclaimer";
        $option_name = "Disclaimer";
        $option_callback = "settings_config_disclaimer_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //stock
        $option = "pricemesh_option_stock";
        $option_name = "Versandinfo zeigen";
        $option_callback = "settings_config_stock_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //duplicates
        $option = "pricemesh_option_duplicates";
        $option_name = "Duplikate filtern";
        $option_callback = "settings_config_duplicates_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //display
        $option = "pricemesh_option_display";
        $option_name = "Anzeige";
        $option_callback = "settings_config_display_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

        //-----------------------------------------------------------------
        // 3rd Party Integration
        //-----------------------------------------------------------------
        $section = "pricemesh_section_3rdparty";
        $section_name = "Third Party Integration";
        $section_callback = "settings_section_3rd_party_callback";
        add_settings_section(
            $section, __($section_name, $this->plugin_slug), array($this, $section_callback),$this->plugin_slug
        );


        //wp robot
        $option = "pricemesh_option_wp_robot_integration";
        $option_name = "WP Robot";
        $option_callback = "settings_3rd_party_wp_robot_callback";
        add_settings_field(
            $option, __($option_name, $this->plugin_slug), array($this, $option_callback), $this->plugin_slug, $section
        );
        register_setting($group, $option);

    }

    /**
     * Auth section Callback
     * @since    1.0.0
     */
    public function settings_section_auth_callback(){
        echo __("Erstellen Sie einen kostenlosen Account auf <a href='https://www.pricemesh.io' target='_blank'>pricemesh.io</a>".
             " und tragen Sie Ihr eigenes Token und den Secret Key ein, um Provisionen für Verkäufe zu erhalten und die".
             " Suchfunktion nutzen zu können", $this->plugin_slug);
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
            echo "<p class='description'>".__("Das Demo-Token ist voll funktionsfähig, es können damit jedoch keine Provisionen verdient werden.", $this->plugin_slug)."</p>";
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
            echo "<p class='description'>".__("Um die Suchfunktion zu nutzen, tragen Sie bitte den Secret Key ein.", $this->plugin_slug)."</p>";
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
        $available_countries = array("de");

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
        if($setting == "on"){
            echo '<input type="checkbox" name="pricemesh_option_debug" value="on" checked>';
        }else{
            echo '<input type="checkbox" name="pricemesh_option_debug" value="on">';
        }
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
     * theme Callback
     * @since    1.3.0
     */
    public function settings_styling_theme_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["theme"];

        $options = array("basic" => __("Standard"));
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
        $options = array("off" => __("Nicht anzeigen"), "top" => __("Oberhalb des Preisvergleichs"), "bottom" => __("Unterhalb des Preisvergleichs"));
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
        if($setting == "on"){
            echo '<input type="checkbox" name="pricemesh_option_stock" value="on" checked>';
        }else{
            echo '<input type="checkbox" name="pricemesh_option_stock" value="on">';
        }


    }

    /**
     * duplicates Callback
     * @since    1.3.0
     */
    public function settings_config_duplicates_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["duplicates"];
        $options = array("off" => _("Nicht filtern"), "all" => _("Alle duplikate filtern (Händler darf nur einmal vorkommen)"), "same-price" => _("Bei gleichem Preis filtern (Händler darf nur einmal pro Preis vorkommen)"));
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
        $options = array("always" => __("Preisvergleich immer anzeigen"), "has-products" => __("Preisvergleich nur anzeigen, wenn Produkte gefunden werden"));
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
     * 3rd party section Callback
     * @since    1.0.1
     */
    public function settings_section_3rd_party_callback(){
        echo __("Pricemesh kann auf andere Plugins zugreifen und einen Preisvergleich anzeigen, wenn ein Barcode erkannt wird.", $this->plugin_slug);
    }

    /**
     * Auth secret Callback
     * @since    1.0.1
     */
    public function settings_3rd_party_wp_robot_callback(){
        $opts = self::get_pricemesh_settings();
        $setting = $opts["wp_robot_integration"];
        if($this->is_wp_robot_installed()){
            $checked = checked('1', $setting);
            echo "<input name='pricemesh_option_wp_robot_integration' type='checkbox' value='1' $checked/>";
        }else{
            //echo "<input name='pricemesh_option_wp_robot_integration' type='checkbox' value='1' disabled/>";
            echo "<p class='description'>".__("WPRobot ist nicht installiert", $this->plugin_slug)."</p>";
        }
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

    function meta_box_init(){
        // create our custom meta box
        add_meta_box('pricemesh-meta',__('Pricemesh', 'pricemesh-plugin'), array($this, 'meta_box'),'post','normal','high');
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

}?>