<?php
/**
 * Created by PhpStorm.
 * User: huzorro@gmail.com
 * Date: 14-1-23
 * Time: 下午5:29
 */
require_once(plugin_dir_path( __FILE__ ) . 'includes/class-wechat-custom-replys-list-table.php');

class Wechat_custom_replys_main {
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
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        // Add the options page and menu item.
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

        // Add an action link pointing to the options page.
        $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
        add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

        /*
         * Define custom functionality.
         *
         * Read more about actions and filters:
         * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
         */
        add_action( '@TODO', array( $this, 'action_method_name' ) );
        add_filter( '@TODO', array( $this, 'filter_method_name' ) );

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
         *    $this->plugin_screen_hook_suffix = add_options_page(
         *   __( 'Page Title', $this->plugin_slug ),
         *    __( 'Menu Text', $this->plugin_slug ),
         *   'manage_options',
         *   $this->plugin_slug,
         *   array( $this, 'display_plugin_admin_page' )
         *    );
         */
        add_submenu_page(
            $this->plugin_slug . "-" . 'settings',
            __('Custom-replys', $this->plugin_slug),
            __('Custom-replys', $this->plugin_slug),
            'activate_plugins',
            'custom_replys',
            array(&$this, 'wechat_custom_replys_page_handler')
        );
        add_submenu_page(
            $this->plugin_slug . "-" . 'settings',
            __('Add Custom-replys', $this->plugin_slug),
            __('Add Custom-replys', $this->plugin_slug),
            'activate_plugins',
            'custom_reply_form',
            array(&$this, 'wechat_custom_replys_form_page_handler')
        );
    }
    /**
     * List page handler
     *
     * This function renders our custom table
     * Notice how we display message about successfull deletion
     * Actualy this is very easy, and you can add as many features
     * as you want.
     *
     * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
     */
    function wechat_custom_replys_page_handler()
    {
        global $wpdb;

        $table = new Wechat_custom_replys_list_table();
        $table->prepare_items();

        $message = '';
        if ('delete' === $table->current_action()) {
            $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', $this->plugin_slug), count($_REQUEST['id'])) . '</p></div>';
        }
        ?>
        <div class="wrap">

            <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h2><?php _e('custom reply', $this->plugin_slug)?> <a class="add-new-h2"
                                                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=custom_reply_form');?>"><?php _e('Add new', $this->plugin_slug)?></a>
            </h2>
            <?php echo $message; ?>

            <form id="persons-table" method="GET">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <?php $table->display() ?>
            </form>

        </div>
    <?php
    }

    /**
     * PART 4. Form for adding andor editing row
     * ============================================================================
     *
     * In this part you are going to add admin page for adding andor editing items
     * You cant put all form into this function, but in this example form will
     * be placed into meta box, and if you want you can split your form into
     * as many meta boxes as you want
     *
     * http://codex.wordpress.org/Data_Validation
     * http://codex.wordpress.org/Function_Reference/selected
     */

    /**
     * Form page handler checks is there some data posted and tries to save it
     * Also it renders basic wrapper in which we are callin meta box render
     */
    function wechat_custom_replys_form_page_handler()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wechat_custom_replys'; // do not forget about tables prefix

        $message = '';
        $notice = '';

        // this is default $item which will be used for new records
        $default = array(
            'id' => 0,
            'keyword' => '',
            'reply_content' => '',
            'reply_type' => '',
            'status' => ''
        );

        // here we are verifying does this request is post back and have correct nonce
        if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
            // combine our default item with request params
            $item = shortcode_atts($default, $_REQUEST);
            // validate data, and if all ok save item to database
            // if id is zero insert otherwise update
            $item_valid = self::wechat_custom_replys_validate($item);
            if ($item_valid === true) {
                if ($item['id'] == 0) {
                    $result = $wpdb->insert($table_name, $item);
                    $item['id'] = $wpdb->insert_id;
                    if ($result) {
                        $message = __('Item was successfully saved', $this->plugin_slug);
                    } else {
                        $notice = __('There was an error while saving item', $this->plugin_slug);
                    }
                } else {
                    $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                    if ($result) {
                        $message = __('Item was successfully updated', $this->plugin_slug);
                    } else {
                        $notice = __('There was an error while updating item', $this->plugin_slug);
                    }
                }
            } else {
                // if $item_valid not true it contains error message(s)
                $notice = $item_valid;
            }
        }
        else {
            // if this is not post back we load item to edit or give new one to create
            $item = $default;
            if (isset($_REQUEST['id'])) {
                $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
                if (!$item) {
                    $item = $default;
                    $notice = __('Item not found', $this->plugin_slug);
                }
            }
        }

        // here we adding our custom meta box
        add_meta_box('wechat_custom_replys_form_meta_box', __('custom replys data', $this->plugin_slug), array(&$this, 'wechat_custom_replys_form_meta_box_handler'), 'reply', 'normal', 'default');
        ?>
        <div class="wrap">
            <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h2><?php _e('custom reply', $this->plugin_slug)?>
                <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=custom_replys');?>"><?php _e('back to list', $this->plugin_slug)?></a>
            </h2>

            <?php if (!empty($notice)): ?>
                <div id="notice" class="error"><p><?php echo $notice ?></p></div>
            <?php endif;?>
            <?php if (!empty($message)): ?>
                <div id="message" class="updated"><p><?php echo $message ?></p></div>
            <?php endif;?>

            <form id="form" method="POST">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
                <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
                <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

                <div class="metabox-holder" id="poststuff">
                    <div id="post-body">
                        <div id="post-body-content">
                            <?php /* And here we call our custom meta box */ ?>
                            <?php do_meta_boxes('reply', 'normal', $item); ?>
                            <input type="submit" value="<?php _e('Save', $this->plugin_slug)?>" id="submit" class="button-primary" name="submit">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <?php
    }

    /**
     * This function renders our custom meta box
     * $item is row
     *
     * @param $item
     */
    function wechat_custom_replys_form_meta_box_handler($item)
    {
        $selected = isset( $item['status'] ) ? esc_attr( $item['status'] ) : "";
        $check = isset( $item['reply_type'] ) ? esc_attr( $item['reply_type'] ) : '';
        $type = array_merge(get_option('wechat_msgtype_desc_settings'), get_option('wechat_reply_func_settings') );
        ?>

        <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
            <tbody>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="keyword"><?php _e('keyword', $this->plugin_slug)?></label>
                </th>
                <td>
                    <input id="keyword" name="keyword" type="text" style="width: 95%" value="<?php echo esc_attr($item['keyword'])?>"
                           size="50" class="code" placeholder="<?php _e('keyword', $this->plugin_slug)?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="reply_content"><?php _e('reply content', $this->plugin_slug)?></label>
                </th>
                <td>
<!--                    <input id="reply_content" name="reply_content" type="text" style="width: 95%" value="<?php /*echo esc_attr($item['reply_content'])*/?>"
                           size="50" class="code" placeholder="<?php /*_e('reply content', $this->plugin_slug)*/?>" required>-->
                    <textarea name="reply_content" type="text" id="reply_content"
                              cols="50" rows="6" value="<?php echo esc_attr($item['reply_content'])?>" required>
                        <?php echo esc_attr($item['reply_content'])?>
                    </textarea>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="reply_type"><?php _e('reply type', $this->plugin_slug)?></label>
                </th>
                <td>
<!--                    <input id="reply_type" name="reply_type" type="text" style="width: 95%" value="--><?php //echo esc_attr($item['reply_type'])?><!--"-->
<!--                           size="50" class="code" placeholder="--><?php //_e('reply type', $this->plugin_slug)?><!--" required>-->
                    <select name="reply_type" id="reply_type">
                    <?php
                    foreach($type as $key => $value) {
                    ?>
                            <option value="<?php echo $key?>" <?php selected( $check, $key ); ?>><?php echo $value;?></option>
                    <?php
                    }
                    ?>
                    </select>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="status"><?php _e('status', $this->plugin_slug)?></label>
                </th>
                <td>
                    <select name="status" id="status">
                        <option value="enable" <?php selected( $selected, 'enable' ); ?>><?php _e("enable", $this->plugin_slug);?></option>
                        <option value="disable" <?php selected( $selected, 'disable' ); ?>><?php _e("disable", $this->plugin_slug);?></option>
                    </select>

                </td>
            </tr>
            </tbody>
        </table>
    <?php
    }
    /**
     * This function renders our custom meta box
     * $item is row
     *
     * @param $item
     */
    function wechat_custom_replys_type_form_meta_box_handler($item)
    {
        $check = isset( $item['replys_type'] ) ? esc_attr( $item['replys_type'] ) : '';
        $msg_type = get_option("wechat_msgtype_desc_settings");
        ?>

        <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
            <tbody>

                <?php
                    foreach($msg_type as $key => $value) {
                ?>
                        <tr class="form-field">
                            <td>
                                <input type="radio" id="replys_type" name="replys_type" <?php checked( $check, $key ); ?> />
                                <label for="my_meta_box_check"><?php echo $value?></label>
                            </td>
                        </tr>
                <?php
                    }
                ?>
            </tbody>
        </table>
    <?php
    }
    /**
     * Simple function that validates data and retrieve bool on success
     * and error message(s) on error
     *
     * @param $item
     * @return bool|string
     */
    function wechat_custom_replys_validate($item)
    {
        $messages = array();

        if (empty($item['keyword'])) $messages[] = __('keyword is required', $this->plugin_slug);
        if (empty($item['reply_content'])) $messages[] = __('reply content is required', $this->plugin_slug);
        if (empty($item['reply_type'])) $messages[] = __('reply type is required', $this->plugin_slug);


        if (empty($messages)) return true;
        return implode('<br />', $messages);
    }
    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {


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