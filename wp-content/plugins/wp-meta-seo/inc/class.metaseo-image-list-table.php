<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if (!class_exists('ImageHelper')) {
    require_once('class.image-helper.php');
}

/**
 * Class MetaSeoImageListTable
 * Base class for displaying a list of image files in an ajaxified HTML table.
 */
class MetaSeoImageListTable extends WP_List_Table
{
    /**
     * @var
     */
    public $months;

    /**
     * MetaSeoImageListTable constructor.
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'metaseo_image',
            'plural' => 'metaseo_images',
            'ajax' => true
        ));
    }

    /**
     * Generate the table navigation above or below the table
     * @param string $which
     */
    protected function display_tablenav($which)
    {
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">

            <?php if ($which == 'top') : ?>
                <input type="hidden" name="page" value="metaseo_image_meta"/>
                <div class="alignleft actions bulkactions">
                    <?php
                    $this->monthsFilter('sldate');
                    $this->metaFilter('slmeta');
                    if (is_plugin_active(WPMSEO_ADDON_FILENAME)
                        && (is_plugin_active('sitepress-multilingual-cms/sitepress.php')
                            || is_plugin_active('polylang/polylang.php'))) {
                        $lang = !empty($_REQUEST['wpms_lang_list']) ? $_REQUEST['wpms_lang_list'] : '0';
                        $sl_lang = apply_filters('wpms_get_languagesList', '', $lang);
                        echo $sl_lang;
                    }
                    ?>
                </div>
            <?php elseif ($which == 'bottom') : ?>
                <input type="hidden" name="page" value="metaseo_image_meta"/>
                <div class="alignleft actions bulkactions">
                    <?php
                    $this->monthsFilter('sldate1');
                    $this->metaFilter('slmeta1');
                    if (is_plugin_active(WPMSEO_ADDON_FILENAME)
                        && (is_plugin_active('sitepress-multilingual-cms/sitepress.php')
                            || is_plugin_active('polylang/polylang.php'))) {
                        $lang = !empty($_REQUEST['wpms_lang_list']) ? $_REQUEST['wpms_lang_list'] : '0';
                        $sl_lang = apply_filters('wpms_get_languagesList', '', $lang);
                        echo $sl_lang;
                    }
                    ?>
                </div>
            <?php endif ?>
            <?php

            ?>
            <div class="alignleft actions">
                <label>
                    <select name="image_mbulk_copy" class="mbulk_copy">
                        <option value="0"><?php _e('Bulk copy', 'wp-meta-seo') ?></option>
                        <option value="all"><?php _e('All Images', 'wp-meta-seo') ?></option>
                        <option value="bulk-copy-title-alt"><?php _e('Selected images', 'wp-meta-seo') ?></option>
                    </select>
                </label>
                <input type="button" name="image_do_copy_alt"
                       class="wpmsbtn wpmsbtn_small btn_do_copy image_do_copy_alt"
                       value="<?php _e('Image name as alt text', 'wp-meta-seo') ?>">
                <input type="button" name="image_do_copy_title"
                       class="wpmsbtn wpmsbtn_small btn_do_copy image_do_copy_title"
                       value="<?php _e('Image name as image title', 'wp-meta-seo') ?>">
                <?php if ($which == 'top') : ?>
                    <div style="float:left;position: relative;">
                        <div class="wpms_process_meta" data-w="0" style="position: absolute;top: -2px;"></div>
                        <input alt="<?php _e('Index images is required to use the Images filtering system above.
                         Beware it may take a while depending of the quantity of images you got.
                          Check the progress bar and be patient :)', 'wp-meta-seo') ?>"
                               type="button" name="image_scan_meta" class="wpmsbtn wpmsbtn_small image_scan_meta"
                               data-paged="1" value="<?php _e('Index images', 'wp-meta-seo') ?>">
                    </div>
                <?php endif; ?>
                <span class="spinner"></span>
            </div>

            <input type="hidden" name="page" value="metaseo_image_meta"/>
            <?php if (!empty($_REQUEST['post_status'])) : ?>
                <input type="hidden" name="post_status" value="<?php echo esc_attr($_REQUEST['post_status']); ?>"/>
            <?php endif ?>

            <div style="float:right;margin-left:8px;">
                <label>
                    <input type="number" required min="1" value="<?php echo $this->_pagination_args['per_page'] ?>"
                           maxlength="3" name="metaseo_imgs_per_page" class="metaseo_imgs_per_page screen-per-page"
                           max="999" min="1" step="1">
                </label>
                <input type="submit" name="btn_perpage" class="button_perpage button" id="button_perpage" value="Apply">
            </div>

            <?php $this->pagination($which); ?>
            <br class="clear"/>
        </div>

        <?php
    }

    /**
     * Get an associative array ( id => link ) with the list
     * of views available on this table.
     * @return array
     */
    protected function get_views()
    {
        global $wpdb;
        $status_links = array();
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        $post_types = "'" . implode("', '", $post_types) . "'";

        $states = get_post_stati(array('show_in_admin_all_list' => true));
        $states['trash'] = 'trash';
        $all_states = "'" . implode("', '", $states) . "'";

        $total_posts = $wpdb->get_var(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status IN ($all_states) AND post_type IN ($post_types)"
        );

        $class = empty($_REQUEST['post_status']) ? ' class="current"' : '';
        $status_links['all'] = "<a href='admin.php?page=metaseo_image_meta'$class>";
        $status_links['all'] .= sprintf(
            _nx(
                'All <span class="count">(%s)</span>',
                'All <span class="count">(%s)</span>',
                $total_posts,
                'posts'
            ),
            number_format_i18n(
                $total_posts
            )
        );
        $status_links['all'] .= "</a>";
        foreach (get_post_stati(array('show_in_admin_all_list' => true), 'objects') as $status) {
            $name = $status->name;

            $total = $wpdb->get_var(
                "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status IN ('$name') AND post_type IN ($post_types)"
            );

            if ($total == 0) {
                continue;
            }

            if (isset($_REQUEST['post_status']) && $name == $_REQUEST['post_status']) {
                $class = ' class="current"';
            } else {
                $class = '';
            }

            $status_links[$name] = "<a href='admin.php?page=metaseo_image_meta&amp;post_status=$name'$class>";
            $status_links[$name] .= sprintf(
                translate_nooped_plural($status->label_count, $total),
                number_format_i18n($total)
            );
            $status_links[$name] .= "</a>";
        }
        $trashed_posts = $wpdb->get_var(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status IN ('trash') AND post_type IN ($post_types)"
        );
        $class = (isset($_REQUEST['post_status']) && 'trash' == $_REQUEST['post_status']) ? 'class="current"' : '';
        $status_links['trash'] = "<a href='admin.php?page=metaseo_image_meta&amp;post_status=trash'$class>";
        $status_links['trash'] .= sprintf(
            _nx(
                'Trash <span class="count">(%s)</span>',
                'Trash <span class="count">(%s)</span>',
                $trashed_posts,
                'posts'
            ),
            number_format_i18n(
                $trashed_posts
            )
        );
        $status_links['trash'] .= "</a>";

        return $status_links;
    }

    /**
     * Extra controls to be displayed between bulk actions and pagination
     * @param string $which
     */
    protected function extra_tablenav($which)
    {
        echo '<div class="alignleft actions">';
        global $wpdb;

        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        $post_types = "'" . implode("', '", esc_sql($post_types)) . "'";

        $states = get_post_stati(array('show_in_admin_all_list' => true));
        $states['trash'] = 'trash';
        $all_states = "'" . implode("', '", esc_sql($states)) . "'";

        $query = "SELECT DISTINCT post_type FROM 
$wpdb->posts WHERE post_status IN ($all_states) AND post_type IN ($post_types) ORDER BY 'post_type' ASC";
        $post_types = $wpdb->get_results($query);

        $selected = !empty($_REQUEST['post_type_filter']) ? $_REQUEST['post_type_filter'] : -1;

        $options = '<option value="-1">Show All Post Types</option>';

        foreach ($post_types as $post_type) {
            $obj = get_post_type_object($post_type->post_type);
            $options .= sprintf(
                '<option value="%2$s" %3$s>%1$s</option>',
                $obj->labels->name,
                $post_type->post_type,
                selected($selected, $post_type->post_type, false)
            );
        }

        echo "</div>";
    }

    /**
     * Get a list of columns. The format is:
     * 'internal-name' => 'Title'
     * @return array
     */
    public function get_columns()
    {
        return $columns = array(
            'cb' => '<input id="cb-select-all-1" type="checkbox">',
            'col_id' => __('ID', 'wp-meta-seo'),
            'col_image' => __('Image', 'wp-meta-seo'),
            'col_image_name' => __('Name', 'wp-meta-seo'),
            'col_image_info' => __('Optimization Info', 'wp-meta-seo'),
            'col_image_alternative' => __('Alternative text', 'wp-meta-seo'),
            'col_image_title' => __('Title', 'wp-meta-seo'),
            'col_image_legend' => __('Caption', 'wp-meta-seo'),
            'col_image_desc' => __('Description', 'wp-meta-seo'),
        );
    }

    /**
     * Get a list of sortable columns. The format is:
     * 'internal-name' => 'orderby'
     * or
     * 'internal-name' => array( 'orderby', true )
     *
     * The second format will make the initial sorting order be descending
     * @return array
     */
    protected function get_sortable_columns()
    {
        return $sortable = array(
            'col_image_name' => array('post_name', true),
            'col_image_title' => array('post_title', true),
        );
    }

    /**
     * Print column headers, accounting for hidden and sortable columns.
     *
     * @since 3.1.0
     * @access public
     *
     * @param bool $with_id Whether to set the id attribute or not
     */
    public function print_column_headers($with_id = true)
    {
        list($columns, $hidden, $sortable) = $this->get_column_info();
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg('paged', $current_url);

        if (isset($_GET['orderby'])) {
            $current_orderby = $_GET['orderby'];
        } else {
            $current_orderby = '';
        }

        if (isset($_GET['order']) && 'desc' == $_GET['order']) {
            $current_order = 'desc';
        } else {
            $current_order = 'asc';
        }

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">
            ' . __('Select All', 'wp-meta-seo') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" style="margin:0;" />';
            $cb_counter++;
        }

        foreach ($columns as $column_key => $column_display_name) {
            $class = array('manage-column', "column-$column_key");

            $style = '';
            if (in_array($column_key, $hidden)) {
                $style = 'display:none;';
            }

            $style = ' style="' . $style . '"';

            if ('cb' == $column_key) {
                $class[] = 'check-column';
            } elseif (in_array($column_key, array('posts', 'comments', 'links'))) {
                $class[] = 'num';
            }

            if (isset($sortable[$column_key])) {
                list($orderby, $desc_first) = $sortable[$column_key];

                if ($current_orderby == $orderby) {
                    $order = 'asc' == $current_order ? 'desc' : 'asc';
                    $class[] = 'sorted';
                    $class[] = $current_order;
                } else {
                    $order = $desc_first ? 'desc' : 'asc';
                    $class[] = 'sortable';
                    $class[] = $desc_first ? 'asc' : 'desc';
                }

                $hr = esc_url(add_query_arg(compact('orderby', 'order'), $current_url));
                $column_display_name = '<a href="' . $hr . '">
<span>' . $column_display_name . '</span>
<span class="sorting-indicator"></span></a>';
            }

            $id = $with_id ? "id='$column_key'" : '';

            if (!empty($class)) {
                $class = "class='" . join(' ', $class) . "'";
            }

            if ($column_key === 'col_id') {
                echo "<th scope='col' $id $class $style colspan=\"1\">$column_display_name</th>";
            } elseif ($column_key === 'col_image_name') {
                echo "<th scope='col' $id $class $style colspan=\"4\">$column_display_name</th>";
            } elseif ($column_key === 'col_image_info') {
                echo "<th scope='col' $id $class $style colspan=\"5\">$column_display_name</th>";
            } elseif ($column_key === 'cb') {
                echo "<th scope='col' $id $class style='padding:8px 10px;'>$column_display_name</th>";
            } else {
                echo "<th scope='col' $id $class $style colspan=\"3\">$column_display_name</th>";
            }
        }
    }

    /**
     * Get months
     */
    public function getMonths()
    {
        global $wpdb;
        $where = " AND ((post_mime_type='image/jpeg') OR (post_mime_type='image/jpg')";
        $where .= " OR (post_mime_type='image/png') OR (post_mime_type='image/gif')) ";
        $months = $wpdb->get_results(
            $wpdb->prepare("
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = %s" . $where . "   
			ORDER BY post_date DESC 
		", 'attachment')
        );

        $months = apply_filters('months_dropdown_results', $months, 'attachment');
        return $months;
    }

    /**
     * Prepares the list of items for displaying.
     * @uses WP_List_Table::set_pagination_args()
     */
    public function prepare_items()
    {
        global $wpdb;
        $this->months = $this->getMonths();
        $where = array();
        $post_type = 'attachment';
        $where[] = " post_type='$post_type' ";
        $where[] = " ((post_mime_type='image/jpeg') OR (post_mime_type='image/jpg')
         OR (post_mime_type='image/png') OR (post_mime_type='image/gif')) ";

        if (!empty($_REQUEST["search"])) {
            if (!empty($_REQUEST["txtkeyword"])) {
                $_REQUEST["txtkeyword"] = stripslashes($_REQUEST["txtkeyword"]);
                $_REQUEST["txtkeyword"] = $wpdb->esc_like($_REQUEST["txtkeyword"]);
                $where[] = $wpdb->prepare(
                    "  (post_title Like %s  or post_name Like %s)",
                    "%" . $_REQUEST["txtkeyword"] . "%",
                    "%" . $_REQUEST["txtkeyword"] . "%"
                );
            }
        }

        if (!empty($_REQUEST['sldate'])) {
            $where[] = $wpdb->prepare("  post_date Like %s", "%" . $_REQUEST['sldate'] . "%");
        }

        $sortable = $this->get_sortable_columns();
        $order_array = array('ASC', 'asc', 'DESC', 'desc');
        if (isset($_GET["orderby"])) {
            $orderby_array = array($_GET["orderby"], true);
        } else {
            $orderby_array = array('post_name', true);
        }
        $orderby = (!empty($_GET["orderby"]) && in_array($orderby_array, $sortable)) ? ($_GET["orderby"]) : 'post_name';
        $order = (!empty($_GET["order"]) && in_array($_GET["order"], $order_array)) ? ($_GET["order"]) : 'ASC';

        $orderStr = '';
        if (!empty($orderby) & !empty($order)) {
            $orderStr = $wpdb->prepare(' ORDER BY %s %s', $orderby, $order);
            $orderStr = str_replace("'", "", $orderStr);
        }

        if (isset($_GET['slmeta']) && $_GET['slmeta'] == 'missing_information') {
            $join = "INNER JOIN (SELECT * FROM {$wpdb->prefix}postmeta
             WHERE meta_key = 'wpms_missing_information') mt ON mt.post_id = posts.ID ";
        } elseif (isset($_GET['slmeta']) && $_GET['slmeta'] == 'resizeimages') {
            $join = "INNER JOIN (SELECT * FROM {$wpdb->prefix}postmeta
             WHERE meta_key = 'wpms_resizeimages' AND meta_value = 1) mt ON mt.post_id = posts.ID ";
        } else {
            $join = "LEFT JOIN (SELECT * FROM {$wpdb->prefix}postmeta
             WHERE meta_key = '_wp_attachment_image_alt') mt ON mt.post_id = posts.ID ";
        }

        // query post by lang with polylang plugin
        if (is_plugin_active(WPMSEO_ADDON_FILENAME) && is_plugin_active('polylang/polylang.php')) {
            if (isset($_GET['wpms_lang_list']) && $_GET['wpms_lang_list'] != '0') {
                $join .= " INNER JOIN (SELECT * FROM $wpdb->term_relationships as ml
                 INNER JOIN (SELECT * FROM $wpdb->terms WHERE slug='" . $_GET['wpms_lang_list'] . "') mp
                  ON mp.term_id = ml.term_taxonomy_id) ml ON ml.object_id = posts.ID ";
            }
        }

        // query post by lang with WPML plugin
        if (is_plugin_active(WPMSEO_ADDON_FILENAME) && is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
            if (isset($_GET['wpms_lang_list']) && $_GET['wpms_lang_list'] != '0') {
                $join .= " INNER JOIN (SELECT * FROM " . $wpdb->prefix . "icl_translations
                 WHERE element_type LIKE 'post_%' AND language_code='" . $_GET['wpms_lang_list'] . "') t
                  ON t.element_id = $wpdb->posts.ID ";
            }
        }

        $query = "SELECT COUNT(ID) 
                FROM $wpdb->posts as posts
                " . $join . "
                WHERE " . implode(" and ", $where) . $orderStr;

        $total_items = $wpdb->get_var($query);
        $query = "SELECT ID, post_title as title, post_name as name, post_content as des,
 post_excerpt as legend, guid, post_type , post_mime_type, post_status, mt.meta_value AS alt
                FROM $wpdb->posts as posts
                " . $join . "
                WHERE " . implode(" and ", $where) . $orderStr;

        if (!empty($_REQUEST['metaseo_imgs_per_page'])) {
            $_per_page = intval($_REQUEST['metaseo_imgs_per_page']);
        } else {
            $_per_page = 0;
        }
        $per_page = get_user_option('metaseo_imgs_per_page');
        if ($per_page !== false) {
            if ($_per_page && $_per_page !== $per_page) {
                $per_page = $_per_page;
                update_user_option(get_current_user_id(), 'metaseo_imgs_per_page', $per_page);
            }
        } else {
            if ($_per_page > 0) {
                $per_page = $_per_page;
            } else {
                $per_page = 10;
            }
            add_user_meta(get_current_user_id(), 'metaseo_imgs_per_page', $per_page);
        }

        $paged = !empty($_GET["paged"]) ? ($_GET["paged"]) : '';

        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }

        $total_pages = ceil($total_items / $per_page);

        if (!empty($paged) && !empty($per_page)) {
            $offset = ($paged - 1) * $per_page;
            $query .= ' LIMIT ' . (int)$offset . ',' . (int)$per_page;
        }

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'per_page' => $per_page
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $wpdb->get_results($query);
        if (isset($_GET['slmeta']) && ($_GET['slmeta'] == 'missing_information' || $_GET['slmeta'] == 'resizeimages')) {
            foreach ($this->items as $item) {
                $item->alt = get_post_meta($item->ID, '_wp_attachment_image_alt', true);
            }
        }
    }

    /**
     * Displays the search box.
     */
    public function searchBox1()
    {
        if (empty($_REQUEST['txtkeyword']) && !$this->has_items()) {
            return;
        }

        $txtkeyword = (!empty($_REQUEST['txtkeyword'])) ? urldecode(stripslashes($_REQUEST['txtkeyword'])) : "";
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
     * Add fillter month
     * @param $name
     */
    public function monthsFilter($name)
    {
        global $wp_locale;
        $month_count = count($this->months);
        if (!$month_count || (1 == $month_count && 0 == $this->months[0]->month)) {
            return;
        }

        $m = isset($_REQUEST['sldate']) ? $_REQUEST['sldate'] : 0;
        ?>
        <label for="filter-by-date" class="screen-reader-text"><?php _e('Filter by date', 'wp-meta-seo'); ?></label>
        <select name="<?php echo $name ?>" id="filter-by-date" class="metaseo-filter">
            <option<?php selected($m, 0); ?> value="0"><?php _e('All dates', 'wp-meta-seo'); ?></option>
            <?php
            foreach ($this->months as $arc_row) {
                if (0 == $arc_row->year) {
                    continue;
                }

                $month = zeroise($arc_row->month, 2);
                $year = $arc_row->year;
                printf(
                    "<option %s value='%s' >%s</option>\n",
                    selected($m, "$year-$month", false),
                    esc_attr("$arc_row->year-$month"),
                    sprintf(
                        __('%1$s %2$d', 'wp-meta-seo'),
                        $wp_locale->get_month($month),
                        $year
                    )
                );
            }
            ?>
        </select>

        <?php
    }

    /**
     * @param $name
     */
    public function metaFilter($name)
    {
        $m = isset($_REQUEST['slmeta']) ? $_REQUEST['slmeta'] : 0;
        ?>
        <label>
            <select name="<?php echo $name ?>" id="filter-by-meta" class="meta_filter">
                <option <?php selected($m, 'all') ?> value="all"><?php _e('All images', 'wp-meta-seo') ?></option>
                <option <?php selected($m, 'missing_information') ?>
                        value="missing_information">
                    <?php _e('Image with missing information', 'wp-meta-seo') ?>
                </option>
                <option <?php selected($m, 'resizeimages') ?>
                        value="resizeimages"><?php _e('HTML resized images', 'wp-meta-seo') ?></option>
            </select>
        </label>
        <input type="submit" name="filter_meta_action" id="image-submit" class="wpmsbtn wpmsbtn_small wpmsbtn_secondary"
               value="<?php _e('Filter', 'wp-meta-seo') ?>">
        <span class="spinner imgspinner"></span>
        <?php
    }

    /**
     * Generate the table rows
     */
    public function display_rows()
    {
        $records = $this->items;
        $i = 0;
        $alternate = "";

        list($columns, $hidden) = $this->get_column_info();

        if (!empty($records)) {
            foreach ($records as $rec) {
                $alternate = 'alternate' == $alternate ? '' : 'alternate';
                $i++;
                $classes = $alternate;
                $img_meta = get_post_meta($rec->ID, '_wp_attachment_metadata', true);
                if (empty($img_meta['file'])) {
                    continue;
                }
                $thumb = wp_get_attachment_image_src($rec->ID, 'thumbnail');
                if (!$thumb) {
                    $thumb_url = $rec->guid;
                } else {
                    $thumb_url = $thumb['0'];
                }

                if (strrpos($img_meta['file'], '/') !== false) {
                    $img_name = substr($img_meta['file'], strrpos($img_meta['file'], '/') + 1);
                } else {
                    $img_name = $img_meta['file'];
                }
                $type = substr($img_meta['file'], strrpos($img_meta['file'], '.'));
                $img_name = str_replace($type, '', $img_name);

                $upload_dir = wp_upload_dir();
                $img_path = $upload_dir['basedir'] . '/' . $img_meta['file'];
                //Get the date that image was uploaded
                $img_date = get_the_date("", $rec->ID);
                if (file_exists($img_path)) {
                    if (is_readable($img_path)) {
                        //Get image attributes including width and height
                        list($img_width, $img_height, $img_type) = getimagesize($img_path);
                        //Get image size
                        if (($size = filesize($img_path) / 1024) > 1024) {
                            $img_size = ($size / 1024);
                            $img_sizes = ' MB';
                        } else {
                            $img_size = ($size);
                            $img_sizes = ' KB';
                        }
                        $img_size = round($img_size, 1);
                    } else {
                        $img_size = 0;
                        $img_sizes = ' MB';
                        $img_width = 0;
                        $img_height = 0;
                    }

                    echo '<tr id="record_' . $rec->ID . '" class="' . $classes . '" >';
                    foreach ($columns as $column_name => $column_display_name) {
                        $class = sprintf('class="%1$s column-%1$s"', $column_name);
                        $style = "";

                        if (in_array($column_name, $hidden)) {
                            $style = ' style="display:none;"';
                        }

                        $attributes = $class . $style;

                        switch ($column_name) {
                            case 'cb':
                                echo '<td scope="row" class="check-column" style="padding:8px 10px;">';
                                echo '<input id="cb-select-1" class="metaseo_post" type="checkbox"
                                 name="post[]" value="' . $rec->ID . '">';
                                echo '</td>';
                                break;

                            case 'col_id':
                                echo '<td class="col_id" colspan="1">';
                                echo $i;
                                echo '</td>';
                                break;

                            case 'col_image':
                                $img = sprintf(
                                    "<img src='$thumb_url' width='100px' height='100px' class='metaseo-image'
  data-name='$img_name$type' data-img-post-id='$rec->ID' />"
                                );

                                echo sprintf('<td %2$s colspan="3">%1$s</td>', $img, $attributes);
                                break;

                            case 'col_image_name':
                                $info = '<div class="img-name-wrapper">';
                                $info .= '<textarea name="name_image[' . $rec->ID . ']"
                                 class="metaseo-img-meta metaseo-img-name" data-meta-type="change_image_name"
                                  id="img-name-' . $rec->ID . '" data-post-id="' . $rec->ID . '" rows="2"
                                    data-extension="' . $type . '">' . $img_name . '</textarea>
                                    <span class="img_type">' . $type . '</span>';
                                $info .= '<p>size: ' . $img_size . $img_sizes . '</p>';
                                $info .= '<p>' . $img_width . 'x' . $img_height . '</p>';
                                $info .= '<p>' . $img_date . '</p>';
                                $info .= '<span class="saved-info" style="position:relative">
                                                        <span class="spinner"></span>
                                                        </span>';
                                $info .= '</div>';
                                echo sprintf('<td %2$s colspan="4">%1$s</td>', $info, $attributes);
                                break;

                            case 'col_image_info':
                                $info = "<div class=\"opt-info\" id=\"opt-info-$rec->ID\"></div>";
                                $info .= '<span class="metaseo-loading"></span>';
                                $info .= '
                                                        <div class="popup-bg"></div>
                                                        <div class="popup post-list">
                                                                        <span class="popup-close" title="Close">x</span>
                                    <div class="popup-content"></div>
                             </div>';

                                echo sprintf(
                                    '<td %2$s colspan="5" style="position:relative">%1$s</td>',
                                    $info,
                                    $attributes
                                );
                                break;

                            case 'col_image_alternative':
                                $input = "<textarea name='img_alternative[$rec->ID]' class='metaseo-img-meta'
 data-meta-type='alt_text' id='img-alt-$rec->ID' data-post-id='$rec->ID'
  rows='2'>" . esc_attr($rec->alt) . "</textarea>";
                                $input .= ('<span class="saved-info" style="position:relative">
                                                        <span class="spinner"></span>
                                                        </span>');
                                echo sprintf('<td %2$s colspan="3">%1$s</td>', $input, $attributes);
                                break;

                            case 'col_image_title':
                                $input = "<textarea name='img_title[$rec->ID]' class='metaseo-img-meta'
 data-meta-type='image_title' id='img-title-$rec->ID' data-post-id='$rec->ID'
  rows='2'>" . esc_attr($rec->title) . "</textarea>";
                                $input .= ('<span class="saved-info" style="position:relative">
                                                        <span class="spinner"></span>
                                                        </span>');
                                echo sprintf('<td %2$s colspan="3">%1$s</td>', $input, $attributes);
                                break;

                            case 'col_image_legend':
                                $input = "<textarea name='img_legend[$rec->ID]' class='metaseo-img-meta'
 data-meta-type='image_caption' id='img-legend-$rec->ID' data-post-id='$rec->ID'
  rows='2'>" . esc_attr($rec->legend) . "</textarea>";
                                $input .= '<span class="saved-info" style="position:relative">
                                                        <span class="spinner"></span>
                                                        </span>';
                                echo sprintf('<td %2$s colspan="3">%1$s</td>', $input, $attributes);
                                break;

                            case 'col_image_desc':
                                $input = "<textarea name='img_desc[$rec->ID]' class='metaseo-img-meta'
 data-meta-type='image_description' id='img-desc-$rec->ID' data-post-id='$rec->ID'
  rows='2'>" . esc_attr($rec->des) . "</textarea>";
                                $input .= ('<span class="saved-info" style="position:relative">
                                                        <span class="spinner"></span>
                                                        </span>');
                                echo sprintf('<td %2$s colspan="3">%1$s</td>', $input, $attributes);
                                break;
                        }
                    }

                    echo '</tr>';
                }
            }
        }
    }

    /**
     * Add a size for image
     * @param $response
     * @param $attachment
     * @return mixed
     */
    public static function addMoreAttachmentSizes($response, $attachment)
    {
        $metaseo_imgs_sizes = get_post_meta($attachment->ID, '_metaseo_sizes_optional', true);

        if (!empty($metaseo_imgs_sizes)) {
            foreach ($metaseo_imgs_sizes as $key => $size) {
                $response['sizes'][$key] = $size;
            }
        }

        return $response;
    }

    /**
     * Add to the list of image sizes that are available to administrators in the WordPress Media Library.
     * @param $sizes
     * @return array
     */
    public static function addMoreAttachmentSizesChoose($sizes)
    {
        global $wpdb;
        $query = "SELECT `meta_value` FROM $wpdb->postmeta WHERE
 `meta_key` = '_metaseo_sizes_optional' AND `meta_value` <> ''";

        $imgSizes = $wpdb->get_results($query);
        if (!empty($imgSizes)) {
            foreach ($imgSizes as $metaseo_img_sizes) {
                $metaseo_img_sizes = @unserialize($metaseo_img_sizes->meta_value);
                if (!empty($metaseo_img_sizes)) {
                    foreach ($metaseo_img_sizes as $key => $size) {
                        add_image_size($key, $size['width'], $size['height'], false);
                    }
                }
            }
        }

        $new_sizes = array();

        $added_sizes = get_intermediate_image_sizes();

        // $added_sizes is an indexed array, therefore need to convert it
        // to associative array, using $value for $key and $value
        foreach ($added_sizes as $value) {
            if (strpos($value, '-metaseo') !== false) {
                $_value = substr($value, 0, strrpos($value, '-metaseo'));
            } else {
                $_value = $value;
            }
            $new_sizes[$value] = ucwords(str_replace(array('-', '_'), ' &ndash; ', $_value));
        }

        // This preserves the labels in $sizes, and merges the two arrays
        $new_sizes = array_merge($new_sizes, $sizes);

        return $new_sizes;
    }

    /**
     * Display page fix meta list
     * @param $img_post_id
     * @param $posts
     * @param $meta_counter
     * @param $p
     * @param $im
     */
    private static function display_fix_metas_list($img_post_id, $posts, $meta_counter, $p, $im)
    {
        if ($meta_counter) {
            $header = sprintf(
                __('We found %s image with missing Title or Alt text', 'wp-meta-seo'),
                $meta_counter . $im . $p
            );
        } else {
            $header = __('We found 0 image with missing Title or Alt text', 'wp-meta-seo');
        }

        //Get default meta information of the image
        $img_post = get_post($img_post_id);
        $alt = get_post_meta($img_post_id, '_wp_attachment_image_alt', true);
        $title = $img_post->post_title;
        ?>
        <h3 class="content-header"><?php echo $header ?></h3>
        <div class="content-box">
            <table class="wp-list-table widefat fixed posts">
                <thead></thead>
                <tbody>
                <?php $alternate = ''; ?>
                <?php if (count($posts) < 1) : ?>
                    <tr>
                        <td colspan="10" style="height:95%">
                            <?php echo __('This image has still not been inserted in any post!', 'wp-meta-seo') ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <tr class="metaseo-border-bottom">
                        <td colspan="1"><?php _e('ID', 'wp-meta-seo') ?></td>
                        <td colspan="2"><?php _e('Title', 'wp-meta-seo') ?></td>
                        <td colspan="2"><?php _e('Image', 'wp-meta-seo') ?></td>
                        <td colspan="5"><?php _e('Image information', 'wp-meta-seo') ?></td>
                    </tr>
                    <?php foreach ($posts as $post) : ?>
                        <?php foreach (wpmsUtf8($post['meta'], 'decode') as $k => $meta) : ?>
                            <?php
                            $alternate = 'alternate' == $alternate ? '' : 'alternate';
                            $file_name = substr($meta['img_src'], strrpos($meta['img_src'], '/') + 1);
                            ?>
                            <tr class="<?php echo $alternate ?>">
                                <td colspan="1"><?php echo $post['ID'] ?></td>
                                <td colspan="2">
                                    <p><?php echo $post['title'] ?></p>
                                </td>
                                <td colspan="2">
                                    <div class="metaseo-img-wrapper">
                                        <img src="<?php echo $meta['img_src'] ?>"/>
                                    </div>
                                </td>
                                <td colspan="5">
                                    <?php foreach ($meta['type'] as $type => $value) : ?>
                                        <div class="metaseo-img-wrapper">
                                            <?php
                                            $specialChr = array('"', '\'');
                                            foreach ($specialChr as $chr) {
                                                $value = str_replace($chr, htmlentities2($chr), $value);
                                            }
                                            if ($type == 'alt') {
                                                $lb = __('Image Alt', 'wp-meta-seo');
                                            } else {
                                                $lb = __('Image Title', 'wp-meta-seo');
                                            }
                                            if ($value == '') {
                                                $placeholder = __(ucfirst($type) . ' is empty', 'wp-meta-seo');
                                            } else {
                                                $placeholder = '';
                                            }
                                            ?>
                                            <div>
                                                <label class="metaseo-img-lb"><?php echo $lb; ?></label>
                                                <input type="text" value="<?php echo esc_attr($value) ?>"
                                                       id="metaseo-img-<?php echo $type . '-' . $post['ID'] ?>"
                                                       class="metaseo-fix-meta metaseo-img-<?php echo $type ?>"
                                                       data-meta-key="_metaseo_fix_metas"
                                                       data-post-id="<?php echo $post['ID'] ?>"
                                                       data-img-post-id="<?php echo $img_post_id ?>"
                                                       data-meta-type="<?php echo $type ?>"
                                                       data-meta-order="<?php echo $k ?>"
                                                       data-file-name="<?php echo $file_name; ?>"
                                                       placeholder="<?php echo $placeholder ?>"
                                                       onfocus="metaseo_fix_meta(this);" onblur="updateInputBlur(this)"
                                                       onkeydown="return checkeyCode(event)"/>
                                            </div>

                                            <?php if (trim($$type) != '' && trim($$type) != $value) : ?>
                                                <a class="meta-default wpmsbtn wpmsbtn_small" href="#"
                                                   data-default-value="<?php echo esc_attr($$type) ?>"
                                                   title="Add to input box"
                                                   onclick="add_meta_default(this)"><?php _e('Copy ', 'wp-meta-seo');
                                                    echo $lb ?></a>
                                                <span class="img_seo_type"><?php echo $$type; ?></span>
                                            <?php endif ?>
                                            <span class="spinner"></span>
                                        </div>
                                    <?php endforeach ?>
                                    <span class="saved-info"></span>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endforeach ?>
                <?php endif ?>
                </tbody>
                <tfoot></tfoot>
            </table>
        </div>
        <div style="padding:5px"></div>
        <?php
    }

    /**
     * Display page resize image list
     * @param $img_post_id
     * @param $posts
     * @param $img_counter
     * @param $p
     * @param $im
     */
    private static function display_resize_image_list($img_post_id, $posts, $img_counter, $p, $im)
    {

        $header = __('We found some images you can resize...', 'wp-meta-seo');
        ?>
        <h3 class="content-header"><?php echo $header ?></h3>
        <div class="content-box">
            <table class="wp-list-table widefat fixed posts">
                <thead></thead>
                <tbody>
                <tr class="metaseo-border-bottom">
                    <td colspan="1">ID</td>
                    <td colspan="3">Title</td>
                    <td colspan="4">Current Images</td>
                    <td colspan="2" class="metaseo-action">Action</td>
                    <td colspan="4">After Replacing</td>
                </tr>
                <?php
                $alternate = "";
                foreach ($posts as $post) :
                    ?>
                    <?php $alternate = 'alternate' == $alternate ? '' : 'alternate'; ?>
                    <tr class="<?php echo $alternate ?>">
                        <td colspan="1"><?php echo $post['ID'] ?></td>
                        <td colspan="3">
                            <p><?php echo $post['title'] ?></p>
                        </td>
                        <td colspan="4" style="overflow: hidden;">
                            <?php foreach ($post['img_before_optm'] as $key => $src) : ?>
                                <div class="metaseo-img-wrapper">
                                    <div class="metaseo-img">
                                        <img width="<?php echo @$src['width']; ?>"
                                             height="<?php #echo @$src['height'] ; ?>" src="<?php echo $src['src'] ?>"/>
                                        <div class="img-choosen">

                                            <div class="pure-checkbox">
                                                <input id="checkin-<?php echo $post['ID'] ?>" checked type="checkbox"
                                                       class="metaseo-checkin checkin-<?php echo $post['ID'] ?>"
                                                       value="<?php echo $key ?>"
                                                       id="checkin-<?php echo $post['ID'] . '-' . $key ?>"
                                                       onclick="uncheck(this)">
                                                <label for="checkin-<?php echo $post['ID'] ?>"></label>
                                            </div>
                                        </div>
                                        <p class="metaseo-msg"></p>
                                    </div>
                                    <div class="dimension">
                                        Orig. <br>
                                        <span>Dimensions</span>: <?php echo $src['dimension'] ?><br>
                                        <span>File size</span>: <?php echo $src['size'] . ' ' . $src['sizes'] ?>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </td>
                        <td colspan="2" class="metaseo-action">
                            <a href="javascript:void(0);"
                               class="metaseo-optimize wpmsbtn wpmsbtn_small wpmsbtn_secondary"
                               data-img-post-id="<?php echo $img_post_id ?>" data-post-id="<?php echo $post['ID'] ?>"
                               onclick="optimize_imgs(this)"><?php echo __('Replace?', 'wp-meta-seo') ?></a>
                            <span class="optimizing spinner"></span>
                        </td>
                        <td colspan="4">
                            <?php foreach ($post['img_after_optm'] as $src) : ?>
                                <div class="metaseo-img-wrapper">
                                    <div class="metaseo-img">
                                        <img src="<?php echo $src['src'] ?>"/>
                                    </div>
                                    <div class="dimension">
                                        OPT <br>
                                        <span>Dimensions</span>: <?php echo $src['dimension'] ?><br>
                                        <span>File size</span>: <?php echo $src['size'] . ' ' . $src['sizes'] ?>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </td>
                    </tr>

                <?php endforeach ?>
                <tr class="metaseo-border-top">
                    <td colspan="8"></td>
                    <td colspan="2">
                        <a href="javascript:void(0);" id="metaseo-replace-all" class="wpmsbtn wpmsbtn_small"
                           onclick="optimize_imgs_group(this)">
                            <?php echo __('Replace All', 'wp-meta-seo') ?>
                        </a>
                        <span class="optimizing spinner"></span>
                    </td>
                    <td colspan="4"></td>
                </tr>
                </tbody>
                <tfoot></tfoot>
            </table>
        </div>
        <div style="padding:5px"></div>
        <?php
    }

    /*
     * Ajax optimize image and update content
     */
    public static function optimizeImages()
    {
        if (!empty($_POST['post_id']) && !empty($_POST['img_post_id'])) {
            $post_id = intval($_POST['post_id']);
            $img_post_id = intval($_POST['img_post_id']);
            if (!empty($_POST['img_exclude'])) {
                $img_exclude = $_POST['img_exclude'];
            } else {
                $img_exclude = array();
            }

            $ret = ImageHelper::optimizeImages($post_id, $img_post_id, $img_exclude);
        } else {
            $ret = array(
                'success' => false,
                'msg' => __('The post is not existed, please choose one another!', 'wp-meta-seo')
            );
        }

        echo json_encode($ret);
        wp_die();
    }

    /*
     * Retrieves a modified URL query string.
     */
    public function processAction()
    {
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $redirect = false;

        if (isset($_POST['search']) and $_POST['search'] === 'Search') {
            $current_url = add_query_arg(
                array(
                    "search" => "Search",
                    "txtkeyword" => urlencode(stripslashes($_POST["txtkeyword"]))),
                $current_url
            );
            $redirect = true;
        }

        if (isset($_POST['sldate'])) {
            $current_url = add_query_arg(array("sldate" => $_POST["sldate"]), $current_url);
            $redirect = true;
        }

        if (isset($_POST['slmeta'])) {
            $current_url = add_query_arg(array("slmeta" => $_POST["slmeta"]), $current_url);
            $redirect = true;
        }

        if (!empty($_POST['paged'])) {
            $current_url = add_query_arg(array("paged" => intval($_POST['paged'])), $current_url);
            $redirect = true;
        }

        if (!empty($_POST['metaseo_imgs_per_page'])) {
            $current_url = add_query_arg(
                array(
                    "metaseo_imgs_per_page" => intval($_POST['metaseo_imgs_per_page'])
                ),
                $current_url
            );
            $redirect = true;
        }

        if (!empty($_POST['wpms_lang_list'])) {
            $current_url = add_query_arg(array("wpms_lang_list" => $_POST['wpms_lang_list']), $current_url);
            $redirect = true;
        }


        if ($redirect === true) {
            wp_redirect($current_url);
            ob_end_flush();
            exit();
        }
    }

    /**
     * Ajax get list of posts contain this image and its clones
     */
    public static function loadPostsCallback()
    {
        $_POST = stripslashes_deep($_POST);
        $post_id = intval($_POST['post_id']);
        $img = trim($_POST['img_name']);
        $opt_key = strtolower(trim($_POST['opt_key']));
        if ($post_id && !empty($img) && !empty($opt_key)) {
            $fn = "display_{$opt_key}_list";
            if (method_exists('MetaSeoImageListTable', $fn)) {
                //Get list of posts contain this image and its clones
                $posts = ImageHelper::getPostList($post_id, $opt_key);

                if (count($posts) > 0) {
                    $img_counter = 0;
                    //Now the time to resize the images
                    if ($opt_key === 'resize_image') {
                        $upload_dir = wp_upload_dir();
                        $metaseo_sizes_optional = get_post_meta(
                            $post_id,
                            '_metaseo_sizes_optional',
                            true
                        );
                        if (!is_array($metaseo_sizes_optional)) {
                            $metaseo_sizes_optional = array();
                        }
                        $attachment_meta_data = wp_get_attachment_metadata($post_id);

                        foreach ($posts as &$post) {
                            foreach ($post['img_after_optm'] as &$img) {
                                $img_counter++;
                                $destination = $upload_dir['basedir'] . '/' . $img['path'];
                                $iresize = ImageHelper::IResize(
                                    $img['src_origin'],
                                    $img['width'],
                                    $img['height'],
                                    $destination
                                );

                                if ($iresize) {
                                    $size = (filesize($destination) / 1024);
                                    if ($size > 1024) {
                                        $size = $size / 1024;
                                        $sizes = 'MB';
                                    } else {
                                        $sizes = 'KB';
                                    }
                                    $size = @round($size, 1);
                                    $img['size'] = $size;
                                    $img['sizes'] = $sizes;
                                }

                                $kpart = ImageHelper::IGetPart($img['path']);
                                $key = preg_replace('/\-(\d+)x(\d+)$/i', '-metaseo${1}${2}', $kpart->name);
                                $key = strtolower($key);
                                $file = substr($img['path'], strrpos($img['path'], '/') + 1);
                                if (!in_array($key, array_keys($metaseo_sizes_optional))) {
                                    $metaseo_sizes_optional[$key] = array(
                                        'url' => $img['src'],
                                        'width' => $img['width'],
                                        'height' => $img['height'],
                                        'orientation' => 'landscape',
                                    );
                                }

                                if (!isset($attachment_meta_data['sizes'][$key])) {
                                    $attachment_meta_data['sizes'][$key] = array(
                                        'file' => $file,
                                        'width' => $img['width'],
                                        'height' => $img['height'],
                                        'mime-type' => 'image/jpeg'
                                    );
                                }
                            }
                        }

                        wp_update_attachment_metadata($post_id, $attachment_meta_data);
                        update_post_meta($post_id, '_metaseo_sizes_optional', $metaseo_sizes_optional);
                    } elseif ($opt_key === 'fix_metas') {
                        $toEdit = false;
                        $pIDs = array();
                        foreach ($posts as $ID => &$post) {
                            $img_counter += count($post['meta']);
                            foreach ($post['meta'] as $order => $meta) {
                                if ($meta['type']['alt'] == '' || $meta['type']['title'] == '') {
                                    $toEdit = true;
                                }

                                if ($meta['type']['alt'] != '' && $meta['type']['title'] != '') {
                                    $pIDs[$ID][] = $order;
                                }
                            }
                        }

                        if ($toEdit === true) {
                            foreach ($pIDs as $ID => $orders) {
                                foreach ($orders as $order) {
                                    unset($posts[$ID]['meta'][$order]);
                                    if ($img_counter > 0) {
                                        $img_counter--;
                                    }
                                }

                                if (empty($posts[$ID]['meta'])) {
                                    unset($posts[$ID]);
                                }
                            }
                        }
                    }
                    //-----------------------------
                }

                //This is a bit crazy but could give more exact information
                if (count($posts) > 1) {
                    $p = ' in ' . count($posts) . ' posts ';
                } else {
                    $p = '';
                }

                if (isset($img_counter) and $img_counter > 1) {
                    $im = ' images ';
                } else {
                    if (!isset($img_counter)) {
                        $img_counter = 0;
                    }
                    $im = ' image ';
                }

                self::$fn($post_id, $posts, $img_counter, $p, $im);
                wp_die();
            }
        }
    }

    /**
     * Scan post to find image good and not good
     */
    public static function scanPostsCallback()
    {
        $_POST = stripslashes_deep($_POST);
        $imgs = $_POST['imgs'];
        if (!empty($imgs)) {
            if (!is_array($imgs)) {
                $ret['success'] = false;
                $ret['msg'] = 'No images are available, please check again!';
                wp_send_json($ret);
            }

            $_imgs = array();
            $_imgs[trim($imgs['name'])] = $imgs['img_post_id'];
            unset($imgs);

            if (!count($_imgs)) {
                $ret['success'] = false;
                $ret['msg'] = 'No images are available, please check again!';
                wp_send_json($ret);
            }

            $msg = ImageHelper::IScanPosts($_imgs, true);
            $ret['msg'] = $msg;
            $ret['success'] = true;

            if (isset($_POST['imgs']['type']) && $_POST['imgs']['type'] == 'update_meta') {
                if ($ret['msg'][$_POST['imgs']['img_post_id']]['imNotGood']['warning']) {
                    update_post_meta($_POST['imgs']['img_post_id'], 'wpms_missing_information', 1);
                } else {
                    delete_post_meta($_POST['imgs']['img_post_id'], 'wpms_missing_information');
                }
                if ($ret['msg'][$_POST['imgs']['img_post_id']]['iNotGood']['warning']) {
                    update_post_meta($_POST['imgs']['img_post_id'], 'wpms_resizeimages', 1);
                } else {
                    delete_post_meta($_POST['imgs']['img_post_id'], 'wpms_resizeimages');
                }
            }
        } else {
            $ret['success'] = false;
            $ret['msg'] = 'No images are available, please check again!';
        }

        wp_send_json($ret);
    }

    /**
     * Ajax update image meta
     */
    public static function updateMetaCallback()
    {
        $response = new stdClass();
        $response->updated = false;
        if (!empty($_POST['addition']['meta_key'])) {
            self::updateImgMetaCallback($_POST['addition'], true);
        }

        if (!empty($_POST['meta_type']) and $_POST['meta_type'] == 'change_image_name') {
            self::updateImageNameCallback();
        }

        if (!empty($_POST['meta_type']) && !empty($_POST['post_id'])) {
            $meta_type = strtolower(trim($_POST['meta_type']));
            $post_id = intval($_POST['post_id']);

            if (!isset($_POST['meta_value'])) {
                $meta_value = '';
            } else {
                $meta_value = trim($_POST['meta_value']);
                if (preg_match('/[<>\/\'\"]+/', $meta_value)) {
                    $response->updated = false;
                    $response->message = 'Should not html tag or special char';

                    echo json_encode($response);
                    wp_die();
                }
            }

            $label = str_replace('_', ' ', $meta_type);
            $label = ucfirst($label);

            $aliases = array(
                'image_title' => 'post_title',
                'image_caption' => 'post_excerpt',
                'image_description' => 'post_content',
                'alt_text' => '_wp_attachment_image_alt'
            );

            if ($meta_type != 'alt_text') {
                $data = array('ID' => $post_id, $aliases[$meta_type] => $meta_value);
                if (wp_update_post($data)) {
                    $response->updated = true;
                    $response->msg = __($label . ' was saved', 'wp-meta-seo');
                }
            } else {
                update_post_meta($post_id, $aliases[$meta_type], $meta_value);
                $response->updated = true;
                $response->msg = __($label . ' was saved', 'wp-meta-seo');
            }

            if ($meta_type == 'alt_text') {
                $settings = get_option('_metaseo_settings');
                if (!isset($settings['metaseo_overridemeta']) || (!empty($settings['metaseo_overridemeta'])
                        && $settings['metaseo_overridemeta'] == 1)) {
                    // call function auto override in content

                    self::autoUpdatePostContent($post_id, $meta_type, $meta_value);
                    $response->type = 'auto_override';
                    $response->pid = $post_id;
                    $response->imgname = $_POST['img_name'];
                }
            }
        } else {
            $response->msg = __('There is a problem when update image meta!', 'wp-meta-seo');
        }

        echo json_encode($response);
        wp_die();
    }

    /**
     * function auto override in content
     * @param $post_id
     * @param $meta_type
     * @param $meta_value
     */
    public static function autoUpdatePostContent($post_id, $meta_type, $meta_value)
    {
        $_POST = stripslashes_deep($_POST);
        $img = trim($_POST['img_name']);
        if ($post_id && !empty($img)) {
            $fn = "display_fix_metas_list";
            if (method_exists('MetaSeoImageListTable', $fn)) {
                //Get list of posts contain this image and its clones
                $posts = ImageHelper::getPostList($post_id, 'fix_metas');
                if (count($posts) > 0) {
                    $img_counter = 0;
                    //Now the time to resize the images
                    $toEdit = false;
                    $pIDs = array();
                    foreach ($posts as $ID => &$post) {
                        $img_counter += count($post['meta']);
                        foreach ($post['meta'] as $order => $meta) {
                            if ($meta['type']['alt'] == '' || $meta['type']['title'] == '') {
                                $toEdit = true;
                            }

                            if ($meta['type']['alt'] != '' && $meta['type']['title'] != '') {
                                $pIDs[$ID][] = $order;
                            }
                        }
                    }

                    if ($toEdit === true) {
                        foreach ($pIDs as $ID => $orders) {
                            foreach ($orders as $order) {
                                unset($posts[$ID]['meta'][$order]);
                                if ($img_counter > 0) {
                                    $img_counter--;
                                }
                            }

                            if (empty($posts[$ID]['meta'])) {
                                unset($posts[$ID]);
                            }
                        }
                    }
                    //-----------------------------
                }
            }
        }
        if (!empty($posts)) {
            foreach ($posts as $p) {
                foreach ($p['meta'] as $k => $meta) {
                    $addition = array();
                    $addition['meta_key'] = '_metaseo_fix_metas';
                    if ($meta_type == 'image_title') {
                        $addition['meta_type'] = 'title';
                    } else {
                        $addition['meta_type'] = 'alt';
                    }

                    $addition['meta_value'] = $meta_value;
                    $addition['post_id'] = $p['ID'];
                    $addition['meta_order'] = $k;
                    $addition['img_post_id'] = $post_id;
                    self::updateImgMetaCallback($addition, false);
                }
            }
        }
    }

    /**
     * Update image name
     */
    public static function updateImageNameCallback()
    {
        global $wpdb;
        $postID = (int)$_POST['post_id'];
        $name = trim($_POST['meta_value']);
        $iname = preg_replace('/(\s{1,})/', '-', $name);
        $img_meta = get_post_meta($postID, '_wp_attachment_metadata', true);
        $linkold = $img_meta['file'];
        $response = new stdClass();
        $response->updated = false;
        $response->msg = __('There is a problem when update image name', 'wp-meta-seo');

        $upload_dirs = wp_upload_dir();
        $upload_dir = $upload_dirs['basedir'];
        $oldpart = ImageHelper::IGetPart($linkold);
        $old_name = $oldpart->name;

        if ($name !== "") {
            if (file_exists($upload_dir . "/" . $linkold)) {
                $newFileName = $oldpart->base_path . $iname . $oldpart->ext;
                // check file not exist
                if (!file_exists($upload_dir . "/" . $newFileName)) {
                    if (rename($upload_dir . "/" . $linkold, $upload_dir . "/" . $newFileName)) {
                        $post_title = get_the_title($postID);
                        $where = array('ID' => $postID);
                        $guid = $upload_dirs['baseurl'] . "/" . $newFileName;
                        if (!$post_title) {
                            $id = $wpdb->update(
                                $wpdb->posts,
                                array(
                                    'guid' => $guid,
                                    'post_title' => $name,
                                    'post_name' => strtolower($iname)
                                ),
                                $where
                            );
                        } else {
                            $id = $wpdb->update($wpdb->posts, array('guid' => $guid), $where);
                        }

                        if ($id) {
                            $attached_metadata = get_post_meta($postID, "_wp_attachment_metadata", true);
                            $attached_metadata["file"] = $newFileName;

                            $images_to_rename = array($oldpart->name . $oldpart->ext => $iname . $oldpart->ext);
                            foreach ($attached_metadata['sizes'] as &$clone) {
                                $clone_file_new = ImageHelper::IReplace($iname, $clone['file']);
                                $clone_path = $upload_dir . '/' . $oldpart->base_path . $clone['file'];
                                $clone_path_new = $upload_dir . '/' . $oldpart->base_path . $clone_file_new;

                                if (@rename($clone_path, $clone_path_new)) {
                                    $images_to_rename[$clone['file']] = $clone_file_new;
                                    $clone['file'] = $clone_file_new;
                                }
                            }

                            $metadats = get_post_meta($postID, '_wp_attachment_metadata', true);
                            $sizes = $metadats['sizes'];

                            // get list image url and image thumbnail url
                            $list_thum_url = array();
                            $imageUrl = wp_get_attachment_url($postID);
                            $list_thum_url[] = $imageUrl;
                            foreach ($sizes as $key => $size) {
                                $thum_url = wp_get_attachment_image_src($postID, $key);
                                $list_thum_url[] = $thum_url[0];
                            }

                            $w = '';
                            $w .= '(';

                            $i = 0;
                            foreach ($list_thum_url as $url) {
                                $i++;
                                if ($i == count($list_thum_url)) {
                                    $w .= ' post_content LIKE "%' . $url . '%"';
                                } else {
                                    $w .= ' post_content LIKE "%' . $url . '%" OR';
                                }
                            }

                            $w .= ')';
                            $where = array();
                            $where[] = "(`post_type` = 'post' or `post_type` = 'page')";
                            $where[] = "post_content LIKE '%<img%>%'";
                            $where[] = $w;
                            $query = "SELECT `ID`, `post_title`, `post_content`, `post_type`, `post_date`
                                                            FROM $wpdb->posts
                                                            WHERE " . implode(' AND ', $where) . " ORDER BY ID";
                            // query post
                            $posts = $wpdb->get_results($query);
                            $imgs = array($old_name . $oldpart->ext => $postID);
                            $posts_contain_img = array();
                            foreach ($posts as $post) {
                                $ifound = ImageHelper::IScan($post->post_content, $imgs);
                                if (count($ifound) > 0) {
                                    $posts_contain_img[] = $post->ID;
                                }
                            }

                            // update post
                            foreach ($posts_contain_img as $id) {
                                if ($post = get_post($id)) {
                                    foreach ($images_to_rename as $src_before => $src_after) {
                                        $src_before = '/' . $src_before;
                                        $src_after = '/' . $src_after;
                                        $post->post_content = str_replace($src_before, $src_after, $post->post_content);
                                    }
                                    remove_action('post_updated', array('MetaSeoBrokenLinkTable', 'updatePost'));
                                    wp_update_post(
                                        array(
                                            'ID' => $post->ID,
                                            'post_content' => $post->post_content)
                                    );

                                    unset($post, $posts_contain_img);
                                    //---------------------------------
                                }
                            }

                            /** Update Image registered to Attachment sizes on Add media page* */
                            $sizeOptional = get_post_meta($postID, '_metaseo_sizes_optional', true);
                            $newOptional = array();
                            if (!empty($sizeOptional) && is_array($sizeOptional)) {
                                foreach ($sizeOptional as $key => $detail) {
                                    $pattern = '/^' . strtolower($old_name) . '(-metaseo\d+)$/';
                                    $key = preg_replace($pattern, strtolower($iname) . '${1}', $key);
                                    $detail['url'] = ImageHelper::IReplace($iname, $detail['url']);
                                    $newOptional[$key] = $detail;
                                }

                                update_post_meta($postID, '_metaseo_sizes_optional', $newOptional);
                                unset($sizeOptional, $newOptional);
                            }

                            //Need to update optimization info of this image
                            ImageHelper::IScanPosts(array($iname . $oldpart->ext => $postID), true);

                            update_post_meta($postID, '_wp_attached_file', $newFileName);
                            update_post_meta($postID, '_wp_attachment_metadata', $attached_metadata);

                            $response->updated = true;
                            $response->msg = __('Image name was changed', 'wp-meta-seo');
                        } else {
                            $response->iname = $old_name;
                            $response->msg = __('There is a problem when update image name', 'wp-meta-seo');
                        }
                    }
                } else {
                    $response->msg = __('File name already given!', 'wp-meta-seo');
                    $response->iname = $old_name;
                }
            } else {
                $response->iname = $old_name;
                $response->msg = __('File is not existed', 'wp-meta-seo');
            }
        } else {
            $response->iname = $old_name;
            $response->msg = __('Should not be empty', 'wp-meta-seo');
        }
        echo json_encode($response);
        wp_die();
    }

    /**
     * Update image meta
     * @param $wpmspost
     * @param bool $return
     */
    public static function updateImgMetaCallback($wpmspost, $return = true)
    {
        $response = new stdClass();
        $response->updated = false;

        foreach ($wpmspost as $k => $v) {
            if (!$v && !in_array($k, array('meta_value', 'meta_order'))) {
                $response->msg = __('There is a problem when update image meta!', 'wp-meta-seo');

                echo json_encode($response);
                wp_die();
            }
        }

        $meta_key = strtolower(trim($wpmspost['meta_key']));
        $meta_type = strtolower(trim($wpmspost['meta_type']));
        $meta_value = htmlspecialchars(trim($wpmspost['meta_value']));
        $meta_order = intval($wpmspost['meta_order']);
        $img_post_id = intval($wpmspost['img_post_id']);
        $post_id = intval($wpmspost['post_id']);
        $meta = get_post_meta($img_post_id, $meta_key, true);
        //Update new value for meta info of this image in wp_postmeta
        $meta[$post_id]['meta'][$meta_order]['type'][$meta_type] = wpmsUtf8($meta_value);
        update_post_meta($img_post_id, $meta_key, $meta);

        //Then we must update this meta info in the appropriate post content
        if (!$post = get_post($post_id)) {
            $response->msg = __('The post has been deleted before, please check again!', 'wp-meta-seo');
        } else {
            if ($post->post_content !== '') {
                //Split content part that do not contain img tag
                $post_content_split = preg_split(
                    '/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i',
                    $post->post_content
                );
                //Get all img tag from the content
                preg_match_all(
                    '/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i',
                    $post->post_content,
                    $matches
                );
                $img_tags = $matches[0];
                if (isset($img_tags[$meta_order])) {
                    $pattern = '/' . $meta_type . '\s*?\=?\"[^\"]*\"/i';
                    // remove attr
                    $img_tags[$meta_order] = preg_replace(
                        '#(<img.+?)' . $meta_type . '=(["\']?)\d*\2(.*?/?>)#i',
                        '$1$3',
                        $img_tags[$meta_order]
                    );
                    preg_match('/' . $meta_type . '="(.+?)"/', $img_tags[$meta_order], $matches);
                    // update attr
                    $img_tags[$meta_order] = preg_replace(
                        '/(<img\b[^><]*)>/i',
                        '$1 ' . $meta_type . '="' . $meta_value . '">',
                        $img_tags[$meta_order]
                    );
                    // create new post content
                    $post_content = '';
                    foreach ($post_content_split as $key => $split) {
                        if (isset($img_tags[$key])) {
                            $img_tag = $img_tags[$key];
                        } else {
                            $img_tag = '';
                        }
                        $post_content .= $split . $img_tag;
                    }

                    remove_action('post_updated', array('MetaSeoBrokenLinkTable', 'updatePost'));

                    //Update content of this post.
                    if (!wp_update_post(array('ID' => $post->ID, 'post_content' => $post_content))) {
                        $response->msg = __('The post haven\'t been updated, please check again!', 'wp-meta-seo');
                    } else {
                        update_option('wpms_last_update_post', time());
                        $response->updated = true;
                        $response->msg = __(ucfirst($meta_type) . ' was saved', 'wp-meta-seo');
                    }
                } else {
                    $response->msg = __('This image has been removed from
                     the post, please check again!', 'wp-meta-seo');
                }
            } else {
                $response->msg = __('Content of the post is empty, please check again', 'wp-meta-seo');
            }
        }

        if ($return == true) {
            echo json_encode($response);
            wp_die();
        }
    }

    /**
     * Scan image metas
     */
    public static function imageScanMeta()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        global $wpdb;
        $limit = 1;
        $ofset = ((int)$_POST['paged'] - 1) * $limit;

        $where = array();
        $where[] = " post_type='attachment' ";
        $where[] = " ((post_mime_type='image/jpeg') OR (post_mime_type='image/jpg')
         OR (post_mime_type='image/png') OR (post_mime_type='image/gif')) ";
        $query = "SELECT COUNT(ID) FROM $wpdb->posts as posts WHERE " . implode(" and ", $where);
        $count_images = $wpdb->get_var($query);
        $present = (100 / $count_images) * $limit;

        $k = 0;
        $sql = $wpdb->prepare(
            "SELECT ID FROM " . $wpdb->posts . " WHERE " . implode(" AND ", $where) . " LIMIT %d OFFSET %d",
            array(
                $limit,
                $ofset
            )
        );
        $attachments = $wpdb->get_results($sql);
        if (empty($attachments)) {
            wp_send_json(array('status' => 'ok'));
        }

        foreach ($attachments as $image) {
            $path = get_attached_file($image->ID);
            $infos = pathinfo($path);
            $img_name = $infos['basename'];
            $imgs = array('name' => $img_name, 'img_post_id' => $image->ID);
            $results = MetaSeoImageListTable::scanPostsMeta($imgs, false, 0);
            if ($results['msg'][$image->ID]['imNotGood']['warning']) {
                update_post_meta($image->ID, 'wpms_missing_information', 1);
            } else {
                delete_post_meta($image->ID, 'wpms_missing_information');
            }
            if ($results['msg'][$image->ID]['iNotGood']['warning']) {
                update_post_meta($image->ID, 'wpms_resizeimages', 1);
            } else {
                delete_post_meta($image->ID, 'wpms_resizeimages');
            }
            $k++;
        }

        if ($k >= $limit) {
            wp_send_json(array('status' => 'error_time', 'paged' => $_POST['paged'], 'precent' => $present));
        } else {
            wp_send_json(array('status' => 'ok'));
        }
    }

    /**
     * @param $imgs
     * @param bool $delete
     * @param int $pid
     * @return mixed
     */
    public static function scanPostsMeta($imgs, $delete = false, $pid = 0)
    {
        if (!empty($imgs)) {
            if (!is_array($imgs)) {
                $ret['success'] = false;
                $ret['msg'] = 'No images are available, please check again!';
                return $ret;
            }

            $_imgs = array();
            $_imgs[trim($imgs['name'])] = $imgs['img_post_id'];
            unset($imgs);

            if (!count($_imgs)) {
                $ret['success'] = false;
                $ret['msg'] = 'No images are available, please check again!';
                return $ret;
            }

            $msg = ImageHelper::IScanPosts($_imgs, $delete, $pid);
            $ret['msg'] = $msg;
            $ret['success'] = true;
        } else {
            $ret['success'] = false;
            $ret['msg'] = 'No images are available, please check again!';
        }

        return $ret;
    }

    /**
     * update meta missing info and meta resize after delete post
     * @param $pid
     */
    public static function deleteAttachment($pid)
    {
        $post = get_post($pid);
        if (!empty($post)) {
            $post_type = get_post_type($pid);
            $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
            if (isset($post_types['attachment'])) {
                unset($post_types['attachment']);
            }

            if (in_array($post_type, $post_types)) {
                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                @$dom->loadHtml($post->post_content);
                $tags = $dom->getElementsByTagName('img');
                if (!empty($tags)) {
                    foreach ($tags as $tag) {
                        $url = $tag->getAttribute('src');
                        $postid = MetaSeoImageListTable::getAttachmentId($url);
                        $path = get_attached_file($postid);
                        $infos = pathinfo($path);
                        $img_name = $infos['basename'];
                        $imgs = array('name' => $img_name, 'img_post_id' => $postid);
                        $results = MetaSeoImageListTable::scanPostsMeta($imgs, true, $pid);

                        // update or delete meta
                        if ($results['msg'][$postid]['imNotGood']['warning']) {
                            update_post_meta($postid, 'wpms_missing_information', 1);
                        } else {
                            delete_post_meta($postid, 'wpms_missing_information');
                        }
                        if ($results['msg'][$postid]['iNotGood']['warning']) {
                            update_post_meta($postid, 'wpms_resizeimages', 1);
                        } else {
                            delete_post_meta($postid, 'wpms_resizeimages');
                        }
                    }
                }
            }
        }
    }

    /**
     * update meta missing info and meta resize after update post
     * @param $post_ID
     * @param $post_after
     * @param $post_before
     */
    public static function updatePost($post_ID, $post_after, $post_before)
    {
        $old_imgs = MetaSeoImageListTable::getImagesInContent($post_before); // return list img in post before
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHtml($post_after->post_content);
        $tags = $dom->getElementsByTagName('img');
        $new_imgs = array(); // return list img in post after
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $url = $tag->getAttribute('src');
                $postid = MetaSeoImageListTable::getAttachmentId($url);
                $new_imgs[] = $postid;
                $post = get_post($postid);
                if (!empty($post)) {
                    $path = get_attached_file($postid);
                    $infos = pathinfo($path);
                    $img_name = $infos['basename'];
                    $imgs = array('name' => $img_name, 'img_post_id' => $postid);
                    $results = MetaSeoImageListTable::scanPostsMeta($imgs, false, 0);

                    // update or delete meta
                    if ($results['msg'][$postid]['imNotGood']['warning']) {
                        update_post_meta($postid, 'wpms_missing_information', 1);
                    } else {
                        delete_post_meta($postid, 'wpms_missing_information');
                    }
                    if ($results['msg'][$postid]['iNotGood']['warning']) {
                        update_post_meta($postid, 'wpms_resizeimages', 1);
                    } else {
                        delete_post_meta($postid, 'wpms_resizeimages');
                    }
                }
            }
        }

        // remove post meta
        $imgs_diff = array_diff($old_imgs, $new_imgs);
        if (!empty($imgs_diff)) {
            foreach ($imgs_diff as $id) {
                $path = get_attached_file($id);
                $infos = pathinfo($path);
                $img_name = $infos['basename'];
                $imgs = array('name' => $img_name, 'img_post_id' => $id);
                $results = MetaSeoImageListTable::scanPostsMeta($imgs, true, $post_ID);

                // update or delete meta
                if ($results['msg'][$id]['imNotGood']['warning']) {
                    update_post_meta($id, 'wpms_missing_information', 1);
                } else {
                    delete_post_meta($id, 'wpms_missing_information');
                }
                if ($results['msg'][$id]['iNotGood']['warning']) {
                    update_post_meta($id, 'wpms_resizeimages', 1);
                } else {
                    delete_post_meta($id, 'wpms_resizeimages');
                }
            }
        }
    }

    /**
     * Get all images id in post content
     * @param $post_before
     * @return array
     */
    public static function getImagesInContent($post_before)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHtml($post_before->post_content);
        $tags = $dom->getElementsByTagName('img');
        $ids = array();
        if (empty($tags)) {
            return $ids;
        }
        foreach ($tags as $tag) {
            $url = $tag->getAttribute('src');
            $postid = MetaSeoImageListTable::getAttachmentId($url);
            $ids[] = $postid;
        }
        return $ids;
    }

    /**
     * Get attachment ID from URL
     * @param string $url URl of attachment
     * @return int
     */
    public static function getAttachmentId($url)
    {
        $attachment_id = 0;
        $dir = wp_upload_dir();
        if (false !== strpos($url, $dir['baseurl'] . '/')) { // Is URL in uploads directory?
            $file = basename($url);
            $query_args = array(
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'value' => $file,
                        'compare' => 'LIKE',
                        'key' => '_wp_attachment_metadata',
                    ),
                )
            );
            $query = new WP_Query($query_args);
            if ($query->have_posts()) {
                foreach ($query->posts as $post_id) {
                    $meta = wp_get_attachment_metadata($post_id);
                    if (!empty($meta['file']) && !empty($meta['sizes'])) {
                        $original_file = basename($meta['file']);
                        $cropped_image_files = wp_list_pluck($meta['sizes'], 'file');
                        if ($original_file === $file || in_array($file, $cropped_image_files)) {
                            $attachment_id = $post_id;
                            break;
                        }
                    }
                }
            }
        }
        return $attachment_id;
    }
}
