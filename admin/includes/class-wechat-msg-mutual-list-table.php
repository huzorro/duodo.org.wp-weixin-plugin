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

class Wechat_msg_mutual_list_table extends WP_List_Table
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
            'singular' => 'mutual',
            'plural' => 'mutuals',
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
    function column_msg_type($item)
    {
        $type = array_merge(get_option('wechat_msgtype_desc_settings'), get_option('wechat_reply_func_settings') );
        return $type[$item['mutual_type']];
    }
    function column_mutual_type($item)
    {

        return $item['mutual_type'] == "receive" ? __('receive', $this->plugin_slug) : __('send', $this->plugin_slug);
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
            'edit' => sprintf('<a href="?page=reply_content_form&id=%s">%s</a>', $item['id'], __('Edit')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete')),
        );

        global $wpdb;
        $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM %s WHERE openid = '%s'", $wpdb->prefix . 'wechat_users'), ARRAY_A);
        var_dump($user);
        return sprintf('%s %s',
            isset($user['nickname']) ? $user['nickname'] : $item['openid'],
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
            'mutual_type' => __('mutual type', $this->plugin_slug),
            'mutual_keyword' => __('mutual keyword', $this->plugin_slug),
            'msg_type' => __('msg type', $this->plugin_slug),
            'createtime' => __('createtime', $this->plugin_slug)
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
        $table_name = $wpdb->prefix . 'wechat_msg_mutual';

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
        $table_name = $wpdb->prefix . 'wechat_msg_mutual';
        $type = array('receive' => __('receive', $this->plugin_slug), 'send' => __('send', $this->plugin_slug));

        $type_count = $wpdb->get_results($wpdb->prepare("SELECT mutual_type, COUNT(*) AS N FROM $table_name GROUP BY mutual_type", ''), ARRAY_A);
        $allN = 0;
        foreach($type_count as $k => $typeN) {
            $allN += $typeN['N'];
            $class = (isset($_REQUEST['mutual_type']) && $_REQUEST['mutual_type'] == $typeN['mutual_type']) ? 'class="current"' : '';
            $type_group[] = "<a $class href='" . esc_url( add_query_arg( 'mutual_type', $typeN['mutual_type']) ) . "'>".sprintf( _nx( ''.$type[$typeN['mutual_type']].' <span class=count>(%s)</span>', ''. $type[$typeN['mutual_type']].' <span class=count>(%s)</span>', $typeN['N'], $this->plugin_slug), number_format_i18n($typeN['N'] )) ."</a>";
            unset($class);
        }
        $class = !isset($_REQUEST['mutual_type']) ? 'class="current"' : '';
        $allV = "<a $class href='" . esc_url( remove_query_arg( 'mutual_type') ) . "'>".sprintf( _nx( __('All', $this->plugin_slug) .' <span class=count>(%s)</span>', ''. __('All', $this->plugin_slug).' <span class=count>(%s)</span>', $allN, $this->plugin_slug), number_format_i18n($allN)) ."</a>";
        isset($type_group) && array_unshift($type_group, $allV);
        return isset($type_group) ? $type_group : array($allV);

    }
    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wechat_msg_mutual'; // do not forget about tables prefix

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

        $condition = isset($_REQUEST['s']) ? " WHERE mutual_keyword LIKE '%%$_REQUEST[s]%%' OR mutual_type LIKE '%%$_REQUEST[s]%%' OR openid LIKE '%%$_REQUEST[s]%%' " : '';

        $condition = isset($_REQUEST['mutual_type']) ? " WHERE reply_type = '$_REQUEST[mutual_type]' " : $condition;

        $total_items = $wpdb->get_var(sprintf("SELECT COUNT(id) FROM $table_name %s", $condition));

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'openid, createtime';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : '';

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