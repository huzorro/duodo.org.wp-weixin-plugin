<?php
/**
 * Created by PhpStorm.
 * User: huzorro@gmail.com
 * Date: 14-1-23
 * Time: 下午2:42
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Wechat_users_list_table extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct()
    {
        global $status, $page;

        $plugin = Plugin_Name::get_instance();
        $this->plugin_slug = $plugin->get_plugin_slug();

        parent::__construct(array(
            'singular' => 'user',
            'plural' => 'users',
        ));
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_headimgurl($item)
    {
        $actions = array(
            'view' => sprintf('<a href="?page=msg_mutual&openid=%s">%s</a>', $item['openid'], __('History', $this->plugin_slug))
        );
        return sprintf("<img src='%s' alt='%s'/> %s", $item['headimgurl'], $item['nickname'], $this->row_actions($actions));
    }

    function column_gender($item)
    {
        return $item['gender'] == '0' ? __('Female', $this->plugin_slug) : __('Male', $this->plugin_slug);
    }
    function column_status($item) {
        return $item['status'] == '1' ? __('subscribe', $this->plugin_slug) : __('unsubscribe', $this->plugin_slug);
    }
    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_openid($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=users_form&id=%s">%s</a>', $item['id'], __('Edit')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete')),
        );
        return sprintf('%s %s',
            $item['openid'],
            $this->row_actions($actions)
        );
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'openid' => __('openid', $this->plugin_slug),
            'headimgurl' => __('headimg', $this->plugin_slug),
            'nickname' => __('nickname', $this->plugin_slug),
            'gender' => __('gender', $this->plugin_slug),
            'city' => __('city', $this->plugin_slug),
            'country' => __('country', $this->plugin_slug),
            'province' => __('province', $this->plugin_slug),
            'language' => __('language', $this->plugin_slug),
            'updatetime' => __('updatetime', $this->plugin_slug),
            'status' => __('status', $this->plugin_slug)
        );
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'updatetime' => array('updatetime', true),
            'gender' => array('gender', false),
            'city' => array('city', false),
            'country' => array('country', false),
            'province' => array('province', false),
            'status' => array('status', false)
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('Delete')
        );
        return $actions;
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wechat_users';

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function get_views(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'wechat_users';
        $province = $wpdb->get_results($wpdb->prepare("SELECT province, COUNT(*) AS N FROM $table_name GROUP BY province", ''), ARRAY_A);
        $allN = 0;
        foreach($province as $k => $provinceN) {
            $allN += $provinceN['N'];
            $class = (isset($_REQUEST['province']) && $_REQUEST['province'] == $provinceN['province']) ? 'class="current"' : '';
            $province_group[] = "<a $class href='" . esc_url( add_query_arg( 'province', $provinceN['province']) ) . "'>".sprintf( _nx( ''.$provinceN['province'].' <span class=count>(%s)</span>', ''. $provinceN['province'].' <span class=count>(%s)</span>', $provinceN['N'], $this->plugin_slug), number_format_i18n($provinceN['N'] )) ."</a>";
            unset($class);
        }
        $class = !isset($_REQUEST['province']) ? 'class="current"' : '';
        $allV = "<a $class href='" . esc_url( remove_query_arg( 'province') ) . "'>".sprintf( _nx( __('All', $this->plugin_slug) .' <span class=count>(%s)</span>', ''. __('All', $this->plugin_slug).' <span class=count>(%s)</span>', $allN, $this->plugin_slug), number_format_i18n($allN)) ."</a>";
        isset($province_group) && array_unshift($province_group, $allV);
        return isset($province_group) ? $province_group: array($allV);

    }
    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wechat_users'; // do not forget about tables prefix

        $per_page = 5; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
//        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name ");

        $condition = isset($_REQUEST['s']) ? " WHERE nickname LIKE '%%$_REQUEST[s]%%' OR city LIKE '%%$_REQUEST[s]%%' OR province LIKE '%%$_REQUEST[s]%%' " : '';

        $condition = isset($_REQUEST['province']) ? " WHERE province = '$_REQUEST[province]' " : $condition;

        $total_items = $wpdb->get_var(sprintf("SELECT COUNT(id) FROM $table_name %s", $condition));

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'updatetime';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
//        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged * $per_page), ARRAY_A);
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name $$condition ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged * $per_page), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}