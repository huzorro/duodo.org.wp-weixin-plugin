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

class Wechat_custom_replys_list_table extends WP_List_Table
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
            'singular' => 'reply',
            'plural' => 'replys',
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
    function column_reply_type($item)
    {
        $type = array_merge(get_option('wechat_msgtype_desc_settings'), get_option('wechat_reply_func_settings') );
        return $type[$item['reply_type']];
    }

    function column_status($item) {
        return __($item['status'], $this->plugin_slug);
    }
    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_keyword($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=custom_reply_form&id=%s">%s</a>', $item['id'], __('Edit', $this->plugin_slug)),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', $this->plugin_slug)),
        );

        return sprintf('%s %s',
            $item['keyword'],
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
            'keyword' => __('Keyword', $this->plugin_slug),
            'reply_content' => __('Reply content', $this->plugin_slug),
            'reply_type' => __('Reply type', $this->plugin_slug),
            'status' => __('status', $this->plugin_slug),
            'updatetime' => __('update time', $this->plugin_slug)
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
            'keyword' => array('keyword', false),
            'reply_type' => array('reply_type', false),
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
            'delete' => __('Delete', $this->plugin_slug)
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
        $table_name = $wpdb->prefix . 'wechat_custom_replys'; // do not forget about tables prefix

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
        $table_name = $wpdb->prefix . 'wechat_custom_replys';
        $type = array_merge(get_option('wechat_msgtype_desc_settings'), get_option('wechat_reply_func_settings') );
        var_dump($type);
//        $sql = sprintf("SELECT reply_type, COUNT(*) AS N FROM $table_name GROUP BY reply_type");
//
//        var_dump($type);
//        $type_count = $wpdb->get_results($wpdb->prepare($sql), ARRAY_A);
//        var_dump($type_count);

//        foreach($type as $key => $value) {
//            $_REQUEST["reply_type"]== $key && $class='class="current"';
//            foreach($type_count as $k => $v) {
//                if($v['reply_type'] != $key)  continue;
//                $type_group[]   = "<a $class href='" . esc_url( add_query_arg( 'reply_type', $key, $this->redirect ) ) . "'>".sprintf( _nx( ''.$value.' <span class=count>(%s)</span>', ''.$value.' <span class=count>(%s)</span>', $v['N'], 'posts' ), number_format_i18n($v['N']) ) ."</a>";
//            }
//
//            unset($class);
//        }

        return $type_group[] = "<a href='#'>test</a>";

    }
    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wechat_custom_replys'; // do not forget about tables prefix

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

        $condition = isset($_REQUEST['s']) ? " WHERE keyword LIKE '%%$_REQUEST[s]%%' OR reply_content LIKE '%%$_REQUEST[s]%%' " : '';

        $condition = empty($condition) && isset($_REQUEST['reply_type']) ? " WHERE reply_type = $_REQUEST[reply_type] " : $condition;

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