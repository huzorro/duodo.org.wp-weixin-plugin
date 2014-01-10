<?php
/**
 * Plugin Name.
 *
 * @package   Plugin_Name_Admin
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-plugin-name.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package Plugin_Name_Admin
 * @author  Your Name <email@example.com>
 */
class Plugin_Name_Admin {

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

    protected $general_settings_key = 'general_settings';
    protected $advanced_settings_key = 'advanced_settings';
    protected $interface_settings_key = "interface_settings";
    protected $default_reply_settings_key = "default_reply_settings_key";

    protected  $general_settings = array();
    protected  $advanced_settings = array();
    protected  $interface_settings = array();
    protected  $default_reply_settings = array();

    protected $plugin_settings_tabs = array();

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */

	private function __construct() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 * @TODO:
		 *
		 * - Rename "Plugin_Name" to the name of your initial plugin class
		 *
		 */
		$plugin = Plugin_Name::get_instance();


		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_admin_scripts' ) );

        add_action('init', array(&$this, 'load_settings'));
        add_action('admin_init', array(&$this, 'register_general_settings'));
        add_action('admin_init', array(&$this, 'register_advanced_settings'));
        add_action('admin_init', array(&$this,  'register_interface_settings'));
        add_action('admin_init', array(&$this,  'register_default_reply_settings'));
		// Add the options page and menu item.
		add_action( 'admin_menu', array( &$this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( '@TODO', array( &$this, 'action_method_name' ) );
		add_filter( '@TODO', array( &$this, 'filter_method_name' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @TODO:
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Plugin_Name::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @TODO:
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Plugin_Name::VERSION );
		}

	}
    /**
    * Loads both the general and advanced settings from
    * the database into their respective arrays. Uses
    * array_merge to merge with default values if they're
    * missing.
    */
    public function load_settings()
    {
        $this->general_settings = (array)get_option($this->general_settings_key);
        $this->advanced_settings = (array)get_option($this->advanced_settings_key);
        $this->interface_settings = (array)get_option($this->interface_settings_key);
        $this->default_reply_settings = (array)get_option($this->default_reply_settings_key);

        // Merge with defaults
        $this->general_settings = array_merge(array(
            'general_option' => 'General value'
        ), $this->general_settings);

        $this->advanced_settings = array_merge(array(
            'advanced_option' => 'Advanced value'
        ), $this->advanced_settings);

        $this->interface_settings = array_merge(array(
            'interface_option' => 'Interface value'
        ), $this->interface_settings);

        $this->default_reply_settings = array_merge(array(
            'default_reply_option' => 'Default reply value'
        ), $this->default_reply_settings);
    }
    /**
    * Registers the general settings via the Settings API,
    * appends the setting to the tabs array of the object.
    */
    function register_general_settings()
    {
        $this->plugin_settings_tabs[$this->general_settings_key] = __('General', $this->plugin_slug);

        register_setting($this->general_settings_key, $this->general_settings_key);
        add_settings_section('section_general', __('General Settings', $this->plugin_slug), array(&$this, 'section_general_desc'), $this->general_settings_key);
        add_settings_field('general_option', __('A General Option', $this->plugin_slug), array(&$this, 'field_general_option'), $this->general_settings_key, 'section_general');
    }

    /**
    * Registers the advanced settings and appends the
    * key to the plugin settings tabs array.
    */
    function register_advanced_settings()
    {
        $this->plugin_settings_tabs[$this->advanced_settings_key] = __('Advanced', $this->plugin_slug);

        register_setting($this->advanced_settings_key, $this->advanced_settings_key);
        add_settings_section('section_advanced', __('Advanced Settings', $this->plugin_slug), array(&$this, 'section_advanced_desc'), $this->advanced_settings_key);
        add_settings_field('advanced_option', __('A Advanced Option', $this->plugin_slug), array(&$this, 'field_advanced_option'), $this->advanced_settings_key, 'section_advanced');
    }
    /**
     * Registers the interface settings and appends the
     * key to the plugin settings tabs array.
     */
    function register_interface_settings()
    {
        $this->plugin_settings_tabs[$this->interface_settings_key] = __('Interface', $this->plugin_slug);

        register_setting($this->interface_settings_key, $this->interface_settings_key);
        add_settings_section('section_interface', __('Interface Settings', $this->plugin_slug), array(&$this, 'section_interface_desc'), $this->interface_settings_key);
        add_settings_field('interface_option', __('A Interface Option', $this->plugin_slug), array(&$this, 'field_interface_option'), $this->interface_settings_key, 'section_interface');
    }
    /**
     * Registers the default reply settings and appends the
     * key to the plugin settings tabs array.
     */
    function register_default_reply_settings()
    {
        $this->plugin_settings_tabs[$this->default_reply_settings_key] = __('Default reply', $this->plugin_slug);

        register_setting($this->default_reply_settings_key, $this->default_reply_settings_key);
        add_settings_section('section_default_reply', __('Default Reply Settings', $this->plugin_slug), array(&$this, 'section_default_reply_desc'), $this->default_reply_settings_key);
        add_settings_field('default_reply_option', __('A Reply Option', $this->plugin_slug), array(&$this, 'field_default_reply_option'), $this->default_reply_settings_key, 'section_default_reply');
    }
    function section_general_desc()
    {
        __('General section description goes here.', $this->plugin_slug);
    }

    function section_advanced_desc()
    {
        __('Advanced section description goes here.', $this->plugin_slug);
    }

    function section_interface_desc() {
        __('Interface section description goes here.', $this->plugin_slug);
    }

    function section_default_reply_desc() {
        __('Default reply section description goes here.', $this->plugin_slug);
    }

    /**
    * General Option field callback, renders a
    * text input, note the name and value.
    */
    function field_general_option()
    {
        ?>
        <input type="text" name="<?php echo $this->general_settings_key; ?>[general_option]"
               value="<?php echo esc_attr($this->general_settings['general_option']); ?>"/>
    <?php
    }

    /**
     * Advanced Option field callback, same as above.
     */
    function field_advanced_option()
    {
        ?>
        <input type="text" name="<?php echo $this->advanced_settings_key; ?>[advanced_option]"
               value="<?php echo esc_attr($this->advanced_settings['advanced_option']); ?>"/>
    <?php
    }

    function field_interface_option()
    {
        ?>
        <input type="text" name="<?php echo $this->interface_settings_key; ?>[interface_option]"
               value="<?php echo esc_attr($this->interface_settings['interface_option']); ?>"/>
    <?php
    }

    function field_default_reply_option()
    {
        ?>
        <input type="text" name="<?php echo $this->default_reply_settings_key; ?>[default_reply_option]"
               value="<?php echo esc_attr($this->default_reply_settings['default_reply_option']); ?>"/>
    <?php
    }
    /**
     * Plugin Options page rendering goes here, checks
     * for active tab and replaces key with the related
     * settings key. Uses the plugin_options_tabs method
     * to render the tabs.
     */
    function plugin_options_page()
    {
        $tab = isset($_GET['tab']) ? $_GET['tab'] : $this->general_settings_key;
        ?>
        <div class="wrap">
            <?php $this->plugin_options_tabs(); ?>
            <form method="post" action="options.php">
                <?php wp_nonce_field('update-options'); ?>
                <?php settings_fields($tab); ?>
                <?php do_settings_sections($tab); ?>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
    }
    /**
     * Renders our tabs in the plugin options page,
     * walks through the object's tabs array and prints
     * them one by one. Provides the heading for the
     * plugin_options_page method.
     */
    function plugin_options_tabs()
    {
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : $this->general_settings_key;

        echo '<h2 class="nav-tab-wrapper">';
        foreach ($this->plugin_settings_tabs as $tab_key => $tab_caption) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_slug. '-settings' . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
        }
        echo '</h2>';
    }

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 * @TODO:
		 *
		 * - Change 'Page Title' to the title of your plugin admin page
		 * - Change 'Menu Text' to the text for menu item for the plugin settings page
		 * - Change 'manage_options' to the capability you see fit
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 *
		 * 	$this->plugin_screen_hook_suffix = add_options_page(
		 *	__( 'Page Title', $this->plugin_slug ),
		 *	__( 'Menu Text', $this->plugin_slug ),
		 *	'manage_options',
		 *	$this->plugin_slug,
		 *	array( $this, 'display_plugin_admin_page' )
		 *  );
		 */

        //add admin main menu
        add_menu_page(
            __('Latest-news', $this->plugin_slug),
            __('Weixin', $this->plugin_slug),
            'activate_plugins',
            $this->plugin_slug . "-" . 'news',
            array(&$this, 'display_plugin_admin_page')
        );
        //add latest news sub menu
        add_submenu_page(
            $this->plugin_slug . "-" . 'news',
            __('Latest-news', $this->plugin_slug),
            __('Latest-news', $this->plugin_slug),
            'activate_plugins',
            $this->plugin_slug . "-" . 'news',
            array(&$this, 'display_plugin_admin_page')
        );
        //add setting sub menu
        add_submenu_page(
            $this->plugin_slug . "-" . 'news',
            __('General-Settings', $this->plugin_slug),
            __('General-Settings', $this->plugin_slug),
            'activate_plugins',
            $this->plugin_slug . "-" . 'settings',
            array(&$this, 'display_plugin_admin_page')
        );

        //add custom-menu sub menu
        add_submenu_page(
            $this->plugin_slug . "-" . 'news',
            __('Custom-menu', $this->plugin_slug),
            __('Custom-menu', $this->plugin_slug),
            'activate_plugins',
            $this->plugin_slug . "-" . 'menu',
            array(&$this, 'display_plugin_admin_page')
        );
        //add custom-reply sub menu
        add_submenu_page(
            $this->plugin_slug . "-" . 'news',
            __('Custom-reply', $this->plugin_slug),
            __('Custom-reply', $this->plugin_slug),
            'activate_plugins',
            $this->plugin_slug . "-" . 'reply',
            array(&$this, 'display_plugin_admin_page')
        );



	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
//		include_once( 'views/general-settings-plugin.php' );
        $this->plugin_options_page();
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}
