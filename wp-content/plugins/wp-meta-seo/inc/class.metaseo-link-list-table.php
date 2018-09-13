<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
wp_enqueue_style('metaseo-google-icon');

/**
 * Class MetaSeoLinkListTable
 * Base class for displaying a list of links in an ajaxified HTML table.
 */
class MetaSeoLinkListTable extends WP_List_Table
{
    /**
     * Months
     *
     * @var string
     */
    public $months;

    /**
     * MetaSeoLinkListTable constructor.
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
        ?>
        <div class="<?php echo esc_attr('tablenav ' . $which); ?>">

            <?php if ($which === 'top') : ?>
                <input type="hidden" name="page" value="metaseo_image_meta"/>
                <div class="alignleft actions bulkactions">
                    <?php $this->monthsFilter('sldate'); ?>
                    <?php $this->sourceFilter(); ?>
                </div>

            <?php elseif ($which === 'bottom') : ?>
                <input type="hidden" name="page" value="metaseo_image_meta"/>
                <div class="alignleft actions bulkactions">
                    <?php $this->monthsFilter('sldate1'); ?>
                    <?php $this->sourceFilter(); ?>
                </div>
            <?php endif ?>

            <input type="hidden" name="page" value="metaseo_image_meta"/>
            <?php // phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
            ?>
            <?php if (!empty($_REQUEST['post_status'])) : ?>
                <input type="hidden" name="post_status" value="<?php echo esc_attr($_REQUEST['post_status']); ?>"/>
            <?php endif ?>
            <?php // phpcs:enable
            ?>
            <div style="float:right;margin-left:8px;">
                <label>
                    <input type="number" required
                           value="<?php echo esc_attr($this->_pagination_args['per_page']) ?>"
                           maxlength="3" name="metaseo_link_per_page" class="metaseo_imgs_per_page screen-per-page"
                           max="999" min="1" step="1">
                </label>
                <input type="submit" name="btn_perpage" class="button_perpage button" id="button_perpage" value="Apply">
            </div>
            <?php if ($which === 'top') : ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->generateFollowFilter(); ?>
                </div>
            <?php elseif ($which === 'bottom') : ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->generateFollowFilter(); ?>
                </div>
            <?php endif ?>

            <?php $this->pagination($which); ?>
            <br class="clear"/>
        </div>

        <?php
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
            'cb'             => '<input id="cb-select-all-1" type="checkbox">',
            'post_id'        => esc_html__('Source', 'wp-meta-seo'),
            'col_link_url'   => esc_html__('URL', 'wp-meta-seo'),
            'col_link_title' => esc_html__('Link title', 'wp-meta-seo'),
            'col_link_label' => esc_html__('Link text', 'wp-meta-seo'),
            'col_follow'     => esc_html__('Follow', 'wp-meta-seo')
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
            'post_id'        => array('post_title', true),
            'col_link_url'   => array('link_url', true),
            'col_link_title' => array('meta_title', true),
            'col_follow'     => array('follow', true),
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

        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        if (isset($_GET['orderby'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
            $current_orderby = $_GET['orderby'];
        } else {
            $current_orderby = '';
        }

        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        if (isset($_GET['order']) && 'desc' === $_GET['order']) {
            $current_order = 'desc';
        } else {
            $current_order = 'asc';
        }

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="' . esc_attr('cb-select-all-' . $cb_counter) . '">
            ' . esc_html__('Select All', 'wp-meta-seo') . '</label>'
                             . '<input id="' . esc_attr('cb-select-all-' . $cb_counter) . '" type="checkbox" style="margin:0;" />';
            $cb_counter ++;
        }

        foreach ($columns as $column_key => $column_display_name) {
            $class = array('manage-column', 'column-' . $column_key);

            $style = '';
            if (in_array($column_key, $hidden)) {
                $style = 'display:none;';
            }

            $style = ' style="' . $style . '"';

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
                $column_display_name = '<a href="' . $hr . '">
<span>' . esc_html($column_display_name) . '</span>
<span class="sorting-indicator"></span></a>';
            }

            $id = $with_id ? 'id="' . esc_attr($column_key) . '"' : '';
            if (!empty($class)) {
                $class = "class='" . esc_attr(join(' ', $class)) . "'";
            }

            // phpcs:disable WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
            if ($column_key === 'col_id') {
                echo '<th scope="col" ' . $id . ' ' . $class . ' ' . $style . ' colspan="1">' . $column_display_name . '</th>';
            } elseif ($column_key === 'col_image_name') {
                echo '<th scope="col" ' . $id . ' ' . $class . ' ' . $style . ' colspan="4">' . $column_display_name . '</th>';
            } elseif ($column_key === 'col_image_info') {
                echo '<th scope="col" ' . $id . ' ' . $class . ' ' . $style . ' colspan="5">' . $column_display_name . '</th>';
            } elseif ($column_key === 'cb') {
                echo '<th scope="col" ' . $id . ' ' . $class . ' style="padding:8px 10px;">' . $column_display_name . '</th>';
            } else {
                echo '<th scope="col" ' . $id . ' ' . $class . ' ' . $style . ' colspan="3">' . $column_display_name . '</th>';
            }
            // phpcs:enable
        }
    }

    /**
     * Get months
     *
     * @return array|mixed|null|object
     */
    public function getMonths()
    {
        global $wpdb;
        $post_types = MetaSeoContentListTable::getPostTypes('attachment');
        foreach ($post_types as &$post_type) {
            $post_type = esc_sql($post_type);
        }
        $post_types = implode("', '", $post_types);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $months = $wpdb->get_results('SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month FROM ' . $wpdb->posts . ' WHERE post_type IN (\'' . $post_types . '\') ORDER BY post_date DESC');
        return $months;
    }

    /**
     * Prepares the list of items for displaying.
     *
     * @return void
     */
    public function prepare_items() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        global $wpdb;
        $this->months = $this->getMonths();

        $where = array('1=1');
        // phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        $keyword = !empty($_GET['txtkeyword']) ? $_GET['txtkeyword'] : '';
        if (isset($keyword) && $keyword !== '') {
            $where[] .= $wpdb->prepare('(link_text LIKE %s OR link_url LIKE %s)', array(
                '%' . $keyword . '%',
                '%' . $keyword . '%'
            ));
        }

        if (isset($_GET['metaseo_link_source']) && $_GET['metaseo_link_source'] === 'internal') {
            $where[] .= 'internal = 1';
        }

        if (isset($_GET['metaseo_link_source']) && $_GET['metaseo_link_source'] === 'external') {
            $where[] .= 'internal = 0';
        }

        $where[] .= 'type="url"';

        $orderby = !empty($_GET['orderby']) ? ($_GET['orderby']) : 'id';
        $order   = !empty($_GET['order']) ? ($_GET['order']) : 'asc';

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

        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        $paged = !empty($_GET['paged']) ? $_GET['paged'] : '';
        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
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

        $query       = 'SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE ' . implode(' AND ', $where) . $orderStr;
        $total_pages = ceil($total_items / $per_page);

        if (!empty($paged) && !empty($per_page)) {
            $offset = ($paged - 1) * $per_page;
            $query  .= ' LIMIT ' . (int) $offset . ',' . (int) $per_page;
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
        // phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        if (empty($_REQUEST['txtkeyword']) && !$this->has_items()) {
            return;
        }

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
                <input type="search" id="image-search-input" name="txtkeyword"
                       value="<?php echo esc_attr(stripslashes($txtkeyword)); ?>"/>
            </label>
            <?php submit_button('Search', 'button', 'search', false, array('id' => 'search-submit')); ?>
        </p>
        <?php
    }

    /**
     * Add filter follow
     *
     * @return void
     */
    public function generateFollowFilter()
    {
        ?>
        <label>
            <select name="metaseo_follow_action" class="metaseo_follow_action">
                <option value="0"><?php esc_html_e('-- Selection --', 'wp-meta-seo') ?></option>
                <option value="follow_selected"><?php esc_html_e('Follow selected', 'wp-meta-seo') ?></option>
                <option value="nofollow_selected"><?php esc_html_e('Nofollow selected', 'wp-meta-seo') ?></option>
                <option value="follow_all"><?php esc_html_e('Follow all', 'wp-meta-seo') ?></option>
                <option value="nofollow_all"><?php esc_html_e('Nofollow all', 'wp-meta-seo') ?></option>
            </select>
        </label>

        <input type="button" class="wpmsbtn wpmsbtn_small wpmsbtn_secondary btn_apply_follow"
               value="<?php esc_attr_e('Apply', 'wp-meta-seo') ?>">
        <div data-comment_paged="1" data-paged="1"
             class="wpmsbtn wpmsbtn_small wpms_scan_link page_link_meta">
            <?php esc_html_e('Re-index content links', 'wp-meta-seo') ?></div>
        <span class="spinner spinner_apply_follow"></span>
        <?php
    }

    /**
     * Add filter link source
     *
     * @return void
     */
    public function sourceFilter()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        $link_source = isset($_GET['metaseo_link_source']) ? $_GET['metaseo_link_source'] : 0;
        ?>
        <label>
            <select name="metaseo_link_source" class="metaseo_link_source">
                <option value="0" <?php selected($link_source, 0) ?>>
                    <?php esc_html_e('Link source', 'wp-meta-seo') ?>
                </option>
                <option value="internal" <?php selected($link_source, 'internal') ?>>
                    <?php esc_html_e('Internal', 'wp-meta-seo') ?>
                </option>
                <option value="external" <?php selected($link_source, 'external') ?>>
                    <?php esc_html_e('External', 'wp-meta-seo') ?>
                </option>
            </select>
        </label>
        <?php
    }

    /**
     * Add filter months
     *
     * @param string $name Filter name
     *
     * @return void
     */
    public function monthsFilter($name)
    {
        global $wp_locale;
        $month_count = count($this->months);

        if (!$month_count || (1 === (int) $month_count && 0 === (int) $this->months[0]->month)) {
            return;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        $m = isset($_REQUEST['sldate']) ? $_REQUEST['sldate'] : 0;
        ?>
        <label for="filter-by-date"
               class="screen-reader-text"><?php esc_html_e('Filter by date', 'wp-meta-seo'); ?></label>
        <select name="<?php echo esc_attr($name) ?>" id="filter-by-date" class="metaseo-filter">
            <option<?php selected($m, 0); ?> value="0"><?php esc_html_e('All dates', 'wp-meta-seo'); ?></option>
            <?php
            foreach ($this->months as $arc_row) {
                if (0 === (int) $arc_row->year) {
                    continue;
                }

                $month = zeroise($arc_row->month, 2);
                $year  = $arc_row->year;
                printf(
                    "<option %s value='%s' >%s</option>\n",
                    selected($m, $year . '-' . $month, false),
                    esc_attr($arc_row->year . '-' . $month),
                    sprintf(
                        esc_html__('%1$s %2$d', 'wp-meta-seo'),
                        esc_html($wp_locale->get_month($month)),
                        esc_html($year)
                    )
                );
            }
            ?>
        </select>
        <input type="submit" name="filter_date_action" id="image-submit" class="wpmsbtn wpmsbtn_small wpmsbtn_secondary"
               value="<?php esc_attr_e('Filter', 'wp-meta-seo') ?>">
        <?php
    }

    /**
     * Generate the table rows
     *
     * @return void
     */
    public function display_rows() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        $records = $this->items;
        list($columns) = $this->get_column_info();
        if (!empty($records)) {
            foreach ($records as $rec) {
                echo '<tr id="' . esc_attr('record_' . $rec->id) . '" data-link="' . esc_attr($rec->id) . '"
                 data-post_id="' . esc_attr($rec->source_id) . '">';
                foreach ($columns as $column_name => $column_display_name) {
                    switch ($column_name) {
                        case 'cb':
                            echo '<td scope="row" class="check-column" style="padding:8px 10px;">';
                            echo '<input id="' . esc_attr('cb-select-' . $rec->id) . '" class="metaseo_link"
                             type="checkbox" name="' . esc_attr('link[' . $rec->id . ']') . '" value="' . esc_attr($rec->id) . '">';
                            echo '</td>';
                            break;

                        case 'post_id':
                            $post = get_post($rec->source_id);
                            if (empty($post)) {
                                echo '<td class="col_id" colspan="3">';
                                echo '<a target="_blank"
                             href="#">' . esc_html__('Not found', 'wp-meta-seo') . '</a>';
                                echo '<p class="wpms_remove_link"
                                 data-link_id="' . esc_attr($rec->id) . '">
                                  <span>' . esc_html__('Remove link', 'wp-meta-seo') . '</span></p>';
                                echo '</td>';
                            } else {
                                $row_action = array(
                                    'edit' => '<a target="_blank" href="' . esc_url(get_edit_post_link($rec->source_id)) . '"
 title="Edit this item">Edit</a>',
                                    'view' => '<a target="_blank" href="' . esc_url(get_permalink($rec->source_id)) . '"
 title="View &#8220;test&#8221;" rel="permalink">View</a>'
                                );
                                echo '<td class="col_id" colspan="3">';
                                echo '<a target="_blank"
                             href="' . esc_url(get_edit_post_link($rec->source_id)) . '">' . esc_html($post->post_title) . '</a>';
                                // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
                                echo $this->row_actions($row_action, false);
                                echo '</td>';
                            }

                            break;

                        case 'col_link_url':
                            echo '<td class="wpms_link_html" colspan="3">';
                            echo '<a target="_blank" href="' . esc_url($rec->link_url) . '">' . esc_html($rec->link_url) . '</a>';
                            echo '</td>';
                            break;

                        case 'col_link_title':
                            echo '<td colspan="3">';
                            echo '<input type="text" data-post_id="' . esc_attr($rec->source_id) . '" name="metaseo_link_title"
                             id="metaseo_link_title" class="metaseo_link_title" value="' . esc_attr($rec->meta_title) . '">';
                            echo '<div data-post_id="' . esc_attr($rec->source_id) . '"
                             class="wpms_update_link">' . esc_html__('Update', 'wp-meta-seo') . '</div>';
                            echo '<strong class="wpms_mesage_link">' . esc_html__('Saved.', 'wp-meta-seo') . '</strong>';
                            echo '<strong class="wpms_error_mesage_link">' . esc_html__('Error.', 'wp-meta-seo') . '</strong>';
                            echo '</td>';
                            break;

                        case 'col_link_label':
                            echo '<td colspan="3">' . esc_html(strip_tags($rec->link_text)) . '</td>';
                            break;

                        case 'col_follow':
                            if ((int) $rec->follow === 1) {
                                echo '<td colspan="3"><i class="material-icons wpms_ok wpms_change_follow"
 data-type="done">done</i></td>';
                            } else {
                                echo '<td colspan="3"><i class="material-icons wpms_warning wpms_change_follow"
 data-type="warning">warning</i></td>';
                            }
                            break;
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

        // phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        if (isset($_POST['search']) && $_POST['search'] === 'Search') {
            $current_url = add_query_arg(
                array(
                    'search'     => 'Search',
                    'txtkeyword' => urlencode(stripslashes($_POST['txtkeyword']))
                ),
                $current_url
            );
            $redirect    = true;
        }

        if (isset($_POST['filter_date_action'])) {
            $current_url = add_query_arg(array('sldate' => $_POST['sldate']), $current_url);
            $redirect    = true;
        }

        if (!empty($_POST['paged'])) {
            $current_url = add_query_arg(array('paged' => intval($_POST['paged'])), $current_url);
            $redirect    = true;
        }

        if (isset($_POST['metaseo_link_source'])) {
            $current_url = add_query_arg(array('metaseo_link_source' => $_POST['metaseo_link_source']), $current_url);
            $redirect    = true;
        }

        if (!empty($_POST['metaseo_link_per_page'])) {
            $current_url = add_query_arg(
                array(
                    'metaseo_link_per_page' => intval($_POST['metaseo_link_per_page'])
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
}
