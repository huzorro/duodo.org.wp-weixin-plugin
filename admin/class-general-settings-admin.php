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
 * @package General_settings_admin
 * @author  Your Name <email@example.com>
 */
class General_settings_admin {

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

    protected $wechat_general_settings_key = 'wechat_general_settings';
    protected $wechat_advanced_settings_key = 'wechat_advanced_settings';
    protected $wechat_msgtype_desc_settings_key = "wechat_msgtype_desc_settings";
    protected $wechat_reply_func_settings_key = "wechat_reply_func_settings";

    protected  $wechat_general_settings = array();
    protected  $wechat_advanced_settings = array();
    protected  $wechat_msgtype_desc_settings = array();
    protected  $wechat_reply_func_settings = array();

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
        add_action('admin_init', array(&$this, 'register_wechat_general_settings'));
        add_action('admin_init', array(&$this, 'register_wechat_advanced_settings'));
        add_action('admin_init', array(&$this,  'register_wechat_msgtype_desc_settings'));
        add_action('admin_init', array(&$this,  'register_wechat_reply_func_settings'));
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
        $this->wechat_general_settings = (array)get_option($this->wechat_general_settings_key);
        $this->wechat_advanced_settings = (array)get_option($this->wechat_advanced_settings_key);
        $this->wechat_msgtype_desc_settings = (array)get_option($this->wechat_msgtype_desc_settings_key);
        $this->wechat_reply_func_settings = (array)get_option($this->wechat_reply_func_settings_key);

        // Merge with defaults
        $this->wechat_general_settings = array_merge(array(
            'token' => __('token', $this->plugin_slug),
            'thumbnails' => __('thumbnails url', $this->plugin_slug),
            'reply' => __('replay max', $this->plugin_slug)
        ), $this->wechat_general_settings);

        $this->wechat_advanced_settings = array_merge(array(
            'appid' => __('appid', $this->plugin_slug),
            'secret' => __('scret', $this->plugin_slug)
        ), $this->wechat_advanced_settings);

        $this->wechat_msgtype_desc_settings = array_merge(array(
            'text' => __('text desc', $this->plugin_slug),
            'image' => __('image desc', $this->plugin_slug),
            'location' => __('location desc', $this->plugin_slug),
            'link' => __('link desc', $this->plugin_slug),
            'music' => __('music desc', $this->plugin_slug),
            'news' => __('news desc', $this->plugin_slug),
            'voice' => __('voice desc', $this->plugin_slug),
            'video' => __('video desc', $this->plugin_slug),
            'event:subscribe' => __('event:subscribe desc', $this->plugin_slug),
            'event:unsubscribe' => __('event:unsubscribe desc', $this->plugin_slug),
            'event:LOCATION' => __('event:LOCATION desc', $this->plugin_slug),
            'event:CLICK' => __('event:CLICK desc', $this->plugin_slug),
            'event:SCAN' => __('event:SCAN desc', $this->plugin_slug)
        ), $this->wechat_msgtype_desc_settings);

        $this->wechat_reply_func_settings = array_merge(array(
            'latest_news_reply_func' => __('function desc', $this->plugin_slug),
            'latest_hot_reply_func' => __('function desc', $this->plugin_slug)
        ), $this->wechat_reply_func_settings);
    }
    /**
    * Registers the general settings via the Settings API,
    * appends the setting to the tabs array of the object.
    */
    function register_wechat_general_settings()
    {
        $this->plugin_settings_tabs[$this->wechat_general_settings_key] = __('General', $this->plugin_slug);

        register_setting($this->wechat_general_settings_key, $this->wechat_general_settings_key);
        add_settings_section('section_general', __('General Settings', $this->plugin_slug), array(&$this, 'section_general_desc'), $this->wechat_general_settings_key);
        add_settings_field('token', __('token', $this->plugin_slug), array(&$this, 'field_wechat_general_option_token'), $this->wechat_general_settings_key, 'section_general');
        add_settings_field('thumbnails', __('thumbnails', $this->plugin_slug), array(&$this, 'field_wechat_general_option_thumbnails'), $this->wechat_general_settings_key, 'section_general');
        add_settings_field('reply', __('reply max', $this->plugin_slug), array(&$this, 'field_wechat_general_option_replay_max'), $this->wechat_general_settings_key, 'section_general');
    }

    /**
    * Registers the advanced settings and appends the
    * key to the plugin settings tabs array.
    */
    function register_wechat_advanced_settings()
    {
        $this->plugin_settings_tabs[$this->wechat_advanced_settings_key] = __('Advanced', $this->plugin_slug);

        register_setting($this->wechat_advanced_settings_key, $this->wechat_advanced_settings_key);
        add_settings_section('section_advanced', __('Advanced Settings', $this->plugin_slug), array(&$this, 'section_advanced_desc'), $this->wechat_advanced_settings_key);
        add_settings_field('appid', __('appid', $this->plugin_slug), array(&$this, 'field_wechat_advanced_option_appid'), $this->wechat_advanced_settings_key, 'section_advanced');
        add_settings_field('secret', __('secret', $this->plugin_slug), array(&$this, 'field_wechat_advanced_option_secret'), $this->wechat_advanced_settings_key, 'section_advanced');
    }
    /**
     * Registers the interface settings and appends the
     * key to the plugin settings tabs array.
     */
    function register_wechat_msgtype_desc_settings()
    {
        $this->plugin_settings_tabs[$this->wechat_msgtype_desc_settings_key] = __('MsgType', $this->plugin_slug);

        register_setting($this->wechat_msgtype_desc_settings_key, $this->wechat_msgtype_desc_settings_key);
        add_settings_section('section_msgtype', __('Msg type desc Settings', $this->plugin_slug), array(&$this, 'section_msgtype_desc'), $this->wechat_msgtype_desc_settings_key);
        add_settings_field('text', __('text type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_text'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('image', __('image type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_image'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('news', __('news type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_news'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('music', __('music type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_music'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('voice', __('voice type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_voice'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('link', __('link type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_link'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('video', __('video type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_video'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('location', __('location type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_location'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('event:subscribe', __('event:subscribe type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_event_subscribe'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('event:unsubscribe', __('event:unsubscribe type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_event_unsubscribe'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('event:LOCATION', __('event:LOCATION type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_event_LOCATION'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('event:CLICK', __('event:CLICK type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_event_CLICK'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
        add_settings_field('event:SCAN', __('event:SCAN type', $this->plugin_slug), array(&$this, 'field_wechat_mmsgtype_desc_option_event_SCAN'), $this->wechat_msgtype_desc_settings_key, 'section_msgtype');
    }
    /**
     * Registers the default reply settings and appends the
     * key to the plugin settings tabs array.
     */
    function register_wechat_reply_func_settings()
    {
        $this->plugin_settings_tabs[$this->wechat_reply_func_settings_key] = __('ReplyFunc', $this->plugin_slug);

        register_setting($this->wechat_reply_func_settings_key, $this->wechat_reply_func_settings_key);
        add_settings_section('section_reply_func', __('Reply func Settings', $this->plugin_slug), array(&$this, 'section_reply_func_desc'), $this->wechat_reply_func_settings_key);
        add_settings_field('latest_news_reply_func', __('latest news reply function', $this->plugin_slug), array(&$this, 'field_wechat_reply_func_option_latest_news_reply_func'), $this->wechat_reply_func_settings_key, 'section_reply_func');
        add_settings_field('latest_hot_reply_func', __('latest hot reply function', $this->plugin_slug), array(&$this, 'field_wechat_reply_func_option_latest_hot_reply_func'), $this->wechat_reply_func_settings_key, 'section_reply_func');
    }
    function section_general_desc()
    {
        _e('Wechat General section description goes here.', $this->plugin_slug);
    }

    function section_advanced_desc()
    {
        _e('Wechat Advanced section description goes here.', $this->plugin_slug);
    }

    function section_msgtype_desc() {
        _e('Wechat msg type desc section description goes here.', $this->plugin_slug);
    }

    function section_reply_func_desc() {
        _e('Wechat replyt function section description goes here.', $this->plugin_slug);
    }

    /**
    * General Option field callback, renders a
    * text input, note the name and value.
    */
    function field_wechat_general_option_token()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_general_settings_key; ?>[token]"
               value="<?php echo esc_attr($this->wechat_general_settings['token']); ?>"/>
    <?php
    }
    function field_wechat_general_option_thumbnails()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_general_settings_key; ?>[thumbnails]"
               value="<?php echo esc_attr($this->wechat_general_settings['thumbnails']); ?>"/>
    <?php
    }
    function field_wechat_general_option_replay_max()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_general_settings_key; ?>[reply]"
               value="<?php echo esc_attr($this->wechat_general_settings['reply']); ?>"/>
    <?php
    }
    /**
     * Advanced Option field callback, same as above.
     */
    function field_wechat_advanced_option_appid()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_advanced_settings_key; ?>[appid]"
               value="<?php echo esc_attr($this->wechat_advanced_settings['appid']); ?>"/>
    <?php
    }

    function field_wechat_advanced_option_secret()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_advanced_settings_key; ?>[secret]"
               value="<?php echo esc_attr($this->wechat_advanced_settings['secret']); ?>"/>
    <?php
    }

    function field_wechat_mmsgtype_desc_option_text()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[text]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['text']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_image()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[image]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['image']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_news()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[news]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['news']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_voice()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[voice]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['voice']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_link()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[link]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['link']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_video()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[video]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['video']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_location()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[location]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['location']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_music()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[music]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['music']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_event_subscribe()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[event:subscribe]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['event:subscribe']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_event_unsubscribe()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[event:unsubscribe]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['event:unsubscribe']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_event_LOCATION()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[event:LOCATION]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['event:LOCATION']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_event_CLICK()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[event:CLICK]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['event:CLICK']); ?>"/>
    <?php
    }
    function field_wechat_mmsgtype_desc_option_event_SCAN() {
        ?>
        <input type="text" name="<?php echo $this->wechat_msgtype_desc_settings_key; ?>[event:SCAN]"
               value="<?php echo esc_attr($this->wechat_msgtype_desc_settings['event:SCAN']); ?>"/>
    <?php
    }
    function field_wechat_reply_func_option_latest_news_reply_func()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_reply_func_settings_key; ?>[latest_news_reply_func]"
               value="<?php echo esc_attr($this->wechat_reply_func_settings['latest_news_reply_func']); ?>"/>
    <?php
    }
    function field_wechat_reply_func_option_latest_hot_reply_func()
    {
        ?>
        <input type="text" name="<?php echo $this->wechat_reply_func_settings_key; ?>[latest_hot_reply_func]"
               value="<?php echo esc_attr($this->wechat_reply_func_settings['latest_hot_reply_func']); ?>"/>
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
        $tab = isset($_GET['tab']) ? $_GET['tab'] : $this->wechat_general_settings_key;
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
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : $this->wechat_general_settings_key;

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
            $this->plugin_slug . "-" . 'settings',
            array(&$this, 'display_plugin_admin_page')
        );
//        //add latest news sub menu
//        add_submenu_page(
//            $this->plugin_slug . "-" . 'news',
//            __('Latest-news', $this->plugin_slug),
//            __('Latest-news', $this->plugin_slug),
//            'activate_plugins',
//            $this->plugin_slug . "-" . 'news',
//            array(&$this, 'display_plugin_admin_page')
//        );
        //add setting sub menu
        add_submenu_page(
            $this->plugin_slug . "-" . 'settings',
            __('General-Settings', $this->plugin_slug),
            __('General-Settings', $this->plugin_slug),
            'activate_plugins',
            $this->plugin_slug . "-" . 'settings',
            array(&$this, 'display_plugin_admin_page')
        );

        //add custom-menu sub menu
//        add_submenu_page(
//            $this->plugin_slug . "-" . 'news',
//            __('Custom-menu', $this->plugin_slug),
//            __('Custom-menu', $this->plugin_slug),
//            'activate_plugins',
//            $this->plugin_slug . "-" . 'menu',
//            array(&$this, 'display_plugin_admin_page')
//        );
//        //add custom-reply sub menu
//        add_submenu_page(
//            $this->plugin_slug . "-" . 'news',
//            __('Custom-reply', $this->plugin_slug),
//            __('Custom-reply', $this->plugin_slug),
//            'activate_plugins',
//            $this->plugin_slug . "-" . 'reply',
//            array(&$this, 'display_plugin_admin_page')
//        );



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
