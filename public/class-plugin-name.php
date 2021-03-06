<?php
/**
 * Plugin Name.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package Plugin_Name
 * @author  Your Name <email@example.com>
 */
class Plugin_Name {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.5';

	/**
	 * @TODO - Rename "plugin-name" to the name your your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'duodo.org.wp-weixin-plugin';

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
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( '@TODO', array( $this, 'filter_method_name' ) );

        self::wechat_update_db_check();
	}

    public static function wechat_update_db_check() {
        if(get_site_option('wechat_db_version') != self::VERSION) {
            self::single_activate();
        }
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

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
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
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
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
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog(  $blog_id);
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

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
        global $wpdb;

        $sql = sprintf('CREATE TABLE %swechat_custom_replys (
                            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                            keyword varchar(50) NOT NULL DEFAULT "" COMMENT "关键字",
                            reply_content varchar(500) NOT NULL DEFAULT "" COMMENT "回复内容",
                            reply_type varchar(100) NOT NULL DEFAULT "" COMMENT "回复类型",
                            status varchar(10) NOT NULL DEFAULT "0" "状态",
                            createtime timestamp NOT NULL DEFAULT "0000-00-00 00:00:00" COMMENT "创建时间",
                            updatetime timestamp NOT NULL DEFAULT "0000-00-00 00:00:00" COMMENT "更新时间",
                            PRIMARY KEY (`id`),
                            KEY `keyword` (`keyword`)
                        )ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
                        CREATE TABLE %swechat_latest_news (
                            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                            openid varchar(100) NOT NULL DEFAULT "",
                            createtime timestamp NOT NULL DEFAULT "0000-00-00 00:00:00",
                            news_type varchar(100) NOT NULL DEFAULT "" COMMENT "消息类型",
                            news_keyword varchar(500) NOT NULL DEFAULT "" COMMENT "消息内容关键字",
                            news_packet varchar(500) NOT NULL DEFAULT "" COMMENT "完整消息包",
                            PRIMARY KEY (`id`),
                            KEY `openid` (`openid`)
                        )ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
                        CREATE TABLE %swechat_reply_content (
                            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                            openid varchar(100) NOT NULL DEFAULT "",
                            reply_type varchar(100) NOT NULL DEFAULT "" COMMENT "回复类型",
                            reply_content varchar(500) NOT NULL DEFAULT "" COMMENT "回复内容",
                            reply_packet varchar(1000) NOT NULL DEFAULT "" COMMENT "完整消息包",
                            createtime timestamp NOT NULL DEFAULT "0000-00-00 00:00:00" COMMENT "创建时间",
                            updatetime timestamp NOT NULL DEFAULT "0000-00-00 00:00:00" COMMENT "更新时间",
                            PRIMARY KEY (`id`),
                            KEY `openid` (`openid`)
                        )ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
                        CREATE TABLE %swechat_users (
                            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                            openid varchar(100) NOT NULL DEFAULT "",
                            nickname varchar(100) NOT NULL DEFAULT "",
                            gender varchar(10) NOT NULL DEFAULT "",
                            city varchar(30) NOT NULL DEFAULT "",
                            country varchar(30) NOT NULL DEFAULT "",
                            province varchar(30) NOT NULL DEFAULT "",
                            language varchar(30) NOT NULL DEFAULT "",
                            headimgurl varchar(500) NOT NULL DEFAULT "",
                            user_packet varchar(1000) NOT NULL DEFAULT "",
                            createtime timestamp NOT NULL DEFAULT "0000-00-00 00:00:00" COMMENT "创建时间",
                            updatetime timestamp NOT NULL DEFAULT "0000-00-00 00:00:00" COMMENT "更新时间",
                            status varchar(10) NOT NULL DEFAULT "",
                            PRIMARY KEY (`id`),
                            KEY `openid` (`openid`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
                        CREATE TABLE %swechat_menu (
                            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                            item varchar(1000) NOT NULL DEFAULT "",
                            createtime timestamp NOT NULL DEFAULT "0000-00-00 00:00:00" COMMENT "创建时间",
                            updatetime timestamp NOT NULL DEFAULT "0000-00-00 00:00:00" COMMENT "更新时间",
                            PRIMARY KEY(`id`)
                        )ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
                        CREATE TABLE %swechat_msg_mutual (
                            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                            openid varchar(100) NOT NULL DEFAULT "",
                            msg_type varchar(100) NOT NULL DEFAULT "" COMMENT "消息类型",
                            mutual_type varchar(100) NOT NULL DEFAULT ""  COMMENT "消息交互类型",
                            mutual_keyword varchar(100) NOT NULL DEFAULT ""  COMMENT "消息交互关键字",
                            mutual_packet varchar(1000) NOT NULL DEFAULT ""  COMMENT "消息交互消息包",
                            createtime timestamp NOT NULL DEFAULT "0000-00-00 00:00:00",
                            PRIMARY KEY (`id`),
                            KEY `openid` (`openid`)
                        )ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
                        ', $wpdb->prefix, $wpdb->prefix, $wpdb->prefix, $wpdb->prefix, $wpdb->prefix,$wpdb->prefix);

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('wechat_db_version', self::VERSION);

        $installed_option = get_option('wechat_db_version');
        if($installed_option != self::VERSION) {
            //@TODO upgrade
            $sql = "";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            update_option('wechat_db_version', self::VERSION);
        }

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
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

//		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_textdomain( $domain, trailingslashit(WP_PLUGIN_DIR) . $domain . '/languages/' . $domain . '-' . $locale . '.mo' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}
