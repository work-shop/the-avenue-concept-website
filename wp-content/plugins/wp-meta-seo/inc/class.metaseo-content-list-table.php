<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class MetaSeoContentListTable
 * Base class for displaying a list of posts/pages in an ajaxified HTML table.
 */
class MetaSeoContentListTable extends WP_List_Table
{
    /**
     * @var
     */
    public $post_types;

    /**
     * MetaSeoContentListTable constructor.
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'metaseo_content',
            'plural' => 'metaseo_contents',
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

            <input type="hidden" name="page" value="metaseo_content_meta"/>
            <input type="hidden" name="page" value="metaseo_content_meta"/>
            <?php if (!empty($_REQUEST['post_status'])) : ?>
                <input type="hidden" name="post_status" value="<?php echo esc_attr($_REQUEST['post_status']); ?>"/>
            <?php endif ?>

            <?php $this->extra_tablenav($which); ?>

            <div style="float:right;margin-left:8px;">
                <label>
                    <input type="number" required min="1" value="<?php echo $this->_pagination_args['per_page'] ?>"
                           maxlength="3" name="metaseo_posts_per_page" class="metaseo_imgs_per_page screen-per-page"
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
        $tag = "<a href='admin.php?page=metaseo_content_meta'$class>";
        $tag .= sprintf(
            _nx(
                'All <span class="count">(%s)</span>',
                'All <span class="count">(%s)</span>',
                $total_posts,
                'posts'
            ),
            number_format_i18n($total_posts)
        );
        $tag .= "</a>";
        $status_links['all'] = $tag;

        foreach (get_post_stati(array('show_in_admin_all_list' => true), 'objects') as $status) {
            $status_name = $status->name;
            $total = $wpdb->get_var(
                "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status IN ('$status_name') AND post_type IN ($post_types)"
            );

            if ($total == 0) {
                continue;
            }

            if (isset($_REQUEST['post_status']) && $status_name == $_REQUEST['post_status']) {
                $class = ' class="current"';
            } else {
                $class = '';
            }

            $status_links[$status_name]
                = "<a href='admin.php?page=metaseo_content_meta&amp;post_status=$status_name'$class>";
            $status_links[$status_name] .= sprintf(
                translate_nooped_plural(
                    $status->label_count,
                    $total
                ),
                number_format_i18n($total)
            );
            $status_links[$status_name] .= "</a>";
        }
        $trashed_posts = $wpdb->get_var(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status IN ('trash') AND post_type IN ($post_types)"
        );
        $class = (isset($_REQUEST['post_status']) && 'trash' == $_REQUEST['post_status']) ? 'class="current"' : '';
        $status_links['trash'] = "<a href='admin.php?page=metaseo_content_meta&amp;post_status=trash'$class>";
        $status_links['trash'] .= sprintf(
            _nx(
                'Trash <span class="count">(%s)</span>',
                'Trash <span class="count">(%s)</span>',
                $trashed_posts,
                'posts'
            ),
            number_format_i18n($trashed_posts)
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
        $selected = !empty($_REQUEST['post_type_filter']) ? $_REQUEST['post_type_filter'] : -1;

        $options = '<option value="-1">Show All Post Types</option>';

        foreach ($this->post_types as $post_type) {
            $obj = get_post_type_object($post_type->post_type);
            $options .= sprintf(
                '<option value="%2$s" %3$s>%1$s</option>',
                $obj->labels->name,
                $post_type->post_type,
                selected($selected, $post_type->post_type, false)
            );
        }

        $sl_bulk = '<select name="mbulk_copy" class="mbulk_copy">
<option value="0">' . __('Bulk copy', 'wp-meta-seo') . '</option>
<option value="all">' . __('All Posts', 'wp-meta-seo') . '</option>
<option value="bulk-copy-metatitle">' . __('Selected posts', 'wp-meta-seo') . '</option>
</select>';
        $btn_bulk = '<input type="button" name="do_copy" id="post_do_copy"
         class="wpmsbtn wpmsbtn_small btn_do_copy post_do_copy"
          value="' . __('Content title as meta title', 'wp-meta-seo') . '"><span class="spinner"></span>';

        $selected_duplicate = !empty($_REQUEST['wpms_duplicate_meta']) ? $_REQUEST['wpms_duplicate_meta'] : 'none';
        $options_dups = array(
            'none' => __('All meta information', 'wp-meta-seo'),
            'duplicate_title' => __('Duplicated meta titles', 'wp-meta-seo'),
            'duplicate_desc' => __('Duplicated meta descriptions', 'wp-meta-seo')
        );
        $sl_duplicate = '<select name="wpms_duplicate_meta" class="wpms_duplicate_meta">';
        foreach ($options_dups as $key => $label) {
            if ($selected_duplicate == $key) {
                $sl_duplicate .= '<option selected value="' . $key . '">' . $label . '</option>';
            } else {
                $sl_duplicate .= '<option value="' . $key . '">' . $label . '</option>';
            }
        }
        $sl_duplicate .= '</select>';

        echo sprintf('<select name="post_type_filter" class="metaseo-filter">%1$s</select>', $options);
        echo $sl_duplicate;
        if (is_plugin_active(WPMSEO_ADDON_FILENAME)
            && (is_plugin_active('sitepress-multilingual-cms/sitepress.php')
                || is_plugin_active('polylang/polylang.php'))) {
            $lang = !empty($_REQUEST['wpms_lang_list']) ? $_REQUEST['wpms_lang_list'] : '0';
            $sl_lang = apply_filters('wpms_get_languagesList', '', $lang);
            echo $sl_lang;
        }
        echo '<input type="submit" name="do_filter" id="post-query-submit"
         class="wpmsbtn wpmsbtn_small wpmsbtn_secondary" value="' . __('Filter', 'wp-meta-seo') . '">';
        echo $sl_bulk . $btn_bulk;
        echo "</div>";
    }

    /**
     * Get a list of columns. The format is:
     * 'internal-name' => 'Title'
     * @return array
     */
    public function get_columns()
    {
        $preview = __(" This is a rendering of what this post might look
         like in Google's search results.", 'wp-meta-seo');
        $info = sprintf('<a class="info-content"><img src=' . WPMETASEO_PLUGIN_URL . 'img/info.png>'
            . '<p class="tooltip-metacontent">'
            . $preview
            . '</p></a>');

        $columns = array(
            'cb' => '<input id="cb-select-all-1" type="checkbox" style="margin:0">',
            'col_id' => __('', 'wp-meta-seo'),
            'col_title' => __('Title', 'wp-meta-seo'),
            'col_snippet' => sprintf(__('Snippet Preview %s', 'wp-meta-seo'), $info),
            'col_meta_title' => __('Meta Title', 'wp-meta-seo'),
        );

        $settings = get_option('_metaseo_settings');
        if (isset($settings['metaseo_showkeywords']) && $settings['metaseo_showkeywords'] == 1) {
            $columns['col_meta_keywords'] = __('Meta Keywords', 'wp-meta-seo');
        }
        $columns['col_meta_desc'] = __('Meta Description', 'wp-meta-seo');
        $settings = get_option('_metaseo_settings');
        if (!empty($settings['metaseo_follow'])) {
            $columns['col_follow'] = __('Follow', 'wp-meta-seo');
        }

        if (!empty($settings['metaseo_index'])) {
            $columns['col_index'] = __('Index', 'wp-meta-seo');
        }

        return $columns;
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
            'col_title' => array('post_title', true),
            'col_meta_title' => array('metatitle', true),
            'col_meta_desc' => array('metadesc', true)
        );
    }

    /**
     * Get a list of all registered post type objects.
     */
    public function getPostType()
    {
        global $wpdb;
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        $post_types = "'" . implode("', '", esc_sql($post_types)) . "'";

        $states = get_post_stati(array('show_in_admin_all_list' => true));
        $states['trash'] = 'trash';
        $all_states = "'" . implode("', '", esc_sql($states)) . "'";

        $query = "SELECT DISTINCT post_type FROM $wpdb->posts WHERE post_status IN ($all_states)
 AND post_type IN ($post_types) ORDER BY 'post_type' ASC";
        $post_types = $wpdb->get_results($query);
        return $post_types;
    }

    /**
     * Prepares the list of items for displaying.
     * @uses WP_List_Table::set_pagination_args()
     */
    public function prepare_items()
    {
        global $wpdb;
        $this->post_types = $this->getPostType();
        $where = array();
        $post_type = isset($_REQUEST['post_type_filter']) ? $_REQUEST['post_type_filter'] : "";
        if ($post_type == "-1") {
            $post_type = "";
        }

        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        if (!empty($post_type) && !in_array($post_type, $post_types)) {
            $post_type = '\'post\'';
        } elseif (empty($post_type)) {
            $post_type = "'" . implode("', '", esc_sql($post_types)) . "'";
        } else {
            $post_type = "'" . $post_type . "'";
        }

        $where[] = "post_type IN ($post_type)";

        $states = get_post_stati(array('show_in_admin_all_list' => true));
        $all_states = "'" . implode("', '", $states) . "'";

        if (empty($_REQUEST['post_status'])) {
            $where[] = "post_status IN ($all_states)";
        } else {
            $requested_state = $_REQUEST['post_status'];
            if (in_array($requested_state, $states)) {
                $where[] = "post_status IN ('$requested_state')";
            } else {
                $where[] = "post_status IN ($all_states)";
            }
        }

        $keyword = !empty($_GET["s"]) ? $_GET["s"] : '';
        if (isset($keyword) && $keyword != '') {
            $where[] = '(post_title LIKE "%' . $keyword . '%"
             OR mt.meta_value LIKE "%' . $keyword . '%" OR md.meta_value LIKE "%' . $keyword . '%")';
        }

        //Order By block
        $orderby = !empty($_GET["orderby"]) ? ($_GET["orderby"]) : 'post_title';
        $order = !empty($_GET["order"]) ? ($_GET["order"]) : 'asc';

        $sortable = $this->get_sortable_columns();
        $orderby_array = array($orderby, true);
        if (in_array($orderby_array, $sortable)) {
            $orderStr = $orderby;
        } else {
            $orderStr = 'post_title';
        }

        if ($order == "asc") {
            $orderStr .= " ASC";
        } else {
            $orderStr .= " DESC";
        }

        if (!empty($orderby) & !empty($order)) {
            $orderStr = $wpdb->prepare(' ORDER BY %s ', $orderStr);
            $orderStr = str_replace("'", "", $orderStr);
        }

        if (isset($_GET['wpms_duplicate_meta']) && $_GET['wpms_duplicate_meta'] != 'none') {
            if ($_GET['wpms_duplicate_meta'] == 'duplicate_title') {
                $where[] = "mt.meta_key = '_metaseo_metatitle'";
                $where[] = "mt.meta_value IN (SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key='_metaseo_metatitle' AND meta_value != '' GROUP BY meta_value HAVING COUNT(*) >= 2)";

            } elseif ($_GET['wpms_duplicate_meta'] == 'duplicate_desc') {
                $where[] = "md.meta_key = '_metaseo_metadesc'";
                $where[] = "md.meta_value IN (SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key='_metaseo_metadesc' AND meta_value != '' GROUP BY meta_value HAVING COUNT(*) >= 2)";
            }
        }

        $query = "SELECT COUNT(ID) "
            . " FROM $wpdb->posts "
            . " LEFT JOIN (SELECT * FROM $wpdb->postmeta
             WHERE meta_key = '_metaseo_metatitle') mt ON mt.post_id = $wpdb->posts.ID "
            . " LEFT JOIN (SELECT * FROM $wpdb->postmeta
             WHERE meta_key = '_metaseo_metadesc') md ON md.post_id = $wpdb->posts.ID "
            . " LEFT JOIN (SELECT * FROM $wpdb->postmeta
             WHERE meta_key = '_metaseo_metakeywords') mk ON mk.post_id = $wpdb->posts.ID "
            . " WHERE " . implode(' AND ', $where);

        $total_items = $wpdb->get_var($query);
        $query = "SELECT DISTINCT ID, post_title, post_name, post_type,  post_status,
         mt.meta_value AS metatitle, md.meta_value AS metadesc ,mk.meta_value AS metakeywords "
            . " FROM $wpdb->posts"
            . " LEFT JOIN (SELECT * FROM $wpdb->postmeta WHERE meta_key = '_metaseo_metatitle')
             mt ON mt.post_id = $wpdb->posts.ID "
            . " LEFT JOIN (SELECT * FROM $wpdb->postmeta WHERE meta_key = '_metaseo_metadesc')
             md ON md.post_id = $wpdb->posts.ID "
            . " LEFT JOIN (SELECT * FROM $wpdb->postmeta WHERE meta_key = '_metaseo_metakeywords')
             mk ON mk.post_id = $wpdb->posts.ID ";

        // query post by lang with polylang plugin
        if (is_plugin_active(WPMSEO_ADDON_FILENAME) && is_plugin_active('polylang/polylang.php')) {
            if (isset($_GET['wpms_lang_list']) && $_GET['wpms_lang_list'] != '0') {
                $query .= " INNER JOIN (SELECT * FROM $wpdb->term_relationships as ml
                 INNER JOIN (SELECT * FROM $wpdb->terms WHERE slug='" . $_GET['wpms_lang_list'] . "')
                  mp ON mp.term_id = ml.term_taxonomy_id) ml ON ml.object_id = $wpdb->posts.ID ";
            }
        }

        // query post by lang with WPML plugin
        if (is_plugin_active(WPMSEO_ADDON_FILENAME) && is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
            if (isset($_GET['wpms_lang_list']) && $_GET['wpms_lang_list'] != '0') {
                $query .= " INNER JOIN (SELECT * FROM " . $wpdb->prefix . "icl_translations
                 WHERE element_type LIKE 'post_%' AND language_code='" . $_GET['wpms_lang_list'] . "') t
                  ON t.element_id = $wpdb->posts.ID ";
            }
        }

        $query .= " WHERE " . implode(' AND ', $where) . $orderStr;

        if (!empty($_REQUEST['metaseo_posts_per_page'])) {
            $_per_page = intval($_REQUEST['metaseo_posts_per_page']);
        } else {
            $_per_page = 0;
        }

        $per_page = get_user_option('metaseo_posts_per_page');
        if ($per_page !== false) {
            if ($_per_page && $_per_page !== $per_page) {
                $per_page = $_per_page;
                update_user_option(get_current_user_id(), 'metaseo_posts_per_page', $per_page);
            }
        } else {
            if ($_per_page > 0) {
                $per_page = $_per_page;
            } else {
                $per_page = 10;
            }
            add_user_meta(get_current_user_id(), 'metaseo_posts_per_page', $per_page);
        }

        $paged = !empty($_GET["paged"]) ? $_GET["paged"] : '';
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
    }

    /**
     * Generate the table rows
     */
    public function display_rows()
    {
        $records = $this->items;
        $i = 0;
        $alternate = "";
        $url = preg_replace('/(http|https):\/\/[w]*[.]?/', '', network_site_url('/'));

        list($columns, $hidden) = $this->get_column_info();

        if (!empty($records)) {
            foreach ($records as $rec) {
                $alternate = 'alternate' == $alternate ? '' : 'alternate';
                $i++;
                $classes = $alternate;
                $rec->link = $url;

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
                            echo '<th scope="row" class="check-column">';
                            echo '<input id="cb-select-' . $rec->ID . '"
                             class="metaseo_post" type="checkbox" name="post[]" value="' . $rec->ID . '">';
                            echo '</th>';

                            break;

                        case 'col_id':
                            echo '<td class="col_id" >';
                            echo $i;
                            echo '</td>';

                            break;

                        case 'col_title':
                            $post_title = stripslashes($rec->post_title);
                            if ($post_title == '') {
                                $post_title = __('(no title)', 'wp-meta-seo');
                            }

                            echo sprintf(
                                '<td %2$s><div class="action-wrapper">
<strong id="post-title-' . $rec->ID . '">%1$s</strong>',
                                $post_title,
                                $attributes
                            );

                            $post_type_object = get_post_type_object($rec->post_type);
                            $can_edit_post = current_user_can($post_type_object->cap->edit_post, $rec->ID);

                            $actions = array();

                            if ($can_edit_post && 'trash' != $rec->post_status) {
                                $actions['edit'] = '<a href="' . get_edit_post_link($rec->ID, true) . '"
                                 title="' . esc_attr(__('Edit this item', 'wp-meta-seo')) . '"
                                 >' . __('Edit', 'wp-meta-seo') . '</a>';
                            }

                            if ($post_type_object->public) {
                                if (in_array($rec->post_status, array('pending', 'draft', 'future'))) {
                                    if ($can_edit_post) {
                                        $hr = esc_url(add_query_arg('preview', 'true', get_permalink($rec->ID)));
                                        $t = esc_attr(
                                            sprintf(
                                                __('Preview &#8220;%s&#8221;', 'wp-meta-seo'),
                                                $rec->post_title
                                            )
                                        );
                                        $actions['view'] = '<a href="' . $hr . '" title="' . $t . '" rel="permalink"
                                        >' . __('Preview', 'wp-meta-seo') . '</a>';
                                    }
                                } elseif ('trash' != $rec->post_status) {
                                    $t = esc_attr(
                                        sprintf(
                                            __('View &#8220;%s&#8221;', 'wp-meta-seo'),
                                            $rec->post_title
                                        )
                                    );
                                    $actions['view'] = '<a target="_blank" href="' . get_permalink($rec->ID) . '"
                                     title="' . $t . '" rel="permalink">' . __('View', 'wp-meta-seo') . '</a>';
                                }
                            }

                            echo $this->row_actions($actions);
                            echo '</div></td>';

                            break;

                        case 'col_snippet':
                            echo '<td><div class="snippet-wrapper">';
                            echo '<div class="snippet">
                                       <a id="snippet_title' . $rec->ID . '" class="snippet_metatitle">
                                       ' . (!empty($rec->metatitle) ? $rec->metatitle : $rec->post_title) . '</a>';

                            echo '<span class="snippet_metalink" id="snippet_metalink_' . $rec->ID . '">
                            ' . $rec->link . '</span>';

                            echo '<p id="snippet_desc' . $rec->ID . '" class="snippet_metades">
                            ' . $rec->metadesc . '</p></div>';
                            echo '<img class="wpms_loader' . $rec->ID . ' wpms_content_loader"
                             src=' . WPMETASEO_PLUGIN_URL . 'img/update_loading.gif>';
                            echo '<span id="savedInfo' . $rec->ID . '"
 style="position: relative; display: block;float:right"
 class="saved-info metaseo-msg-success"><span style="position:absolute; float:right" class="spinner"></span></span>';
                            echo '</div></td>';
                            break;
                        case 'col_page_slug':
                            $permalink = get_permalink($rec->ID);
                            $display_slug = str_replace(get_bloginfo('url'), '', $permalink);
                            echo sprintf(
                                '<td %2$s><a href="%3$s" target="_blank">%1$s</a></td>',
                                stripslashes($display_slug),
                                $attributes,
                                $permalink
                            );
                            break;

                        case 'col_meta_title':
                            $input = sprintf(
                                '</br><textarea class="large-text metaseo-metatitle"
 rows="3" id="%1$s" name="%2$s" autocomplete="off">%3$s</textarea>',
                                'metaseo-metatitle-' . $rec->ID,
                                'metatitle[' . $rec->ID . ']',
                                ($rec->metatitle) ? $rec->metatitle : ''
                            );
                            $input .= sprintf(
                                '<div class="title-len" id="%1$s"></div>',
                                'metaseo-metatitle-len' . $rec->ID
                            );
                            echo sprintf('<td %2$s>%1$s</td>', $input, $attributes);
                            break;

                        case 'col_meta_keywords':
                            $input = sprintf(
                                '</br><textarea class="large-text metaseo-metakeywords"
 rows="3" id="%1$s" name="%2$s" autocomplete="off">%3$s</textarea>',
                                'metaseo-metakeywords-' . $rec->ID,
                                'metakeywords[' . $rec->ID . ']',
                                ($rec->metakeywords) ? $rec->metakeywords : ''
                            );
                            $input .= sprintf(
                                '<div class="keywords-len" id="%1$s"></div>',
                                'metaseo-metakeywords-len' . $rec->ID
                            );
                            echo sprintf('<td %2$s>%1$s</td>', $input, $attributes);
                            break;

                        case 'col_meta_desc':
                            $input = sprintf(
                                '</br><textarea class="large-text metaseo-metadesc"
 rows="3" id="%1$s" name="%2$s" autocomplete="off">%3$s</textarea>',
                                'metaseo-metadesc-' . $rec->ID,
                                ' metades[' . $rec->ID . ']',
                                ($rec->metadesc) ? $rec->metadesc : ''
                            );
                            $input .= sprintf(
                                '<div class="desc-len" id="%1$s"></div>',
                                'metaseo-metadesc-len' . $rec->ID
                            );
                            echo sprintf('<td %2$s>%1$s</td>', $input, $attributes);
                            break;

                        case 'col_index':
                            $page_index = get_post_meta($rec->ID, '_metaseo_metaindex', true);
                            if (isset($page_index) && $page_index == 'noindex') {
                                $input = '<input class="metaseo_post_index"
                                 name="index[]" type="checkbox" value="' . $rec->ID . '">';
                            } else {
                                $input = '<input checked class="metaseo_post_index"
                                 name="index[]" type="checkbox" value="' . $rec->ID . '">';
                            }
                            echo sprintf('<td %2$s>%1$s</td>', $input, $attributes);
                            break;

                        case 'col_follow':
                            $page_follow = get_post_meta($rec->ID, '_metaseo_metafollow', true);
                            if (isset($page_follow) && $page_follow == 'nofollow') {
                                $input = '<input class="metaseo_post_follow"
                                 name="follow[]" type="checkbox" value="' . $rec->ID . '">';
                            } else {
                                $input = '<input checked class="metaseo_post_follow"
                                 name="follow[]" type="checkbox" value="' . $rec->ID . '">';
                            }
                            echo sprintf('<td %2$s>%1$s</td>', $input, $attributes);
                            break;
                    }
                }

                echo '</tr>';
            }
        }
    }

    /**
     * Retrieves a modified URL query string.
     */
    public function processAction()
    {
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $redirect = false;
        if (isset($_POST['do_filter'])) {
            $current_url = add_query_arg(array("post_type_filter" => $_POST['post_type_filter']), $current_url);
            $current_url = add_query_arg(array("wpms_duplicate_meta" => $_POST['wpms_duplicate_meta']), $current_url);
            $current_url = add_query_arg(array("wpms_lang_list" => $_POST['wpms_lang_list']), $current_url);
            $redirect = true;
        }

        if (!empty($_POST['paged'])) {
            $current_url = add_query_arg(array("paged" => intval($_POST['paged'])), $current_url);
            $redirect = true;
        }

        if (!empty($_POST['metaseo_posts_per_page'])) {
            $current_url = add_query_arg(
                array(
                    "metaseo_posts_per_page" => intval($_POST['metaseo_posts_per_page'])),
                $current_url
            );
            $redirect = true;
        }

        if (isset($_POST['s'])) {
            $current_url = add_query_arg(array("s" => urlencode($_POST['s'])), $current_url);
            $redirect = true;
        }

        if ($redirect === true) {
            wp_redirect($current_url);
            ob_end_flush();
            exit();
        }
    }

    /**
     * Get all posts that is public and contain images with a string seperated by comma
     * @return array|string
     */
    public static function getPostTypes()
    {
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        if (!empty($post_types)) {
            $post_types = "'" . implode("', '", $post_types) . "'";
        }

        return $post_types;
    }

    /**
     * import meta from other plugin
     */
    public static function importMetaData()
    {
        global $wpdb;
        $meta_metaseo_keys = array('_metaseo_metatitle', '_metaseo_metadesc');
        $key = array(
            '_aio_' => array('_aioseop_title', '_aioseop_description'),
            '_yoast_' => array('_yoast_wpseo_title', '_yoast_wpseo_metadesc')
        );

        if (!empty($_POST['plugin']) and in_array(strtolower(trim($_POST['plugin'])), array('_aio_', '_yoast_'))) {
            $plugin = strtolower(trim($_POST['plugin']));
            $metakeys = '';
            foreach ($meta_metaseo_keys as $k => $mkey) {
                $metakeys .= ' OR `meta_key` = \'' . $mkey . '\' OR `meta_key` = \'' . $key[$plugin][$k] . '\'';
            }

            $metakeys = ltrim($metakeys, ' OR ');
            $query = "SELECT `post_id` as pID, `meta_key`, `meta_value` 
					  FROM $wpdb->postmeta 
					  WHERE  $metakeys
					  ORDER BY `meta_key`";
            $posts_metas = $wpdb->get_results($query);

            if (is_array($posts_metas) && count($posts_metas) > 0) {
                $_posts_metas = array();
                foreach ($posts_metas as $postmeta) {
                    $_posts_metas[$postmeta->pID][$postmeta->meta_key] = $postmeta->meta_value;
                }
                unset($posts_metas);
                foreach ($_posts_metas as $pID => $pmeta) {
                    foreach ($meta_metaseo_keys as $k => $mkey) {
                        $mvalue = $pmeta[$mkey];
                        $msynckey = $key[$plugin][$k];
                        $msyncvalue = $pmeta[$msynckey];

                        if (is_null($mvalue) || ($mvalue == '' && $msynckey != '')) {
                            update_post_meta($pID, $mkey, $msyncvalue);
                        } elseif (is_null($msyncvalue) || ($msyncvalue == '' && $mvalue != '')) {
                            update_post_meta($pID, $msynckey, $mvalue);
                        } elseif ($mvalue != '' && $msyncvalue != '') {
                            update_post_meta($pID, $mkey, $msyncvalue);
                        }
                    }
                }

                unset($posts_metas);
            }


            $ret['success'] = true;

            update_option('_aio_import_notice_flag', 1);
            update_option('_yoast_import_notice_flag', 1);
            update_option('plugin_to_sync_with', $plugin);
        } else {
            $ret['success'] = false;
        }

        echo json_encode($ret);
        wp_die();
    }

    /**
     * Dismiss import message
     */
    public static function dismissImport()
    {
        if (!empty($_POST['plugin']) and in_array(strtolower(trim($_POST['plugin'])), array('_aio_', '_yoast_'))) {
            $plugin = strtolower(trim($_POST['plugin']));

            update_option($plugin . 'import_notice_flag', 1);
            $ret['success'] = true;
        } else {
            $ret['success'] = false;
        }

        echo json_encode($ret);
        wp_die();
    }

    /**
     * Update meta sync
     * @param int $meta_id meta id
     * @param int $object_id object id
     * @param string $meta_key meta key
     * @param string $meta_value meta value
     * @return bool|null
     */
    public static function updateMetaSync($meta_id, $object_id, $meta_key, $meta_value)
    {
        if (!self::isUpdateSync($meta_key)) {
            return null;
        }

        if (self::doUpdateMetaSync($object_id, $meta_key, $meta_value, 'update')) {
            return true;
        }

        return null;
    }

    /**
     * deletes all custom fields with the specified key
     * @param int $meta_ids meta id
     * @param int $object_id object id
     * @param string $meta_key meta key
     * @param string $meta_value meta value
     * @return bool|null
     */
    public static function deleteMetaSync($meta_ids, $object_id, $meta_key, $meta_value)
    {
        if (!self::isUpdateSync($meta_key)) {
            return null;
        }

        if (self::doUpdateMetaSync($object_id, $meta_key, $meta_value, 'delete')) {
            return true;
        }

        return null;
    }

    /**
     * Update meta sync
     * @param string $type
     * @param int $object_id object id
     * @param string $meta_key meta key
     * @param string $meta_value meta value
     * @return bool
     */
    private static function doUpdateMetaSync($object_id, $meta_key, $meta_value, $type = '')
    {
        if (!($sync = get_option('plugin_to_sync_with')) or !in_array($sync, array('_aio_', '_yoast_'))) {
            return false;
        }

        $metakeys = array(
            '_metaseo_' => array('_metaseo_metatitle', '_metaseo_metadesc'),
            '_aio_' => array('_aioseop_title', '_aioseop_description'),
            '_yoast_' => array('_yoast_wpseo_title', '_yoast_wpseo_metadesc')
        );

        $_metakeys = array();
        $_metakeys['_metaseo_'] = $metakeys['_metaseo_'];
        $_metakeys[$sync] = $metakeys[$sync];
        unset($metakeys);

        foreach ($_metakeys as $identify => $mkeys) {
            foreach ($mkeys as $k => $mkey) {
                if ($meta_key === $mkey) {
                    if ($identify === '_metaseo_') {
                        $mkeysync = $_metakeys[$sync][$k];
                    } else {
                        $mkeysync = $_metakeys['_metaseo_'][$k];
                    }

                    if ($type == 'update') {
                        update_post_meta($object_id, $mkeysync, $meta_value);
                        return true;
                    }

                    if ($type == 'delete') {
                        delete_post_meta($object_id, $mkeysync);
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check is update sync
     * @param $meta_key
     * @return bool
     */
    public static function isUpdateSync($meta_key)
    {
        $mkey_prefix = array('_metaseo_', '_yoast_', '_aio');
        foreach ($mkey_prefix as $prefix) {
            if (strpos($meta_key, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }
}
