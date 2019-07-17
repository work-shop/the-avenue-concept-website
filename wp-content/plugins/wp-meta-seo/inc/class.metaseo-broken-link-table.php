<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class MetaSeoBrokenLinkTable
 * Base class for displaying a list of links in an ajaxified HTML table.
 */
class MetaSeoBrokenLinkTable extends WP_List_Table
{
    /**
     * Img pattern
     *
     * @var string
     */
    public static $img_pattern = '/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i';
    /**
     * Old URL
     *
     * @var string
     */
    public static $old_url = '';
    /**
     * New URL
     *
     * @var string
     */
    public static $new_url = '';

    /**
     * MetaSeoBrokenLinkTable constructor.
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'metaseo_image',
            'plural'   => 'metaseo_images',
            'ajax'     => true
        ));
    }

    /**
     * Generate the table navigation above or below the table
     *
     * @param string $which Possition of table nav
     *
     * @return void
     */
    protected function display_tablenav($which) // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        if (!empty($post_types['attachment'])) {
            unset($post_types['attachment']);
        }
        ?>
        <div class="<?php echo esc_attr('tablenav ' . $which); ?>">

            <?php if ($which === 'top') : ?>
                <input type="hidden" name="page" value="metaseo_image_meta"/>

                <div class="alignleft actions bulkactions">
                    <?php
                    $this->btnFilter();
                    $this->flushFilter('sl_flush');
                    ?>
                </div>
                <div style="float:right;margin-left:5px;">
                    <label style="float: left">
                        <input type="number" required
                               value="<?php echo esc_attr($this->_pagination_args['per_page']) ?>"
                               maxlength="3" name="metaseo_broken_link_per_page"
                               class="metaseo_imgs_per_page screen-per-page"
                               max="999" min="1" step="1">
                    </label>

                    <button type="submit" name="btn_perpage"
                            class="button_perpage ju-button orange-button waves-effect waves-light"
                            id="button_perpage"><?php esc_html_e('Apply', 'wp-meta-seo') ?></button>
                </div>
            <?php endif ?>

            <input type="hidden" name="page" value="metaseo_image_meta"/>

            <?php if ($which === 'bottom') : ?>
                <?php $this->pagination('top'); ?>
            <?php endif ?>
            <br class="clear"/>
        </div>

        <?php
    }

    /**
     * Display the pagination.
     *
     * @param string $which Possition
     *
     * @return void
     */
    protected function pagination($which)
    {
        if (empty($this->_pagination_args)) {
            return;
        }

        $total_items     = (int) $this->_pagination_args['total_items'];
        $total_pages     = (int) $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if (isset($this->_pagination_args['infinite_scroll'])) {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ('top' === $which && $total_pages > 1) {
            $this->screen->render_screen_reader_content('heading_pagination');
        }

        $output = '<span class="displaying-num">' . sprintf(_n('%s item', '%s items', $total_items, 'wp-meta-seo'), number_format_i18n($total_items)) . '</span>';

        $current              = (int) $this->get_pagenum();
        $removable_query_args = wp_removable_query_args();

        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        $current_url = remove_query_arg($removable_query_args, $current_url);

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = false;
        $disable_last  = false;
        $disable_prev  = false;
        $disable_next  = false;

        if ($current === 1) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ($current === 2) {
            $disable_first = true;
        }

        if ($current === $total_pages) {
            $disable_last = true;
            $disable_next = true;
        }
        if ($current === $total_pages - 1) {
            $disable_last = true;
        }

        if ($disable_first) {
            $page_links[] = '<a class="wpms-number-page first-page disable"><i class="material-icons">first_page</i></a>';
        } else {
            $page_links[] = sprintf(
                "<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><i class='material-icons'>%s</i></a>",
                esc_url(remove_query_arg('paged', $current_url)),
                __('First page', 'wp-meta-seo'),
                'first_page'
            );
        }

        if ($disable_prev) {
            $page_links[] = '<a class="wpms-number-page prev-page disable"><i class="material-icons">keyboard_backspace</i></a>';
        } else {
            $page_links[] = sprintf(
                "<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><i class='material-icons'>%s</i></a>",
                esc_url(add_query_arg('paged', max(1, $current - 1), $current_url)),
                __('Previous page', 'wp-meta-seo'),
                'keyboard_backspace'
            );
        }

        $begin = $current - 2;
        $end   = $current + 2;
        if ($begin < 1) {
            $begin = 1;
            $end   = $begin + 4;
        }
        if ($end > $total_pages) {
            $end   = $total_pages;
            $begin = $end - 4;
        }
        if ($begin < 1) {
            $begin = 1;
        }

        $custom_html = '';
        for ($i = $begin; $i <= $end; $i ++) {
            if ($i === $current) {
                $custom_html .= '<a class="wpms-number-page active" href="' . esc_url(add_query_arg('paged', $i, $current_url)) . '"><span class="screen-reader-text">' . esc_html($i) . '</span><span aria-hidden="true">' . esc_html($i) . '</span></a>';
            } else {
                $custom_html .= '<a class="wpms-number-page" href="' . esc_url(add_query_arg('paged', $i, $current_url)) . '"><span class="screen-reader-text">' . esc_html($i) . '</span><span aria-hidden="true">' . esc_html($i) . '</span></a>';
            }
        }
        $page_links[] = $total_pages_before . $custom_html . $total_pages_after;

        if ($disable_next) {
            $page_links[] = '<a class="wpms-number-page disable next-page"><i class="material-icons">trending_flat</i></a>';
        } else {
            $page_links[] = sprintf(
                "<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><i class='material-icons'>%s</i></a>",
                esc_url(add_query_arg('paged', min($total_pages, $current + 1), $current_url)),
                __('Next page', 'wp-meta-seo'),
                'trending_flat'
            );
        }

        if ($disable_last) {
            $page_links[] = '<a class="wpms-number-page last-page disable"><i class="material-icons">last_page</i></a>';
        } else {
            $page_links[] = sprintf(
                "<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><i class='material-icons'>%s</i></a>",
                esc_url(add_query_arg('paged', $total_pages, $current_url)),
                __('Last page', 'wp-meta-seo'),
                'last_page'
            );
        }

        $pagination_links_class = 'pagination-links';
        if (!empty($infinite_scroll)) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= '<span class="' . esc_html($pagination_links_class) . '">' . join('', $page_links) . '</span>';

        if ($total_pages) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = '<div class="tablenav-pages' . esc_html($page_class) . '">' . $output . '</div>';

        // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped
        echo $this->_pagination;
    }

    /**
     * Get a list of columns. The format is:
     * 'internal-name' => 'Title'
     *
     * @return array
     */
    public function get_columns() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        $columns = array(
            'col_link_url'  => esc_html__('URL', 'wp-meta-seo'),
            'col_hit'       => esc_html__('Hits number', 'wp-meta-seo'),
            'col_status'    => esc_html__('Status', 'wp-meta-seo'),
            'col_link_text' => esc_html__('Type or Link text', 'wp-meta-seo'),
            'col_source'    => esc_html__('Source', 'wp-meta-seo')
        );

        return $columns;
    }

    /**
     * Get a list of sortable columns. The format is:
     * 'internal-name' => 'orderby'
     * or
     * 'internal-name' => array( 'orderby', true )
     *
     * The second format will make the initial sorting order be descending
     *
     * @return array
     */
    protected function get_sortable_columns() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        $sortable = array(
            'col_status'   => array('status_text', true),
            'col_link_url' => array('link_url', true)
        );

        return $sortable;
    }

    /**
     * Print column headers, accounting for hidden and sortable columns.
     *
     * @param boolean $with_id Whether to set the id attribute or not
     *
     * @return void
     */
    public function print_column_headers($with_id = true) // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        list($columns, $hidden, $sortable) = $this->get_column_info();

        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg('paged', $current_url);

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        if (isset($_GET['orderby'])) {
            $current_orderby = $_GET['orderby'];
        } else {
            $current_orderby = '';
        }

        if (isset($_GET['order']) && 'desc' === $_GET['order']) {
            $current_order = 'desc';
        } else {
            $current_order = 'asc';
        }
        // phpcs:enable
        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text"
             for="' . esc_attr('cb-select-all-' . $cb_counter) . '">' . esc_html__('Select All', 'wp-meta-seo') . '</label>'
                             . '<input id="' . esc_attr('cb-select-all-' . $cb_counter) . '" type="checkbox" style="margin:0;" />';
            $cb_counter ++;
        }

        foreach ($columns as $column_key => $column_display_name) {
            $class = array('manage-column', 'column-' . $column_key);

            $style = '';
            if (in_array($column_key, $hidden)) {
                $style = 'style="display:none;"';
            }

            if ('cb' === $column_key) {
                $class[] = 'check-column';
            } elseif (in_array($column_key, array('posts', 'comments', 'links'))) {
                $class[] = 'num';
            }

            if (isset($sortable[$column_key])) {
                list($orderby, $desc_first) = $sortable[$column_key];

                if ($current_orderby === $orderby) {
                    $order   = 'asc' === $current_order ? 'desc' : 'asc';
                    $class[] = 'sorted';
                    $class[] = $current_order;
                } else {
                    $order   = $desc_first ? 'desc' : 'asc';
                    $class[] = 'sortable';
                    $class[] = $desc_first ? 'asc' : 'desc';
                }

                $hr                  = esc_url(add_query_arg(compact('orderby', 'order'), $current_url));
                $column_display_name = '<a href="' . esc_url($hr) . '">
<span>' . esc_html($column_display_name) . '</span>
<span class="sorting-indicator"></span></a>';
            }

            $id = $with_id ? 'id="' . esc_attr($column_key) . '"' : '';

            if (!empty($class)) {
                $class = "class='" . esc_attr(join(' ', $class)) . "'";
            }

            // phpcs:disable WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
            if ($column_key === 'cb') {
                echo '<th scope="col" ' . $id . ' ' . $class . ' style="padding:8px 10px;">' . $column_display_name . '</th>';
            } else {
                echo '<th scope="col" ' . $id . ' ' . $class . ' ' . $style . '>' . $column_display_name . '</th>';
            }
            // phpcs:enable
        }
    }

    /**
     * Prepares the list of items for displaying.
     *
     * @return void
     */
    public function prepare_items() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        global $wpdb;
        $where = array('1=1');
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        $where[]  = "type IN ('url', 'comment', '404_automaticaly', 'add_custom')";
        $where_or = array();
        if (!empty($_REQUEST['sl_broken'])) {
            if (in_array('custom_redirect_url', $_REQUEST['sl_broken'])) {
                $where_or[] = 'link_url_redirect !=""';
            }

            if (in_array('valid_links', $_REQUEST['sl_broken'])) {
                $where_or[] = 'broken_internal = 0 AND broken_indexed = 0';
            }

            if (in_array('internal_broken_links', $_REQUEST['sl_broken'])) {
                $where_or[] = 'broken_internal = 1';
            }

            if (in_array('automaticaly_indexed', $_REQUEST['sl_broken'])) {
                $where_or[] = 'broken_indexed = 1';
            }

            if (in_array('not_yet_redirect', $_REQUEST['sl_broken'])) {
                $where_or[] = '(broken_internal = 1 OR broken_indexed = 1) AND link_url_redirect = ""';
            }
        } else {
            // default
            $where_or[] = 'broken_internal = 1';
            $where_or[] = 'broken_indexed = 1';
            $where_or[] = 'link_url_redirect = ""';
        }

        $where_or_string = implode(' OR ', $where_or);
        $where[]         = '(' . $where_or_string . ')';

        $keyword = !empty($_GET['txtkeyword']) ? $_GET['txtkeyword'] : '';
        if (isset($keyword) && $keyword !== '') {
            $where[] .= $wpdb->prepare('(link_text LIKE %s OR link_url LIKE %s)', array(
                '%' . $keyword . '%',
                '%' . $keyword . '%'
            ));
        }

        $orderby = !empty($_GET['orderby']) ? ($_GET['orderby']) : 'id';
        $order   = !empty($_GET['order']) ? ($_GET['order']) : 'desc';
        $paged   = !empty($_GET['paged']) ? $_GET['paged'] : '';
        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }

        if (!empty($_REQUEST['metaseo_broken_link_per_page'])) {
            $_per_page = intval($_REQUEST['metaseo_broken_link_per_page']);
        } else {
            $_per_page = 0;
        }
        // phpcs:enable
        $per_page = get_user_option('metaseo_broken_link_per_page');
        if ($per_page !== false) {
            if ($_per_page && $_per_page !== $per_page) {
                $per_page = $_per_page;
                update_user_option(get_current_user_id(), 'metaseo_broken_link_per_page', $per_page);
            }
        } else {
            if ($_per_page > 0) {
                $per_page = $_per_page;
            } else {
                $per_page = 10;
            }
            add_user_meta(get_current_user_id(), 'metaseo_broken_link_per_page', $per_page);
        }

        $sortable      = $this->get_sortable_columns();
        $orderby_array = array($orderby, true);
        if (in_array($orderby_array, $sortable)) {
            $orderStr = $orderby;
        } else {
            $orderStr = 'id';
        }

        if ($order === 'asc') {
            $orderStr .= ' ASC';
        } else {
            $orderStr .= ' DESC';
        }

        if (!empty($orderby) & !empty($order)) {
            $orderStr = ' ORDER BY ' . esc_sql($orderStr) . ' ';
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $total_items           = $wpdb->get_var('SELECT COUNT(id) FROM ' . $wpdb->prefix . 'wpms_links WHERE ' . implode(' AND ', $where) . $orderStr);
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $query                 = 'SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE ' . implode(' AND ', $where) . $orderStr;

        $total_pages = ceil($total_items / $per_page);

        if (!empty($paged) && !empty($per_page)) {
            $offset = ($paged - 1) * $per_page;
            $query  .= $wpdb->prepare(' LIMIT %d, %d', array($offset, $per_page));
        }

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'per_page'    => $per_page
        ));

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $this->items = $wpdb->get_results($query);
    }

    /**
     * Displays the search box.
     *
     * @return void
     */
    public function searchBox1()
    {
        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            require_once(WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/custom_redirect_form.php');
        }
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        $txtkeyword = (!empty($_REQUEST['txtkeyword'])) ? urldecode(stripslashes($_REQUEST['txtkeyword'])) : '';
        if (!empty($_REQUEST['orderby'])) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
        }

        if (!empty($_REQUEST['order'])) {
            echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
        }

        if (!empty($_REQUEST['post_mime_type'])) {
            echo '<input type="hidden" name="post_mime_type" value="' . esc_attr($_REQUEST['post_mime_type']) . '" />';
        }

        if (!empty($_REQUEST['detached'])) {
            echo '<input type="hidden" name="detached" value="' . esc_attr($_REQUEST['detached']) . '" />';
        }
        // phpcs:enable
        ?>
        <p class="search-box">
            <label>
                <input type="search" id="image-search-input" class="wpms-search-input" name="txtkeyword"
                       class="form-control"
                       value="<?php echo esc_attr(stripslashes($txtkeyword)); ?>"
                       placeholder="<?php esc_html_e('Search URL', 'wp-meta-seo') ?>"/>
            </label>

            <button type="submit" id="search-submit"><span class="dashicons dashicons-search"></span></button>
        </p>
        <?php
    }

    /**
     * Add filter broken
     *
     * @param string $name Selectbox name
     *
     * @return void
     */
    public function brokenFilter($name)
    {
        $brokens = array(
            'internal_broken_links' => esc_html__('Internal broken links', 'wp-meta-seo'),
            'automaticaly_indexed'  => esc_html__('404 automaticaly indexed', 'wp-meta-seo'),
        );
        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            $brokens['custom_redirect_url'] = esc_html__('Custom redirect URL', 'wp-meta-seo');
        }
        $brokens['valid_links']      = esc_html__('Valid links', 'wp-meta-seo');
        $brokens['not_yet_redirect'] = esc_html__('Not yet redirected', 'wp-meta-seo');
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        if (empty($_REQUEST['sl_broken'])) {
            $selected = array('automaticaly_indexed', 'internal_broken_links', 'not_yet_redirect');
        } else {
            $selected = $_REQUEST['sl_broken'];
        }
        // phpcs:enable
        ?>
        <label for="filter-by-broken"
               class="screen-reader-text"><?php esc_html_e('Filter by broken', 'wp-meta-seo'); ?></label>
        <div class="form-group form-group-broken">
            <select multiple name="<?php echo esc_attr($name) ?>" id="filter-by-broken"
                    class="broken_filter form-control">
                <?php
                foreach ($brokens as $k => $broken) {
                    if (in_array($k, $selected)) {
                        echo '<option selected value="' . esc_attr($k) . '">' . esc_html($broken) . '</option>';
                    } else {
                        echo '<option value="' . esc_attr($k) . '">' . esc_html($broken) . '</option>';
                    }
                }
                ?>
            </select>
            <input type="submit" name="filter_type_action" id="broken-submit"
                   class="ju-button orange-button wpms-small-btn wpms_right" style="height: 45px"
                   value="<?php esc_attr_e('Filter', 'wp-meta-seo') ?>">
        </div>
        <?php
    }

    /**
     * Add filter type
     *
     * @return void
     */
    public function btnFilter()
    {
        echo '<div style="float:left; margin-left: 5px">';
        echo '<div data-comment_paged="1" data-paged="1" class="ju-button orange-button wpms_scan wpms_scan_link">';
        esc_html_e('Index internal broken links', 'wp-meta-seo');
        echo '<div class="wpms_process ju-button" data-w="0"></div>';
        echo '</div></div>';
    }

    /**
     * Add filter flush
     *
     * @param string $name Filter name
     *
     * @return void
     */
    public function flushFilter($name)
    {
        $flushs = array(
            'automaticaly_indexed'  => esc_html__('Automatic indexed 404', 'wp-meta-seo'),
            'internal_broken_links' => esc_html__('Internal broken links', 'wp-meta-seo'),
            'all'                   => esc_html__('Flush all 404', 'wp-meta-seo')
        );
        ?>
        <div class="wpms_left">
            <label for="filter-by-flush"
                   class="screen-reader-text"><?php esc_html_e('Filter by flush', 'wp-meta-seo'); ?></label>
            <select name="<?php echo esc_attr($name) ?>" id="filter-by-flush">
                <option value="none"><?php esc_html_e('Remove URLs', 'wp-meta-seo'); ?></option>
                <?php
                foreach ($flushs as $k => $flush) {
                    echo '<option value="' . esc_attr($k) . '">' . esc_html($flush) . '</option>';
                }
                ?>
            </select>
            <input type="button" class="ju-button orange-button wpms_flush_link"
                   value="<?php esc_html_e('Flush', 'wp-meta-seo') ?>">
            <span class="spinner wpms-spinner wpms-spinner-flush"></span>
            <span class="wpms-msg-success flush-msg-success"><?php esc_html_e('URLs removed!', 'wp-meta-seo') ?></span>
            <span class="wpms-msg-error flush-msg-error"><?php esc_html_e('Please select a remove URL option to flush', 'wp-meta-seo') ?></span>
        </div>
        <?php
    }

    /**
     * Generate the table rows
     *
     * @return void
     */
    public function display_rows() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        add_thickbox();
        $records = $this->items;
        $i       = 0;
        list($columns, $hidden) = $this->get_column_info();
        if (!empty($records)) {
            foreach ($records as $rec) {
                $i ++;
                echo '<tr id="' . esc_attr('record_' . $i) . '" data-linkid="' . esc_attr($rec->id) . '"
                 data-link="' . esc_attr($i) . '" data-post_id="' . esc_attr($rec->source_id) . '">';
                foreach ($columns as $column_name => $column_display_name) {
                    switch ($column_name) {
                        case 'col_link_url':
                            if ($rec->type === 'url') {
                                $value_url = $rec->link_final_url;
                            } else {
                                $value_url = $rec->link_url;
                            }
                            echo '<td class="wpms_link_html">';
                            echo '<input type="hidden" class="wpms_link_text"
                             value="' . esc_attr($rec->link_text) . '">';
                            if ($rec->type === 'add_custom') {
                                if (strpos($rec->link_url, home_url()) !== false) {
                                    echo '<a class="link_html" target="_blank"
                                 href="' . esc_url($rec->link_url) . '">' . esc_html($value_url) . '</a>';
                                } else {
                                    echo '<a class="link_html" target="_blank"
                                 href="' . esc_url(home_url($rec->link_url)) . '">' . esc_html($value_url) . '</a>';
                                }

                                if ($rec->link_url_redirect !== '') {
                                    echo ' to ';
                                    if (strpos($rec->link_url_redirect, 'http://') !== false || strpos($rec->link_url_redirect, 'https://') !== false) {
                                        echo '<a class="link_html_redirect" target="_blank"
                                     href="' . esc_url($rec->link_url_redirect) . '"
                                    >' . esc_url($rec->link_url_redirect) . '</a>';
                                    } else {
                                        echo '<a class="link_html_redirect" target="_blank"
                                     href="' . esc_url(home_url($rec->link_url_redirect)) . '"
                                    >' . esc_html(str_replace(home_url(), '', $rec->link_url_redirect)) . '</a>';
                                    }
                                }
                            } else {
                                echo '<a class="link_html" target="_blank"
                                 href="' . esc_url($value_url) . '">' . esc_html($value_url) . '</a>';
                            }

                            $row_action = array(
                                'edit'    => '<a class="wpms_action_link wpms-edit-button"
                                 title="' . esc_attr__('Edit redirect', 'wp-meta-seo') . '">
<div class="wpms_icon_action"><i class="material-icons">mode_edit</i></div>
<span>' . esc_html__('Edit', 'wp-meta-seo') . '</span></a>',
                                'delete'  => '<a class="wpms_action_link submitdelete wpms-unlink-button"
                                 data-link_id="' . esc_attr($rec->id) . '" data-type="' . esc_attr($rec->type) . '"
                                  data-source_id="' . esc_attr($rec->source_id) . '"
                                  title="' . esc_attr__('Remove redirect or link', 'wp-meta-seo') . '">
                                  <div class="wpms_icon_action"><i class="material-icons">delete_forever</i></div>
                                  <span>' . esc_html__('Remove redirect', 'wp-meta-seo') . '</span></a>',
                                'recheck' => '<a class="wpms_action_link wpms-recheck-button"
                                 data-link_id="' . esc_attr($rec->id) . '" data-type="' . esc_attr($rec->type) . '"
                                  data-source_id="' . esc_attr($rec->source_id) . '"
                                  title="' . esc_attr__('Check the link', 'wp-meta-seo') . '">
                                  <div class="wpms_icon_action"><i class="material-icons">loop</i></div>
                                  <span>' . esc_html__('Check', 'wp-meta-seo') . '</span></a>'
                            );

                            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
                            echo $this->row_actions($row_action, false);
                            $iii = 0;
                            $jjj = 0;
                            if (!empty($rec->source_id)) {
                                if ($rec->type === 'url') {
                                    $pos = get_post($rec->source_id);
                                    if (!empty($pos)) {
                                        preg_match_all(
                                            '#<a[^>]*>.*?</a>#si',
                                            $pos->post_content,
                                            $matches,
                                            PREG_PATTERN_ORDER
                                        );
                                        foreach ($matches[0] as $i => $content) {
                                            preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                                            if (isset($matches[1])) {
                                                $href = $matches[1];
                                                if ($href === $rec->link_url) {
                                                    $iii ++;
                                                }
                                            }
                                        }
                                    }
                                } elseif ($rec->type === 'comment_content_url') {
                                    $com = get_comment($rec->source_id);
                                    if (!empty($pos)) {
                                        preg_match_all(
                                            '#<a[^>]*>.*?</a>#si',
                                            $com->comment_content,
                                            $matches,
                                            PREG_PATTERN_ORDER
                                        );
                                        foreach ($matches[0] as $i => $content) {
                                            preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                                            if (isset($matches[1])) {
                                                $href = $matches[1];
                                                if ($href === $rec->link_url) {
                                                    $jjj ++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            ?>
                            <div class="wpms-inline-editor-content">
                                <h4><?php esc_html_e('Edit Link', 'wp-meta-seo'); ?></h4>
                                <?php
                                if ($rec->type === 'url') {
                                    if ($iii > 1) {
                                        echo '<div class="form-group">
<label class="title">' . esc_html__('Text', 'wp-meta-seo') . '</label>
<input type="text" name="link_text" class="wpms-link-text-field form-control"
 placeholder="' . esc_attr__('Multiple link', 'wp-meta-seo') . '" data-type="multi" /></div>';
                                    } else {
                                        echo '<div class="form-group">
<label class="title">' . esc_html__('Text', 'wp-meta-seo') . '</label>
<input type="text" name="link_text" class="wpms-link-text-field form-control"
 value="' . esc_attr($rec->link_text) . '" data-type="only" /></div>';
                                    }
                                } elseif ($rec->type === 'comment_content_url') {
                                    if ($jjj > 1) {
                                        echo '<div class="form-group">
<label class="title">' . esc_html__('Text', 'wp-meta-seo') . '</label>
<input type="text" name="link_text" class="wpms-link-text-field form-control"
 placeholder="' . esc_attr__('Multiple link', 'wp-meta-seo') . '" data-type="multi" /></div>';
                                    } else {
                                        echo '<span class="form-group">
<label class="title">' . esc_html__('Text', 'wp-meta-seo') . '</label>
<input type="text" name="link_text" class="wpms-link-text-field form-control"
 value="' . esc_attr($rec->link_text) . '" data-type="only" /></span>';
                                    }
                                } else {
                                    if ($rec->type !== 'add_custom') {
                                        echo '<div class="form-group">
<label class="title">' . esc_html__('Text', 'wp-meta-seo') . '</label>
<input readonly type="text" name="link_text" class="wpms-link-text-field form-control" value="(None)" data-type="only" /></div>';
                                    } else {
                                        ?>
                                        <div class="form-group">
                                            <label class="title">
                                                <?php esc_html_e('Status', 'wp-meta-seo') ?>
                                            </label>
                                            <label>
                                                <select name="custom_redirect_status" class="custom_redirect_status">
                                                    <option value="301" <?php selected($rec->meta_title, 301) ?>>301
                                                    </option>
                                                    <option value="302" <?php selected($rec->meta_title, 302) ?>>302
                                                    </option>
                                                    <option value="307" <?php selected($rec->meta_title, 307) ?>>307
                                                    </option>
                                                </select>
                                            </label>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                                <div class="form-group">
                                    <label
                                            class="title">
                                        <?php esc_html_e('URL', 'wp-meta-seo'); ?>
                                    </label>
                                    <input <?php echo ($rec->type === '404_automaticaly') ? 'readonly' : '' ?>
                                            type="text" name="link_url" class="wpms-link-url-field form-control"
                                            value="<?php echo esc_attr($value_url); ?>"/>

                                </div>
                                <div class="form-group">
                                    <label class="title"><?php esc_html_e('Redirect', 'wp-meta-seo'); ?></label>
                                    <input type="text" name="link_url_redirect"
                                           class="wpms-link-redirect-field form-control" style="margin-right: 0"
                                           value="<?php echo esc_attr($rec->link_url_redirect); ?>"/>
                                    <span class="wlink-btn">
                                        <i class="mce-ico mce-i-link link-btn" id="link-btn"></i>
                                    </span>
                                </div>

                                <div class="submit wpms-inline-editor-buttons">
                                    <input type="button"
                                           class="ju-button wpms-small-btn
                                            cancel alignleft wpms-cancel-button"
                                           value="<?php echo esc_attr(__('Cancel', 'wp-meta-seo')); ?>"/>
                                    <input type="button" data-type="<?php echo esc_html($rec->type) ?>"
                                           data-link_id="<?php echo esc_attr($rec->id) ?>"
                                           data-source_id="<?php echo esc_attr($rec->source_id) ?>"
                                           class="ju-button orange-button wpms-small-btn save alignright wpms-update-link-button"
                                           value="<?php echo esc_attr(__('Update', 'wp-meta-seo')); ?>"/>
                                </div>
                            </div>
                            <?php
                            echo '</td>';
                            break;

                        case 'col_hit':
                            echo '<td style="text-align:center;">';
                            echo esc_html($rec->hit);
                            echo '</td>';
                            break;
                        case 'col_status':
                            echo '<td class="col_status">';
                            if (strpos($rec->status_text, '200') !== false) {
                                echo '<i class="material-icons wpms_ok metaseo_help_status" data-alt="Link is OK">done</i>';
                            } elseif (strpos($rec->status_text, '301') !== false) {
                                echo '<i class="material-icons wpms_ok metaseo_help_status"
 data-alt="Permanent redirect">done</i>';
                            } elseif (strpos($rec->status_text, '302') !== false) {
                                echo '<i class="material-icons wpms_ok metaseo_help_status"
 data-alt="Moved temporarily">done</i>';
                            } elseif (strpos($rec->status_text, '404') !== false
                                      || $rec->status_text === 'Server Not Found') {
                                $wpms_settings_404 = get_option('wpms_settings_404');
                                if ((isset($wpms_settings_404['wpms_redirect_homepage'])
                                     && (int) $wpms_settings_404['wpms_redirect_homepage'] === 1)
                                    || $rec->link_url_redirect !== '') {
                                    echo '<i class="material-icons wpms_ok metaseo_help_status"
 data-alt="Permanent redirect">done</i>';
                                } else {
                                    echo '<i class="material-icons wpms_warning metaseo_help_status"
 data-alt="404 error, not found">warning</i>';
                                }
                            } else {
                                echo esc_html($rec->status_text);
                            }

                            echo '</td>';
                            break;

                        case 'col_link_text':
                            if ($rec->type === 'image' || $rec->type === 'comment_content_image') {
                                echo '<td class="link_text">
<span style="float: left;margin-right: 5px;"><i class="material-icons metaseo_help_status" data-alt="Images">photo</i></span>
<span> ' . esc_html__('Image', 'wp-meta-seo') . '</span></td>';
                            } elseif ($rec->type === 'comment') {
                                echo '<td class="link_text"><span> ' . esc_html($rec->link_text) . '</span></td>';
                            } else {
                                if (strip_tags($rec->link_text) !== '') {
                                    echo '<td class="link_text">' . esc_html(strip_tags($rec->link_text)) . '</td>';
                                } else {
                                    echo '<td class="link_text">
<i>' . esc_html__('No text on this link', 'wp-meta-seo') . '</i></td>';
                                }
                            }

                            break;

                        case 'col_source':
                            $source_inner = '';
                            $row_action   = array();
                            if ($rec->type === '404_automaticaly') {
                                $source_inner = '<span style="float: left;margin-right: 5px;">
<i class="material-icons metaseo_help_status" data-alt="External URL indexed">link</i></span>';
                                $source_inner .= esc_html__('404 automaticaly indexed', 'wp-meta-seo');

                                // referrer infos
                                $referrers    = explode('||', $rec->referrer);
                                $source_inner .= '<div id="referrers-' . $rec->id . '" style="display:none;">';
                                $source_inner .= '<h3>' . $rec->link_url . '</h3>';
                                $source_inner .= '<ul class="referrers_infos">';
                                foreach ($referrers as $referrer) {
                                    if ($referrer !== '') {
                                        $source_inner .= '<li><i class="material-icons">arrow_right_alt</i><a href="' . $referrer . '" target="_blank">' . $referrer . '</a></li>';
                                    } else {
                                        $source_inner .= '<li><i class="material-icons">arrow_right_alt</i><a href="' . esc_url(get_home_url()) . '" target="_blank">' . esc_url(get_home_url()) . '</a></li>';
                                    }
                                }
                                $source_inner .= '</ul>';
                                $source_inner .= '</div>';

                                $source_inner .= '<div>';
                                $source_inner .= '<a href="#TB_inline?width=600&height=550&inlineId=referrers-' . $rec->id . '" class="thickbox">' . esc_html__('Referrer', 'wp-meta-seo') . '</a>';
                                $source_inner .= '</div>';

                                // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
                                echo '<td>' . $source_inner . '</td>';
                            } else {
                                if ($rec->type === 'comment' || $rec->type === 'comment_content_url'
                                    || $rec->type === 'comment_content_image') {
                                    $source = get_comment($rec->source_id);
                                    if (!empty($source)) {
                                        $row_action = array(
                                            'edit' => '<a target="_blank"
                                             href="' . esc_url(get_edit_comment_link($rec->source_id)) . '"
                                             title="' . esc_attr__('Edit this item', 'wp-meta-seo') . '">
                                             ' . esc_html__('Edit', 'wp-meta-seo') . '</a>',
                                            'view' => '<a target="_blank"
                                             href="' . esc_url(get_comment_link($rec->source_id)) . '"
                                             title="' . esc_attr('View &#8220;' . $source->comment_author . '&#8221;') . '" rel="permalink">
                                             ' . esc_html__('View', 'wp-meta-seo') . '</a>'
                                        );

                                        if ($rec->type === 'comment') {
                                            $source_inner = '<span style="float: left;margin-right: 5px;">
<i class="material-icons metaseo_help_status" data-alt="Comments">person_outline</i></span>';
                                        } else {
                                            $source_inner = '<span style="float: left;margin-right: 5px;">
<i class="material-icons metaseo_help_status" data-alt="Comments content">chat_bubble</i></span>';
                                        }
                                        $source_inner .= '<a target="_blank"
                                         href="' . get_edit_comment_link($rec->source_id) . '">
                                        ' . esc_html($source->comment_author) . '</a>';
                                    }
                                } else {
                                    $source = get_post($rec->source_id);
                                    if (!empty($source)) {
                                        $row_action = array(
                                            'edit' => '<a target="_blank"
                                             href="' . esc_url(get_edit_post_link($rec->source_id)) . '"
                                             title="' . esc_attr__('Edit this item', 'wp-meta-seo') . '">
                                             ' . esc_html__('Edit', 'wp-meta-seo') . '</a>',
                                            'view' => '<a target="_blank"
                                             href="' . esc_url(get_post_permalink($rec->source_id)) . '"
                                              title="' . esc_attr('View &#8220;' . $source->post_title . '&#8221;') . '"
                                               rel="permalink">View</a>'
                                        );

                                        $source_inner = '<span style="float: left;margin-right: 5px;">
<i class="material-icons metaseo_help_status" data-alt="Post , Page , Custom post">layers</i></span>';
                                        $source_inner .= '<a target="_blank"
                                         href="' . esc_url(get_edit_post_link($rec->source_id)) . '">
                                         ' . esc_html($source->post_title) . '</a>';
                                    }
                                }

                                echo '<td>';
                                if (!empty($source)) {
                                    // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
                                    echo $source_inner;
                                    // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
                                    echo $this->row_actions($row_action, false);
                                } else {
                                    if ($rec->type === 'add_custom' || $rec->type === 'add_rule') {
                                        echo '<a><i title="' . esc_attr__('Custom redirect', 'wp-meta-seo') . '"
 class="wpms_outgoing material-icons">call_missed_outgoing</i></a>';
                                    } else {
                                        echo '<a>' . esc_html__('Source Not Found', 'wp-meta-seo') . '</a>';
                                    }
                                }
                                echo '</td>';
                                break;
                            }
                    }
                }

                echo '</tr>';
            }
        }
    }

    /**
     * Retrieves a modified URL query string.
     *
     * @return void
     */
    public function processAction()
    {
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $redirect    = false;
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
        if (isset($_POST['txtkeyword'])) {
            $current_url = add_query_arg(
                array(
                    'search'     => 'Search',
                    'txtkeyword' => urlencode(stripslashes($_POST['txtkeyword']))
                ),
                $current_url
            );
            $redirect    = true;
        }

        if (isset($_POST['filter_type_action'])) {
            $current_url = add_query_arg(
                array(
                    'sl_broken' => $_POST['sl_broken']
                ),
                $current_url
            );
            $redirect    = true;
        }

        if (!empty($_POST['paged'])) {
            $current_url = add_query_arg(array('paged' => intval($_POST['paged'])), $current_url);
            $redirect    = true;
        }

        if (!empty($_POST['metaseo_broken_link_per_page'])) {
            $current_url = add_query_arg(
                array(
                    'metaseo_broken_link_per_page' => intval($_POST['metaseo_broken_link_per_page'])
                ),
                $current_url
            );
            $redirect    = true;
        }
        // phpcs:enable
        if ($redirect) {
            wp_redirect($current_url);
            ob_end_flush();
            exit();
        }
    }

    /**
     * Get link details
     *
     * @param string  $source_link Link source container current link
     * @param integer $source_id   Id source container current link
     * @param string  $url         Link url
     * @param string  $link_text   Link text
     * @param string  $type        Link type
     * @param string  $status      Link status
     * @param string  $status_type Link status type
     * @param string  $meta_title  Title of link
     * @param string  $rel         Link rel
     * @param integer $postID      Post id
     *
     * @return array
     */
    public static function getResultLink(
        $source_link,
        $source_id,
        $url,
        $link_text,
        $type,
        $status,
        $status_type,
        $meta_title = '',
        $rel = '',
        $postID = 0
    ) {
        $res = array(
            'source_link' => $source_link,
            'source_id'   => (int) $source_id,
            'link_url'    => $url,
            'link_text'   => $link_text,
            'type'        => $type,
            'status'      => $status,
            'status_type' => $status_type
        );

        if (isset($meta_title)) {
            $res['meta_title'] = $meta_title;
        } else {
            $res['meta_title'] = '';
        }

        if (isset($rel) && $rel === 'nofollow') {
            $res['follow'] = 0;
        } else {
            $res['follow'] = 1;
        }

        if (strpos($url, 'mailto:') !== false) {
            $res['link_final_url'] = $url;
        } else {
            if ($type === 'url') {
                if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                    $perlink = get_option('permalink_structure');
                    if (empty($perlink)) {
                        $res['link_final_url'] = get_site_url() . '/' . $url;
                    } else {
                        if (!empty($postID)) {
                            $res['link_final_url'] = get_permalink($postID) . $url;
                        } else {
                            $res['link_final_url'] = $perlink . '/' . $url;
                        }
                    }
                } else {
                    $res['link_final_url'] = $url;
                }
            } else {
                $res['link_final_url'] = $url;
            }
        }

        return $res;
    }

    /**
     * Get link status
     *
     * @param string $url  Url to get status
     * @param string $type Type
     *
     * @return string
     */
    public static function getUrlStatus($url, $type = '')
    {
        if (strpos($url, 'mailto:') !== false) {
            return 'Not checked';
        }

        if (strpos($url, '#') === 0 || strpos($url, 'tel:') === 0) {
            return 'HTTP/1.1 200 OK';
        }

        if ($type === 'update_post') {
            return 'HTTP/1.1 200 OK';
        }

        // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- fix warning
        $status = @get_headers($url, 0);
        if (isset($status[0])) {
            return $status[0];
        } else {
            return 'Server Not Found';
        }
    }

    /**
     * Get link status type
     *
     * @param string $status Status label
     *
     * @return string
     */
    public static function getUrlStatusType($status)
    {
        if (isset($status) && $status === 'Not checked') {
            return 'ok';
        }

        if (isset($status) && $status !== 'Server Not Found') {
            if (((int) substr($status, 9, 3) >= 200
                 && (int) substr($status, 9, 3) <= 204) || (int) substr($status, 9, 3) === 401) {
                $type = 'ok';
            } elseif (((int) substr($status, 9, 3) >= 400
                       && (int) substr($status, 9, 3) <= 503 && (int) substr($status, 9, 3) !== 401)) {
                if (in_array((int) substr($status, 9, 3), array(404, 410))) {
                    $type = 'broken_internal';
                } else {
                    $type = 'warning';
                }
            } elseif (((int) substr($status, 9, 3) >= 301 && (int) substr($status, 9, 3) <= 304)) {
                $type = 'ok';
            } else {
                $type = 'dismissed';
            }
        } else {
            $type = 'broken_internal';
        }
        return $type;
    }

    /**
     * Delete link comment in wpms_links table when delete comment
     *
     * @param integer $comment_ID Comment id
     *
     * @return void
     */
    public static function deletedComment($comment_ID)
    {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM ' . $wpdb->prefix . 'wpms_links WHERE source_id = %d AND (type = %s || type = %s || type = %s)',
                array(
                    $comment_ID,
                    'comment',
                    'comment_content_url',
                    'comment_content_image'
                )
            )
        );
    }

    /**
     * Delete link post in wpms_links table when delete post
     *
     * @param integer $post_id Post id
     *
     * @return void
     */
    public static function deletePost($post_id)
    {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM ' . $wpdb->prefix . 'wpms_links WHERE source_id = %d
                     AND type != %s',
                array(
                    $post_id,
                    'comment'
                )
            )
        );
    }

    /**
     * Update wpms_links table when update comment
     *
     * @param integer $comment_ID Id of current comment
     *
     * @return void
     */
    public static function updateComment($comment_ID)
    {
        if (empty($_POST['_wpnonce'])
            || !wp_verify_nonce($_POST['_wpnonce'], 'update-comment_' . $comment_ID)) {
            die();
        }

        global $wpdb;
        $comment = get_comment($comment_ID);
        $status  = wp_get_comment_status($comment_ID);
        if ($status === 'approved') {
            if (!empty($comment->comment_author_url)) {
                $status      = self::getUrlStatus(($comment->comment_author_url));
                $status_text = self::getStatusText($status);
                $status_type = self::getUrlStatusType($status);
                $check       = $wpdb->get_var($wpdb->prepare(
                    'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wpms_links WHERE source_id=%d AND type=%s ',
                    array(
                        $comment_ID,
                        'comment'
                    )
                ));
                if ((int) $check === 0) {
                    $value = array(
                        'link_url'        => $comment->comment_author_url,
                        'link_text'       => $comment->comment_author,
                        'source_id'       => $comment_ID,
                        'type'            => 'comment',
                        'status_code'     => $status,
                        'status_text'     => $status_text,
                        'broken_indexed'  => 0,
                        'broken_internal' => 0,
                        'warning'         => 0,
                        'dismissed'       => 0,
                    );
                    if (isset($status_type) && $status_type !== 'ok') {
                        $value[$status_type] = 1;
                    }
                    $wpdb->insert(
                        $wpdb->prefix . 'wpms_links',
                        $value
                    );
                } else {
                    $value = array(
                        'link_url'        => $comment->comment_author_url,
                        'status_code'     => $status,
                        'status_text'     => $status_text,
                        'broken_indexed'  => 0,
                        'broken_internal' => 0,
                        'warning'         => 0,
                        'dismissed'       => 0,
                    );

                    if (isset($_POST['link_redirect'])) {
                        $value['link_url_redirect'] = ($_POST['link_redirect']);
                    }

                    if (isset($status_type) && $status_type !== 'ok') {
                        $value[$status_type] = 1;
                    }
                    $wpdb->update(
                        $wpdb->prefix . 'wpms_links',
                        $value,
                        array(
                            'source_id' => $comment_ID,
                            'type'      => 'comment'
                        ),
                        array('%s', '%s', '%s', '%d', '%d', '%d', '%d'),
                        array('%d', '%s')
                    );
                }
            } else {
                $wpdb->query(
                    $wpdb->prepare(
                        'DELETE FROM ' . $wpdb->prefix . 'wpms_links WHERE source_id = %d AND (type = %s)',
                        array(
                            (int) $comment_ID,
                            'comment'
                        )
                    )
                );
            }

            $linkscontent = array();
            if (isset($comment->comment_content) && $comment->comment_content !== '') {
                preg_match_all('#<a[^>]*>.*?</a>#si', $comment->comment_content, $matches, PREG_PATTERN_ORDER);
                foreach (array_unique($matches[0]) as $i => $content) {
                    preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                    $href               = $matches[1];
                    $status             = self::getUrlStatus($href);
                    $status_type        = self::getUrlStatusType($status);
                    $link_text          = preg_replace('/<a\s(.+?)>(.+?)<\/a>/is', '$2', $content);
                    $source_link        = '<a href="' . get_edit_comment_link($comment->comment_ID) . '">';
                    $source_link        .= '<b>' . $comment->comment_author . '</b>';
                    $source_link        .= '</a>';
                    $key                = $href . 'comment_content_url' . $comment->comment_ID . $link_text;
                    $linkscontent[$key] = self::getResultLink(
                        $source_link,
                        $comment->comment_ID,
                        $href,
                        $link_text,
                        'comment_content_url',
                        $status,
                        $status_type
                    );
                }
                preg_match_all(
                    '/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i',
                    $comment->comment_content,
                    $matches,
                    PREG_PATTERN_ORDER
                );
                foreach (array_unique($matches[0]) as $content) {
                    preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $matches);
                    $src                = $matches[1];
                    $status             = self::getUrlStatus($src);
                    $status_type        = self::getUrlStatusType($status);
                    $link_text          = '';
                    $source_link        = '<a href="' . get_edit_comment_link($comment->comment_ID) . '">';
                    $source_link        .= '<b>' . $comment->comment_author . '</b>';
                    $source_link        .= '</a>';
                    $key                = $src . 'comment_content_image' . $comment->comment_ID;
                    $linkscontent[$key] = self::getResultLink(
                        $source_link,
                        $comment->comment_ID,
                        $src,
                        $link_text,
                        'comment_content_image',
                        $status,
                        $status_type
                    );
                }
            }

            $links = $wpdb->get_results($wpdb->prepare(
                'SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE source_id=%d AND (type = %s || type = %s)',
                array(
                    (int) $comment->comment_ID,
                    'comment_content_url',
                    'comment_content_image'
                )
            ));
            foreach ($links as $link) {
                if (empty($linkscontent[$link->link_url . $link->type])) {
                    $wpdb->delete($wpdb->prefix . 'wpms_links', array('id' => $link->id), array('%d'));
                } else {
                    unset($linkscontent[$link->link_url . $link->type . $link->link_text]);
                }
            }

            if (!empty($linkscontent)) {
                foreach ($linkscontent as $link) {
                    self::insertLink($link, $wpdb);
                }
            }
        } else {
            $wpdb->query(
                $wpdb->prepare(
                    'DELETE FROM ' . $wpdb->prefix . 'wpms_links
                     WHERE source_id = %d AND (type = %s || type = %s || type = %s)',
                    array(
                        (int) $comment_ID,
                        'comment',
                        'comment_content_url',
                        'comment_content_image'
                    )
                )
            );
        }

        update_option('wpms_last_update_post', time());
    }

    /**
     * Update wpms_links table when update post
     *
     * @param integer $post_ID     Id of current post
     * @param string  $post_after  Post content after update
     * @param string  $post_before Post content before update
     *
     * @return void
     */
    public static function updatePost($post_ID, $post_after, $post_before)
    {
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        unset($post_types['attachment']);
        if (!in_array($post_after->post_type, $post_types)) {
            return;
        }

        global $wpdb;
        $post         = $post_after;
        $linkscontent = array();

        if (isset($post->post_content)) {
            $seoClass = new MetaSeoAdmin();
            $post->post_content = $seoClass->injectAcfField($post->post_content, $post->ID);
        }

        if ($post->post_excerpt !== 'metaseo_404_page') {
            if ($post->post_status === 'publish') {
                if (isset($post->post_content) && $post->post_content !== '') {
                    $a_tags = wpmsExtractTags($post->post_content, 'a', false, true);
                    foreach ($a_tags as $a_tag) {
                        $meta_title = '';
                        $rel        = '';
                        $href       = '';
                        if (isset($a_tag['attributes']['title'])) {
                            $meta_title = $a_tag['attributes']['title'];
                        }

                        if (isset($a_tag['attributes']['rel'])) {
                            $rel = $a_tag['attributes']['rel'];
                        }

                        if (isset($a_tag['attributes']['href'])) {
                            $href = $a_tag['attributes']['href'];
                        }

                        $link_text = $a_tag['contents'];

                        $status      = self::getUrlStatus($href, 'update_post');
                        $status_type = self::getUrlStatusType($status);

                        $source_link        = '<a href="' . get_edit_post_link($post->ID) . '">';
                        $source_link        .= '<b>' . $post->post_title . '</b>';
                        $source_link        .= '</a>';
                        $key                = $href . 'url' . $post->ID . $link_text;
                        $linkscontent[$key] = self::getResultLink(
                            $source_link,
                            $post->ID,
                            $href,
                            $link_text,
                            'url',
                            $status,
                            $status_type,
                            $meta_title,
                            $rel
                        );
                    }

                    // find <img> tag in current post content
                    $img_tags = wpmsExtractTags($post->post_content, 'img', true, true);
                    foreach ($img_tags as $tag) {
                        $source_link = '<a href="' . get_edit_post_link($post->ID) . '">';
                        $source_link .= '<b>' . $post->post_title . '</b>';
                        $source_link .= '</a>';

                        if (empty($tag['attributes']['src'])) {
                            $src = '';
                        } else {
                            $src = $tag['attributes']['src'];
                        }

                        $status                                 = self::getUrlStatus($src, 'update_post');
                        $status_type                            = self::getUrlStatusType($status);
                        $link_text                              = '';
                        $linkscontent[$src . 'img' . $post->ID] = self::getResultLink(
                            $source_link,
                            $post->ID,
                            $src,
                            $link_text,
                            'image',
                            $status,
                            $status_type
                        );
                    }
                }

                $links = $wpdb->get_results($wpdb->prepare(
                    'SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE source_id=%d AND type != %s',
                    array(
                        $post->ID,
                        'comment'
                    )
                ));
                foreach ($links as $link) {
                    if (empty($linkscontent[$link->link_url . $link->type])) {
                        $wpdb->delete($wpdb->prefix . 'wpms_links', array('id' => $link->id), array('%d'));
                    } else {
                        unset($linkscontent[$link->link_url . $link->type . $link->link_text]);
                    }
                }

                if (!empty($linkscontent)) {
                    foreach ($linkscontent as $link) {
                        self::insertLink($link, $wpdb);
                    }
                }
            } else {
                $wpdb->query(
                    $wpdb->prepare(
                        'DELETE FROM ' . $wpdb->prefix . 'wpms_links
                             WHERE source_id = %d AND (type = %s || type = %s)',
                        array(
                            $post->ID,
                            'image',
                            'url'
                        )
                    )
                );
            }
        }

        update_option('wpms_last_update_post', time());
    }

    /**
     * Scan link in comment , post
     *
     * @return void
     */
    public static function scanLink()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        global $wpdb;
        $limit_comment_content   = 1;
        $limit_comment           = 10;
        $limit_post              = 1;
        $total_comments          = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'comments');
        $percent_comment_content = 33.33;
        $percent_comment         = 33.33;
        if (!empty($total_comments)) {
            $percent_comment_content = 33.33 / $total_comments;
        }

        if ($total_comments < $limit_comment_content) {
            $percent_comment_content = 33.33;
        }

        if (!empty($total_comments)) {
            $percent_comment = 33.33 / $total_comments;
        }

        if ($total_comments < $limit_comment) {
            $percent_comment = 33.33;
        }

        // scan link in comment url
        $comments = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'comments
         WHERE comment_approved = 1 AND comment_author_url != "" AND comment_author_url NOT IN (SELECT link_url
          FROM ' . $wpdb->prefix . 'wpms_links WHERE type = %s) LIMIT %d', array('comment', $limit_comment)));

        if (!empty($comments)) {
            foreach ($comments as $comment) {
                if (!empty($comment->comment_author_url)) {
                    $source_link = '<a href="' . get_edit_comment_link($comment->comment_ID) . '">';
                    $source_link .= '<b>' . $comment->comment_author . '</b>';
                    $source_link .= '</a>';
                    $status      = self::getUrlStatus($comment->comment_author_url);
                    $status_type = self::getUrlStatusType($status);
                    $coms        = self::getResultLink(
                        $source_link,
                        $comment->comment_ID,
                        $comment->comment_author_url,
                        $comment->comment_author,
                        'comment',
                        $status,
                        $status_type
                    );
                    self::insertLink($coms, $wpdb);
                }
            }
            wp_send_json(array('status' => false, 'type' => 'limit', 'percent' => $percent_comment));
        }

        // scan link in comment content
        $k = 0;
        if (isset($_POST['comment_paged'])) {
            $off_set = ($_POST['comment_paged'] - 1) * $limit_comment_content;
        } else {
            $off_set = 0;
        }

        $comments_content = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'comments
         WHERE comment_approved = 1 AND comment_content != "" LIMIT %d OFFSET %d', array(
            $limit_comment_content,
            $off_set
        )));
        if (!empty($comments_content)) {
            foreach ($comments_content as $comment) {
                if (isset($comment->comment_content) && $comment->comment_content !== '') {
                    preg_match_all('#<a[^>]*>.*?</a>#si', $comment->comment_content, $matches, PREG_PATTERN_ORDER);
                    foreach (array_unique($matches[0]) as $i => $content) {
                        preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                        $href        = $matches[1];
                        $status      = self::getUrlStatus($href);
                        $status_type = self::getUrlStatusType($status);
                        $link_text   = preg_replace('/<a\s(.+?)>(.+?)<\/a>/is', '$2', $content);
                        $source_link = '<a href="' . get_edit_comment_link($comment->comment_ID) . '">';
                        $source_link .= '<b>' . $comment->comment_author . '</b>';
                        $source_link .= '</a>';
                        $link_a      = self::getResultLink(
                            $source_link,
                            $comment->comment_ID,
                            $href,
                            $link_text,
                            'comment_content_url',
                            $status,
                            $status_type
                        );
                        self::insertLink($link_a, $wpdb);
                    }
                    preg_match_all(
                        '/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i',
                        $comment->comment_content,
                        $matches,
                        PREG_PATTERN_ORDER
                    );
                    foreach (array_unique($matches[0]) as $content) {
                        $source_link = '<a href="' . get_edit_comment_link($comment->comment_ID) . '">';
                        $source_link .= '<b>' . $comment->comment_author . '</b>';
                        $source_link .= '</a>';
                        preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $matches);
                        $src         = $matches[1];
                        $status      = self::getUrlStatus($src);
                        $status_type = self::getUrlStatusType($status);
                        $link_text   = '';
                        $link_sou    = self::getResultLink(
                            $source_link,
                            $comment->comment_ID,
                            $src,
                            $link_text,
                            'comment_content_image',
                            $status,
                            $status_type
                        );
                        self::insertLink($link_sou, $wpdb);
                    }
                }
            }

            $k ++;
            if ($k >= $limit_comment_content) {
                wp_send_json(
                    array(
                        'status'  => false,
                        'type'    => 'limit_comment_content',
                        'paged'   => $_POST['comment_paged'],
                        'percent' => $percent_comment_content * count($comments_content)
                    )
                );
            }
        }

        // scan link in post
        $j          = 0;
        $off_set    = ($_POST['paged'] - 1) * $limit_post;
        $post_types = MetaSeoContentListTable::getPostTypes('attachment');
        foreach ($post_types as &$post_type) {
            $post_type = esc_sql($post_type);
        }
        $post_types = implode("', '", $post_types);

        $where   = array();
        $where[] = 'post_type IN (\'' . $post_types . '\')';
        $where[] = 'post_status = "publish"';
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $total_posts  = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->posts . ' WHERE ' . implode(' AND ', $where));
        $percent_post = 33.33;
        if (!empty($total_posts)) {
            $percent_post = 33.33 / $total_posts;
        }

        if ($total_posts < $limit_post) {
            $percent_post = 33.33;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $results = $wpdb->get_results($wpdb->prepare('SELECT ID, post_title, post_excerpt, post_content, post_name, post_type, post_status FROM ' . $wpdb->posts . ' WHERE ' . implode(' AND ', $where) . 'LIMIT %d OFFSET %d', array(
            $limit_post,
            $off_set
        )));
        if (empty($results)) {
            wp_send_json(array('status' => true));
        }

        foreach ($results as $post) {
            if ($post->post_excerpt !== 'metaseo_404_page') {
                $dom = new DOMDocument;
                libxml_use_internal_errors(true);
                if (isset($post->post_content) && $post->post_content !== '') {
                    preg_match_all('#<a[^>]*>.*?</a>#si', $post->post_content, $matches, PREG_PATTERN_ORDER);
                    foreach (array_unique($matches[0]) as $i => $content) {
                        $dom->loadHTML($content);
                        $tags       = $dom->getElementsByTagName('a');
                        $meta_title = $tags->item(0)->getAttribute('title');
                        $rel        = $tags->item(0)->getAttribute('rel');
                        preg_match('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $matches);
                        $href        = $matches[1];
                        $status      = self::getUrlStatus($href);
                        $status_type = self::getUrlStatusType($status);
                        $link_text   = preg_replace('/<a\s(.+?)>(.+?)<\/a>/is', '$2', $content);
                        $source_link = '<a href="' . get_edit_post_link($post->ID) . '">';
                        $source_link .= '<b>' . $post->post_title . '</b>';
                        $source_link .= '</a>';
                        $link_a      = self::getResultLink(
                            $source_link,
                            $post->ID,
                            $href,
                            $link_text,
                            'url',
                            $status,
                            $status_type,
                            $meta_title,
                            $rel,
                            $post->ID
                        );
                        self::insertLink($link_a, $wpdb);
                    }
                    preg_match_all(
                        '/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i',
                        $post->post_content,
                        $matches,
                        PREG_PATTERN_ORDER
                    );
                    foreach (array_unique($matches[0]) as $content) {
                        $source_link = '<a href="' . get_edit_post_link($post->ID) . '">';
                        $source_link .= '<b>' . $post->post_title . '</b>';
                        $source_link .= '</a>';
                        preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $matches);
                        $src         = $matches[1];
                        $status      = self::getUrlStatus($src);
                        $status_type = self::getUrlStatusType($status);
                        $link_text   = '';
                        $link_src    = self::getResultLink(
                            $source_link,
                            $post->ID,
                            $src,
                            $link_text,
                            'image',
                            $status,
                            $status_type
                        );
                        self::insertLink($link_src, $wpdb);
                    }
                }
            }
            $j ++;
            if ($j >= $limit_post) {
                wp_send_json(
                    array(
                        'status'  => false,
                        'type'    => 'limit_post',
                        'paged'   => $_POST['paged'],
                        'percent' => $percent_post * count($results)
                    )
                );
            }
        }

        $link_settings = array(
            'enable'            => 0,
            'numberFrequency'   => 1,
            'showlinkFrequency' => 'month'
        );

        $linksettings = get_option('wpms_link_settings');
        if (is_array($linksettings)) {
            $link_settings = array_merge($link_settings, $linksettings);
        }

        $link_settings['wpms_lastRun_scanlink'] = time();
        update_option('wpms_link_settings', $link_settings);
        /**
         * Scan link
         */
        do_action('wpms_scan_link');
        wp_send_json(array('status' => true));
    }

    /**
     * Insert link to wpms_link table
     *
     * @param array  $link Link details to insert
     * @param object $wpdb Global wordpress database
     *
     * @return void
     */
    public static function insertLink($link, $wpdb)
    {
        if (!empty($link['link_url'])) {
            $links = $wpdb->get_results($wpdb->prepare(
                'SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE link_url=%s AND link_text=%s AND type=%s AND source_id=%d ',
                array(
                    $link['link_url'],
                    $link['link_text'],
                    $link['type'],
                    $link['source_id']
                )
            ));
            if (count($links) === 0) {
                $status_text = self::getStatusText($link['status']);
                $value       = array(
                    'link_url'        => $link['link_url'],
                    'link_final_url'  => $link['link_final_url'],
                    'link_text'       => $link['link_text'],
                    'source_id'       => $link['source_id'],
                    'type'            => $link['type'],
                    'status_code'     => $link['status'],
                    'status_text'     => $status_text,
                    'broken_indexed'  => 0,
                    'broken_internal' => 0,
                    'warning'         => 0,
                    'dismissed'       => 0,
                    'meta_title'      => $link['meta_title'],
                    'follow'          => $link['follow']
                );
                if (isset($link['status_type']) && $link['status_type'] !== 'ok') {
                    $value[$link['status_type']] = 1;
                }

                $site_url = get_site_url();
                $value    = self::checkInternalLink($link['link_url'], $site_url, $value);

                $wpdb->insert(
                    $wpdb->prefix . 'wpms_links',
                    $value
                );
            } else {
                $value    = array(
                    'meta_title' => $link['meta_title'],
                    'follow'     => $link['follow']
                );
                $site_url = get_site_url();
                // get status
                $status_text          = self::getStatusText($link['status']);
                $value                = self::checkInternalLink($links[0]->link_url, $site_url, $value);
                $value['status_code'] = $link['status'];
                $value['status_text'] = $status_text;
                if ((int) $links[0]->follow !== (int) $link['follow'] || $links[0]->meta_title !== $link['meta_title']
                    || (int) $links[0]->internal !== (int) $value['internal'] || $links[0]->status_code !== $value['status_code']) {
                    // update link status
                    $wpdb->update(
                        $wpdb->prefix . 'wpms_links',
                        $value,
                        array(
                            'id' => $links[0]->id
                        )
                    );
                }
            }
        }
    }

    /**
     * Check internal link
     *
     * @param string $link    Current link url
     * @param string $siteUrl Site url
     * @param array  $value   Value
     *
     * @return mixed
     */
    public static function checkInternalLink($link, $siteUrl, $value)
    {
        $info_link     = parse_url($link);
        $info_site_url = parse_url($siteUrl);
        if (empty($info_link['path']) || $info_site_url['path'] || empty($info_link['host'])) {
            $value['internal'] = 0;
            return $value;
        }

        $domain_link = $info_link['host'] . $info_link['path'] . '/';
        $domain_site = $info_site_url['host'] . $info_site_url['path'] . '/';
        if (strpos($domain_link, $domain_site) !== false) {
            $value['internal'] = 1;
        } else {
            $value['internal'] = 0;
        }

        return $value;
    }

    /**
     * Flush link
     *
     * @return void
     */
    public static function flushLink()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        global $wpdb;
        if (isset($_POST['type']) && $_POST['type'] !== 'none') {
            switch ($_POST['type']) {
                case 'automaticaly_indexed':
                    $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'wpms_links WHERE broken_indexed = 1 AND link_url_redirect = ""');
                    break;
                case 'internal_broken_links':
                    $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'wpms_links WHERE broken_internal = 1 AND link_url_redirect = ""');
                    break;
                case 'all':
                    $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'wpms_links WHERE (broken_internal = 1 OR broken_indexed = 1) AND link_url_redirect = ""');

                    break;
            }
            wp_send_json(true);
        }
        wp_send_json(false);
    }

    /**
     * Get status text
     *
     * @param string $status Statue of link
     *
     * @return boolean|string
     */
    public static function getStatusText($status)
    {
        if ($status === 'Not checked') {
            return 'Not checked';
        }
        if ($status === 'Server Not Found') {
            $status_text = 'Server Not Found';
        } else {
            $status_text = substr($status, 9);
        }
        return $status_text;
    }

    /**
     * Add custom redirect
     *
     * @return void
     */
    public static function addCustomRedirect()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            $wpms_nonce = 0;
        } else {
            $wpms_nonce = $_POST['wpms_nonce'];
        }

        /**
         * Add custom redirect
         *
         * @param string nonce
         */
        do_action('wpms_add_custom_redirect', $wpms_nonce);
        wp_send_json(array('status' => true, 'message' => esc_html__('Done!', 'wp-meta-seo')));
    }

    /**
     * Update link
     *
     * @return void
     */
    public static function updateLinkRedirect()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (isset($_POST['link_id'])) {
            global $wpdb;
            $link_detail = $wpdb->get_row($wpdb->prepare(
                'SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE id=%d',
                array($_POST['link_id'])
            ));
            if (empty($link_detail)) {
                wp_send_json(false);
            }

            $new_link      = stripslashes($_POST['new_link']);
            $link_redirect = stripslashes($_POST['link_redirect']);
            if (isset($_POST['new_text'])) {
                $new_text = stripcslashes($_POST['new_text']);
            } else {
                $new_text = '';
            }

            if ($link_redirect !== '') {
                $status = 'HTTP/1.1 200 OK';
            } else {
                $status = self::getUrlStatus($new_link);
            }

            $status_text = self::getStatusText($status);
            $status_type = self::getUrlStatusType($status);
            if ($link_detail->type !== '404_automaticaly') {
                $value = array(
                    'link_url'          => $new_link,
                    'link_final_url'    => '',
                    'link_url_redirect' => '',
                    'link_text'         => stripcslashes($new_text),
                    'status_code'       => $status,
                    'status_text'       => $status_text,
                    'broken_indexed'    => 0,
                    'broken_internal'   => 0,
                    'warning'           => 0,
                    'dismissed'         => 0,
                );
                if (strpos($new_link, 'mailto:') !== false) {
                    $value['link_final_url'] = $new_link;
                } else {
                    if (!preg_match('~^(?:f|ht)tps?://~i', $new_link)) {
                        $perlink = get_option('permalink_structure');
                        if (empty($perlink)) {
                            $value['link_final_url'] = get_site_url() . '/' . $new_link;
                        } else {
                            if (!empty($link_detail->source_id)) {
                                $value['link_final_url'] = get_permalink($link_detail->source_id) . $new_link;
                            } else {
                                $value['link_final_url'] = $perlink . '/' . $new_link;
                            }
                        }
                    } else {
                        $value['link_final_url'] = $new_link;
                    }
                }

                if (!empty($link_redirect)) {
                    $value['link_url_redirect'] = $link_redirect;
                }

                if (isset($status_type) && $status_type !== 'ok') {
                    $value[$status_type] = 1;
                }
            } else {
                $status      = self::getUrlStatus($link_redirect);
                $status_text = self::getStatusText($status);

                $value = array(
                    'link_url_redirect' => stripslashes($link_redirect),
                    'status_code'       => $status,
                    'status_text'       => $status_text,
                    'broken_indexed'    => 1
                );
            }

            if ($link_detail->type === 'add_custom') {
                $value['meta_title'] = $_POST['status_redirect'];
            }

            $site_url = get_site_url();
            $value    = self::checkInternalLink($new_link, $site_url, $value);

            $wpdb->update(
                $wpdb->prefix . 'wpms_links',
                $value,
                array(
                    'id' => $_POST['link_id']
                )
            );

            switch ($link_detail->type) {
                case '404_automaticaly':
                    wp_send_json(
                        array(
                            'status'      => true,
                            'type'        => '404_automaticaly',
                            'status_text' => $status_text,
                            'new_link'    => esc_url($new_link)
                        )
                    );
                    break;
                case 'comment_content_image':
                    $comment = get_comment($link_detail->source_id);
                    if (!empty($comment)) {
                        $old_value   = $comment->comment_content;
                        $edit_result = self::editLinkImg(
                            $old_value,
                            $new_link,
                            $link_detail->link_url
                        );
                        $my_comment  = array(
                            'comment_ID'      => $link_detail->source_id,
                            'comment_content' => $edit_result['content']
                        );
                        remove_action('edit_comment', array('MetaSeoBrokenLinkTable', 'updateComment'));
                        wp_update_comment($my_comment);
                        wp_send_json(
                            array(
                                'status'      => true,
                                'type'        => 'image',
                                'status_text' => $status_text,
                                'new_link'    => esc_url($edit_result['raw_url'])
                            )
                        );
                    }
                    break;
                case 'image':
                    $post = get_post($link_detail->source_id);
                    if (!empty($post)) {
                        $old_value   = $post->post_content;
                        $edit_result = self::editLinkImg(
                            $old_value,
                            $new_link,
                            $link_detail->link_url
                        );
                        $my_post     = array(
                            'ID'           => $link_detail->source_id,
                            'post_content' => $edit_result['content']
                        );
                        remove_action('post_updated', array('MetaSeoBrokenLinkTable', 'updatePost'));
                        wp_update_post($my_post);
                        wp_send_json(
                            array(
                                'status'      => true,
                                'type'        => 'image',
                                'status_text' => $status_text,
                                'new_link'    => esc_url($edit_result['raw_url'])
                            )
                        );
                    }
                    break;

                case 'comment_content_url':
                    $comment = get_comment($link_detail->source_id);
                    if (!empty($comment)) {
                        $old_value = $comment->comment_content;
                        if (isset($_POST['data_type']) && $_POST['data_type'] === 'multi' && $new_text === '') {
                            $edit_result = self::editLinkHtml(
                                $old_value,
                                $new_link,
                                $link_detail->link_url
                            );
                            $new_text    = '';
                        } else {
                            $edit_result = self::editLinkHtml(
                                $old_value,
                                $new_link,
                                $link_detail->link_url,
                                $new_text
                            );
                            $new_text    = strip_tags($edit_result['link_text']);
                        }

                        $my_comment = array(
                            'comment_ID'      => $link_detail->source_id,
                            'comment_content' => $edit_result['content']
                        );
                        remove_action('edit_comment', array('MetaSeoBrokenLinkTable', 'updateComment'));
                        wp_update_comment($my_comment);
                        wp_send_json(
                            array(
                                'status'      => true,
                                'type'        => 'url',
                                'status_text' => $status_text,
                                'new_link'    => $edit_result['raw_url'],
                                'new_text'    => $new_text
                            )
                        );
                    }

                    break;

                case 'url':
                    $post = get_post($link_detail->source_id);
                    if (!empty($post)) {
                        $old_value = $post->post_content;
                        if (isset($_POST['data_type']) && $_POST['data_type'] === 'multi' && $new_text === '') {
                            $edit_result = self::editLinkHtml(
                                $old_value,
                                $new_link,
                                $link_detail->link_url
                            );
                            $new_text    = '';
                        } else {
                            $edit_result = self::editLinkHtml(
                                $old_value,
                                $new_link,
                                $link_detail->link_url,
                                $new_text
                            );
                            $new_text    = strip_tags($edit_result['link_text']);
                        }

                        $my_post = array(
                            'ID'           => $link_detail->source_id,
                            'post_content' => $edit_result['content']
                        );
                        remove_action('post_updated', array('MetaSeoBrokenLinkTable', 'updatePost'));
                        wp_update_post($my_post);
                        wp_send_json(
                            array(
                                'status'      => true,
                                'type'        => 'url',
                                'status_text' => $status_text,
                                'new_link'    => $edit_result['raw_url'],
                                'new_text'    => $new_text
                            )
                        );
                    }

                    break;
                case 'comment':
                    wp_update_comment(
                        array(
                            'comment_ID'         => $link_detail->source_id,
                            'comment_author_url' => $new_link
                        )
                    );
                    wp_send_json(
                        array(
                            'status'      => true,
                            'type'        => 'orther',
                            'status_text' => $status_text,
                            'new_link'    => $new_link
                        )
                    );
                    break;

                case 'add_custom':
                    wp_send_json(
                        array(
                            'status'      => true,
                            'type'        => 'orther',
                            'status_text' => $status_text,
                            'new_link'    => $new_link
                        )
                    );
                    break;
            }
        }
        wp_send_json(false);
    }

    /**
     * Remove link
     *
     * @return void
     */
    public static function unlink()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (isset($_POST['link_id'])) {
            global $wpdb;
            $link_detail = $wpdb->get_row($wpdb->prepare(
                'SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE id=%d',
                array($_POST['link_id'])
            ));
            if (empty($link_detail)) {
                wp_send_json(false);
            }

            $wpdb->delete($wpdb->prefix . 'wpms_links', array('id' => $_POST['link_id']));
            switch ($link_detail->type) {
                case 'add_rule':
                case '404_automaticaly':
                    wp_send_json(true);
                    break;

                case 'comment_content_image':
                    $comment = get_comment($link_detail->source_id);
                    if (!empty($comment)) {
                        $old_value   = $comment->comment_content;
                        $new_content = self::unlinkImg($old_value, $link_detail->link_url);
                        remove_action('edit_comment', array('MetaSeoBrokenLinkTable', 'updateComment'));
                        $my_comment = array(
                            'comment_ID'      => $link_detail->source_id,
                            'comment_content' => $new_content
                        );
                        wp_update_comment($my_comment);
                    }
                    wp_send_json(true);
                    break;

                case 'image':
                    $post = get_post($link_detail->source_id);
                    if (!empty($post)) {
                        $old_value   = $post->post_content;
                        $new_content = self::unlinkImg($old_value, $link_detail->link_url);
                        remove_action('post_updated', array('MetaSeoBrokenLinkTable', 'updatePost'));
                        $my_post = array(
                            'ID'           => $link_detail->source_id,
                            'post_content' => $new_content
                        );
                        wp_update_post($my_post);
                    }
                    wp_send_json(true);
                    break;

                case 'comment_content_url':
                    $comment = get_comment($link_detail->source_id);
                    if (!empty($comment)) {
                        $old_value   = $comment->comment_content;
                        $new_content = self::unlinkHtml($old_value, $link_detail->link_url);
                        remove_action('edit_comment', array('MetaSeoBrokenLinkTable', 'updateComment'));
                        $my_comment = array(
                            'comment_ID'      => $link_detail->source_id,
                            'comment_content' => $new_content
                        );
                        wp_update_comment($my_comment);
                    }
                    wp_send_json(true);
                    break;

                case 'url':
                    $post = get_post($link_detail->source_id);
                    if (!empty($post)) {
                        $old_value   = $post->post_content;
                        $new_content = self::unlinkHtml($old_value, $link_detail->link_url);
                        remove_action('post_updated', array('MetaSeoBrokenLinkTable', 'updatePost'));
                        $my_post = array(
                            'ID'           => $link_detail->source_id,
                            'post_content' => $new_content
                        );
                        wp_update_post($my_post);
                    }
                    wp_send_json(true);
                    break;
                case 'comment':
                    wp_update_comment(array('comment_ID' => $link_detail->source_id, 'comment_author_url' => ''));
                    wp_send_json(true);
                    break;
                case 'add_custom':
                    wp_send_json(true);
                    break;
            }
        }
        wp_send_json(false);
    }

    /**
     * Change all occurrences of a given plaintext URLs to a new URL.
     *
     * @param string $content Look for URLs in this string.
     * @param string $new_url Change them to this URL.
     * @param string $old_url The URL to look for.
     *
     * @return array|WP_Error If successful, the return value will be an associative array with two
     * keys : 'content' - the modified content, and 'raw_url' - the new raw, non-normalized URL used
     * for the modified links. In most cases, the returned raw_url will be equal to the new_url.
     */
    public static function editLinkImg($content, $new_url, $old_url)
    {
        self::$old_url = $old_url;
        self::$new_url = htmlentities($new_url);
        $content       = preg_replace_callback(
            self::$img_pattern,
            array(
                'MetaSeoBrokenLinkTable',
                'editImgCallback'
            ),
            $content
        );

        return array(
            'content' => $content,
            'raw_url' => self::$new_url,
        );
    }

    /**
     * Edit Image callback
     *
     * @param array $matches Matches
     *
     * @return string
     */
    public static function editImgCallback($matches)
    {
        $url = $matches[3];
        if (($url) === self::$old_url) {
            return $matches[1] . '"' . self::$new_url . '"' . $matches[4];
        } else {
            return $matches[0];
        }
    }

    /**
     * Remove all occurrences of a specific plaintext URL.
     *
     * @param string $content Look for URLs in this string.
     * @param string $url     The URL to look for.
     *
     * @return string Input string with all matching plaintext URLs removed.
     */
    public static function unlinkImg($content, $url)
    {
        self::$old_url = $url; //used by the callback
        $content       = preg_replace_callback(
            self::$img_pattern,
            array(
                'MetaSeoBrokenLinkTable',
                'unlinkImgCallback'
            ),
            $content
        );
        return $content;
    }

    /**
     * Get image unchanged
     *
     * @param array $matches Matches
     *
     * @return string
     */
    public static function unlinkImgCallback($matches)
    {
        $url = $matches[3];
        if (($url) === self::$old_url) {
            return ''; //Completely remove the IMG tag
        } else {
            return $matches[0]; //return the image unchanged
        }
    }

    /**
     * Remove all occurrences of a specific plaintext URL.
     *
     * @param string $content Look for URLs in this string.
     * @param string $url     The URL to look for.
     *
     * @return string Input string with all matching plaintext URLs removed.
     */
    public static function unlinkHtml($content, $url)
    {
        $args = array(
            'old_url' => $url,
        );

        $content = self::multiEdit(
            $content,
            array(
                'MetaSeoBrokenLinkTable',
                'unlinkHtmlCallback'
            ),
            $args
        );

        return $content;
    }

    /**
     * Get link to remove
     *
     * @param array $link   Link infos
     * @param array $params Params
     *
     * @return mixed
     */
    public static function unlinkHtmlCallback($link, $params)
    {
        if ($link['href'] !== $params['old_url']) {
            return $link['#raw'];
        }

        return $link['#link_text'];
    }

    /**
     * Change all occurrences of a given plaintext URLs to a new URL.
     *
     * @param string $content  Look for URLs in this string.
     * @param string $new_url  Change them to this URL.
     * @param string $old_url  The URL to look for.
     * @param string $new_text New text of this URL.
     *
     * @return array|WP_Error If successful, the return value will be an associative array with two
     * keys : 'content' - the modified content, and 'raw_url' - the new raw, non-normalized URL used
     * for the modified links. In most cases, the returned raw_url will be equal to the new_url.
     */
    public static function editLinkHtml($content, $new_url, $old_url, $new_text = null)
    {
        //Save the old & new URLs for use in the edit callback.
        $args = array(
            'old_url'  => $old_url,
            'new_url'  => $new_url,
            'new_text' => $new_text,
        );

        //Find all links and replace those that match $old_url.
        $content = self::multiEdit(
            $content,
            array(
                'MetaSeoBrokenLinkTable',
                'editHtmlCallback'
            ),
            $args
        );

        $result = array(
            'content' => $content,
            'raw_url' => $new_url,
        );
        if (isset($new_text)) {
            $result['link_text'] = $new_text;
        }
        return $result;
    }

    /**
     * Get url in content
     *
     * @param array $link   Link details
     * @param array $params New params to edit
     *
     * @return array
     */
    public static function editHtmlCallback($link, $params)
    {
        if ($link['href'] === $params['old_url']) {
            $modified = array(
                'href' => $params['new_url'],
            );
            if (isset($params['new_text'])) {
                $modified['#link_text'] = $params['new_text'];
            }

            if (isset($params['meta_title'])) {
                $modified['title'] = $params['meta_title'];
            }

            if (isset($params['follow']) && (int) $params['follow'] === 0) {
                $modified['rel'] = 'nofollow';
            } else {
                $modified['rel'] = '';
            }
            return $modified;
        } else {
            return $link['#raw'];
        }
    }

    /**
     * Helper function for blcHtmlLink::multi_edit()
     * Applies the specified callback function to each link and merges
     * the result with the current link attributes. If the callback returns
     * a replacement HTML tag instead, it will be stored in the '#new_raw'
     * key of the return array.
     *
     * @param array $link Link
     * @param array $info The callback function and the extra argument to pass to that function (if any).
     *
     * @return array
     */
    public static function editCallback($link, $info)
    {
        list($callback, $extra) = $info;

        //Prepare arguments for the callback
        $params = array($link);
        if (isset($extra)) {
            $params[] = $extra;
        }

        $new_link = call_user_func_array($callback, $params);

        if (is_array($new_link)) {
            $link = array_merge($link, $new_link);
        } elseif (is_string($new_link)) {
            $link['#new_raw'] = $new_link;
        }

        return $link;
    }

    /**
     * Modify all HTML links found in a string using a callback function.
     * The callback function should return either an associative array or a string. If
     * a string is returned, the parser will replace the current link with the contents
     * of that string. If an array is returned, the current link will be modified/rebuilt
     * by substituting the new values for the old ones.
     * htmlentities() will be automatically applied to attribute values (but not to #link_text).
     *
     * @param string   $content  A text string containing the links to edit.
     * @param callback $callback Callback function used to modify the links.
     * @param mixed    $extra    If supplied, $extra will be passed as the second parameter to the function $callback.
     *
     * @return string The modified input string.
     */
    public static function multiEdit($content, $callback, $extra = null)
    {
        //Just reuse map() + a little helper func. to apply the callback to all links and get modified links
        $modified_links = self::map(
            $content,
            array(
                'MetaSeoBrokenLinkTable',
                'editCallback'
            ),
            array(
                $callback,
                $extra
            )
        );
        //Replace each old link with the modified one
        $offset = 0;
        foreach ($modified_links as $link) {
            if (isset($link['#new_raw'])) {
                $new_html = $link['#new_raw'];
            } else {
                //Assemble the new link tag
                $new_html = '<a';
                foreach ($link as $name => $value) {
                    //Skip special keys like '#raw' and '#offset'
                    if (substr($name, 0, 1) === '#') {
                        continue;
                    }

                    $new_html .= sprintf(' %s="%s"', $name, esc_attr($value));
                }
                $new_html .= '>' . $link['#link_text'] . '</a>';
            }

            $content = substr_replace($content, $new_html, $link['#offset'] + $offset, strlen($link['#raw']));
            //Update the replacement offset
            $offset += (strlen($new_html) - strlen($link['#raw']));
        }

        return $content;
    }

    /**
     * Apply a callback function to all HTML links found in a string and return the results.
     *
     * The link data array will contain at least these keys :
     *  'href' - the URL of the link (with htmlentitydecode() already applied).
     *  '#raw' - the raw link code, e.g. the entire '<a href="...">...</a>' tag of a HTML link.
     *  '#offset' - the offset within $content at which the first character of the link tag was found.
     *  '#link_text' - the link's anchor text, if any. May contain HTML tags.
     *
     * Any attributes of the link tag will also be included in the returned array as attr_name => attr_value
     * pairs. This function will also automatically decode any HTML entities found in attribute values.
     *
     * @param string   $content  A text string to parse for links.
     * @param callback $callback Callback function to apply to all found links.
     * @param mixed    $extra    If the optional $extra param. is supplied,
     *                           it will be passed as the second parameter to the function $callback.
     *
     * @return array An array of all detected links after applying $callback to each of them.
     */
    public static function map($content, $callback, $extra = null)
    {
        $results = array();

        //Find all links
        $links = wpmsExtractTags($content, 'a', false, true);

        //Iterate over the links and apply $callback to each
        foreach ($links as $link) {
            //Massage the found link into a form required for the callback function
            $param = $link['attributes'];
            $param = array_merge(
                $param,
                array(
                    '#raw'       => $link['full_tag'],
                    '#offset'    => $link['offset'],
                    '#link_text' => $link['contents'],
                    'href'       => isset($link['attributes']['href']) ? $link['attributes']['href'] : '',
                )
            );

            //Prepare arguments for the callback
            $params = array($param);
            if (isset($extra)) {
                $params[] = $extra;
            }

            //Execute & store :)
            $results[] = call_user_func_array($callback, $params);
        }

        return $results;
    }

    /**
     * Ajax recheck link
     *
     * @return void
     */
    public static function reCheckLink()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (isset($_POST['link_id'])) {
            global $wpdb;
            $linkId = $_POST['link_id'];
            $link   = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE id=%d', array($linkId)));
            if (!empty($link)) {
                if ($link->link_url_redirect !== '') {
                    $status = 'HTTP/1.1 200 OK';
                } else {
                    $status = self::getUrlStatus(($link->link_url));
                }

                $status_text = self::getStatusText($status);

                if ($link->type === '404_automaticaly') {
                    if (((int) substr($status, 9, 3) >= 301
                         && (int) substr($status, 9, 3) <= 304)
                        || ((int) substr($status, 9, 3) >= 400
                            && (int) substr($status, 9, 3) <= 503
                            && (int) substr($status, 9, 3) !== 401) || $status === 'Server Not Found') {
                        $type = array('broken_indexed' => 1, 'broken_internal' => 0);
                    } else {
                        $type = array('broken_indexed' => 0, 'broken_internal' => 0);
                    }
                } else {
                    if (((int) substr($status, 9, 3) >= 400
                         && (int) substr($status, 9, 3) <= 503
                         && (int) substr($status, 9, 3) !== 401)
                        || $status === 'Server Not Found') {
                        $type = array('broken_internal' => 1, 'broken_indexed' => 0);
                    } else {
                        $type = array('broken_internal' => 0, 'broken_indexed' => 0);
                    }
                }

                $value = array(
                    'status_code'     => $status,
                    'status_text'     => $status_text,
                    'broken_indexed'  => $type['broken_indexed'],
                    'broken_internal' => $type['broken_internal']
                );

                $wpdb->update(
                    $wpdb->prefix . 'wpms_links',
                    $value,
                    array(
                        'ID' => $_POST['link_id']
                    )
                );
                wp_send_json(array('status' => true, 'status_text' => $status_text));
            }
            wp_send_json(array('status' => false));
        }
    }
}
