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
     * Post type
     *
     * @var array
     */
    public $post_types;

    /**
     * MetaSeoContentListTable constructor.
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'metaseo_content',
            'plural'   => 'metaseo_contents',
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

            <input type="hidden" name="page" value="metaseo_content_meta"/>
            <input type="hidden" name="page" value="metaseo_content_meta"/>
            <?php // phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
            ?>
            <?php if (!empty($_REQUEST['post_status'])) : ?>
                <input type="hidden" name="post_status" value="<?php echo esc_attr($_REQUEST['post_status']); ?>"/>
            <?php endif ?>
            <?php // phpcs:enable
            ?>
            <?php $this->extra_tablenav($which); ?>

            <div style="float:right;margin-left:8px;">
                <label>
                    <input type="number" required
                           value="<?php echo esc_attr($this->_pagination_args['per_page']) ?>"
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
     * Extra controls to be displayed between bulk actions and pagination
     *
     * @param string $which Possition of table nav
     *
     * @return void
     */
    protected function extra_tablenav($which) // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        echo '<div class="alignleft actions">';
        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        $selected = !empty($_REQUEST['post_type_filter']) ? $_REQUEST['post_type_filter'] : - 1;

        $options = '<option value="-1">Show All Post Types</option>';

        foreach ($this->post_types as $post_type) {
            $obj     = get_post_type_object($post_type);
            $options .= sprintf(
                '<option value="%2$s" %3$s>%1$s</option>',
                esc_html($obj->labels->name),
                esc_attr($post_type),
                selected($selected, $post_type, false)
            );
        }

        $sl_bulk  = '<select name="mbulk_copy" class="mbulk_copy">
<option value="0">' . esc_html__('Bulk copy', 'wp-meta-seo') . '</option>
<option value="all">' . esc_html__('All Posts', 'wp-meta-seo') . '</option>
<option value="bulk-copy-metatitle">' . esc_html__('Selected posts', 'wp-meta-seo') . '</option>
</select>';
        $btn_bulk = '<input type="button" name="do_copy" id="post_do_copy"
         class="wpmsbtn wpmsbtn_small btn_do_copy post_do_copy"
          value="' . esc_attr__('Content title as meta title', 'wp-meta-seo') . '"><span class="spinner"></span>';

        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        $selected_duplicate = !empty($_REQUEST['wpms_duplicate_meta']) ? $_REQUEST['wpms_duplicate_meta'] : 'none';
        $options_dups       = array(
            'none'            => esc_html__('All meta information', 'wp-meta-seo'),
            'duplicate_title' => esc_html__('Duplicated meta titles', 'wp-meta-seo'),
            'duplicate_desc'  => esc_html__('Duplicated meta descriptions', 'wp-meta-seo')
        );
        $sl_duplicate       = '<select name="wpms_duplicate_meta" class="wpms_duplicate_meta">';
        foreach ($options_dups as $key => $label) {
            if ($selected_duplicate === $key) {
                $sl_duplicate .= '<option selected value="' . esc_attr($key) . '">' . esc_html($label) . '</option>';
            } else {
                $sl_duplicate .= '<option value="' . esc_attr($key) . '">' . esc_html($label) . '</option>';
            }
        }
        $sl_duplicate .= '</select>';
        // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
        echo sprintf('<select name="post_type_filter" class="metaseo-filter">%1$s</select>', $options);
        // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
        echo $sl_duplicate;
        if (is_plugin_active(WPMSEO_ADDON_FILENAME)
            && (is_plugin_active('sitepress-multilingual-cms/sitepress.php')
                || is_plugin_active('polylang/polylang.php'))) {
            // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
            $lang    = !empty($_REQUEST['wpms_lang_list']) ? $_REQUEST['wpms_lang_list'] : '0';
            $sl_lang = apply_filters('wpms_get_languagesList', '', $lang);
            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in the method MetaSeoAddonAdmin::listLanguageSelect
            echo $sl_lang;
        }

        echo '<input type="submit" name="do_filter" id="post-query-submit"
         class="wpmsbtn wpmsbtn_small wpmsbtn_secondary" value="' . esc_html__('Filter', 'wp-meta-seo') . '">';
        // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
        echo $sl_bulk . $btn_bulk;
        echo '</div>';
    }

    /**
     * Get a list of columns. The format is:
     * 'internal-name' => 'Title'
     *
     * @return array
     */
    public function get_columns() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        $preview = esc_html__(" This is a rendering of what this post might look
         like in Google's search results.", 'wp-meta-seo');
        $info    = sprintf('<a class="info-content"><img src=' . WPMETASEO_PLUGIN_URL . 'img/info.png>'
                           . '<p class="tooltip-metacontent">'
                           . $preview
                           . '</p></a>');

        $columns = array(
            'cb'             => '<input id="cb-select-all-1" type="checkbox" style="margin:0">',
            'col_id'         => '',
            'col_title'      => esc_html__('Title', 'wp-meta-seo'),
            'col_snippet'    => sprintf(esc_html__('Snippet Preview %s', 'wp-meta-seo'), $info),
            'col_meta_title' => esc_html__('Meta Title', 'wp-meta-seo'),
        );

        $settings = get_option('_metaseo_settings');
        if (isset($settings['metaseo_showkeywords']) && (int) $settings['metaseo_showkeywords'] === 1) {
            $columns['col_meta_keywords'] = esc_html__('Meta Keywords', 'wp-meta-seo');
        }
        $columns['col_meta_desc'] = esc_html__('Meta Description', 'wp-meta-seo');
        $settings                 = get_option('_metaseo_settings');
        if (!empty($settings['metaseo_follow'])) {
            $columns['col_follow'] = esc_html__('Follow', 'wp-meta-seo');
        }

        if (!empty($settings['metaseo_index'])) {
            $columns['col_index'] = esc_html__('Index', 'wp-meta-seo');
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
     *
     * @return array
     */
    protected function get_sortable_columns() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        $sortable = array(
            'col_title'      => array('post_title', true),
            'col_meta_title' => array('metatitle', true),
            'col_meta_desc'  => array('metadesc', true)
        );

        return $sortable;
    }

    /**
     * Get all posts that is public and contain images with a string seperated by comma
     *
     * @param string $unset Unset element
     *
     * @return array
     */
    public static function getPostTypes($unset = '')
    {
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        if (!empty($unset)) {
            unset($post_types[$unset]);
        }
        return $post_types;
    }

    /**
     * Prepares the list of items for displaying.
     *
     * @return void
     */
    public function prepare_items() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        global $wpdb;
        $this->post_types = $this->getPostTypes();
        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        $post_type = isset($_REQUEST['post_type_filter']) ? $_REQUEST['post_type_filter'] : '';
        if ($post_type === '-1') {
            $post_type = '';
        }

        if (!empty($post_type) && !in_array($post_type, $this->post_types)) {
            $post_type = 'post';
        } elseif (empty($post_type)) {
            $post_type = $this->getPostTypes();
            foreach ($post_type as &$pt) {
                $pt = esc_sql($pt);
            }
            $post_type = implode("', '", $post_type);
        }

        $states = get_post_stati(array('show_in_admin_all_list' => true));
        foreach ($states as &$state) {
            $state = esc_sql($state);
        }

        $all_states = implode("', '", $states);
        $where      = array();
        $where[]    = 'post_type IN (\'' . $post_type . '\')';
        $where[]    = 'post_status IN (\'' . $all_states . '\')';
        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        $keyword = !empty($_GET['s']) ? $_GET['s'] : '';
        if (isset($keyword) && $keyword !== '') {
            $where[] = $wpdb->prepare('(post_title LIKE %s OR mt.meta_value LIKE %s OR md.meta_value LIKE %s)', array(
                '%' . $keyword . '%',
                '%' . $keyword . '%',
                '%' . $keyword . '%'
            ));
        }

        //Order By block
        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        $orderby = !empty($_GET['orderby']) ? ($_GET['orderby']) : 'post_title';
        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        $order = !empty($_GET['order']) ? ($_GET['order']) : 'asc';

        $sortable      = $this->get_sortable_columns();
        $orderby_array = array($orderby, true);
        if (in_array($orderby_array, $sortable)) {
            $orderStr = $orderby;
        } else {
            $orderStr = 'post_title';
        }

        if ($order === 'asc') {
            $orderStr .= ' ASC';
        } else {
            $orderStr .= ' DESC';
        }

        if (!empty($orderby) & !empty($order)) {
            $orderStr = ' ORDER BY ' . esc_sql($orderStr) . ' ';
        }

        // phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        if (isset($_GET['wpms_duplicate_meta']) && $_GET['wpms_duplicate_meta'] !== 'none') {
            if ($_GET['wpms_duplicate_meta'] === 'duplicate_title') {
                $where[] = 'mt.meta_key = "_metaseo_metatitle" AND mt.meta_value IN (SELECT DISTINCT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key="_metaseo_metatitle" AND meta_value != "" GROUP BY meta_value HAVING COUNT(*) >= 2)';
            } elseif ($_GET['wpms_duplicate_meta'] === 'duplicate_desc') {
                $where[] = 'md.meta_key = "_metaseo_metadesc" AND md.meta_value IN (SELECT DISTINCT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key="_metaseo_metadesc" AND meta_value != "" GROUP BY meta_value HAVING COUNT(*) >= 2)';
            }
        }
        // phpcs:enable
        $query = 'SELECT COUNT(ID) '
                 . ' FROM ' . $wpdb->posts . ' as p'
                 . ' LEFT JOIN (SELECT * FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_metaseo_metatitle") mt ON mt.post_id = p.ID '
                 . ' LEFT JOIN (SELECT * FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_metaseo_metadesc") md ON md.post_id = p.ID '
                 . ' LEFT JOIN (SELECT * FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_metaseo_metakeywords") mk ON mk.post_id = p.ID ';
        $query .= ' WHERE ' . implode(' AND ', $where);

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $total_items = $wpdb->get_var($query);

        $query = 'SELECT DISTINCT ID, post_title, post_name, post_type,  post_status, mt.meta_value AS metatitle, md.meta_value AS metadesc ,mk.meta_value AS metakeywords '
                 . ' FROM ' . $wpdb->posts . ' as p'
                 . ' LEFT JOIN (SELECT * FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_metaseo_metatitle") mt ON mt.post_id = p.ID '
                 . ' LEFT JOIN (SELECT * FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_metaseo_metadesc") md ON md.post_id = p.ID '
                 . ' LEFT JOIN (SELECT * FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_metaseo_metakeywords") mk ON mk.post_id = p.ID';
        // query post by lang with polylang plugin
        if (is_plugin_active(WPMSEO_ADDON_FILENAME) && is_plugin_active('polylang/polylang.php')) {
            // phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
            if (isset($_GET['wpms_lang_list']) && $_GET['wpms_lang_list'] !== '0') {
                $query .= $wpdb->prepare(' INNER JOIN (SELECT * FROM ' . $wpdb->term_relationships . ' as ml
                 INNER JOIN (SELECT * FROM ' . $wpdb->terms . ' WHERE slug = %s)
                  mp ON mp.term_id = ml.term_taxonomy_id) ml ON ml.object_id = p.ID ', array($_GET['wpms_lang_list']));
            }
            // phpcs:enable
        }

        // query post by lang with WPML plugin
        if (is_plugin_active(WPMSEO_ADDON_FILENAME) && is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
            // phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
            if (isset($_GET['wpms_lang_list']) && $_GET['wpms_lang_list'] !== '0') {
                $query .= $wpdb->prepare(' INNER JOIN (SELECT * FROM ' . $wpdb->prefix . 'icl_translations
                 WHERE element_type LIKE %s AND language_code = %s) t
                  ON t.element_id = p.ID ', array('post_%', $_GET['wpms_lang_list']));
            }
            // phpcs:enable
        }

        $query .= ' WHERE ' . implode(' AND ', $where) . $orderStr;

        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
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

        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        $paged = !empty($_GET['paged']) ? $_GET['paged'] : '';
        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }

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

        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $this->items = $wpdb->get_results($query);
    }

    /**
     * Generate the table rows
     *
     * @return void
     */
    public function display_rows() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- extends from WP_List_Table class
    {
        $records   = $this->items;
        $i         = 0;
        $alternate = '';
        $url       = preg_replace('/(http|https):\/\/[w]*[.]?/', '', network_site_url('/'));

        list($columns, $hidden) = $this->get_column_info();

        if (!empty($records)) {
            foreach ($records as $rec) {
                $alternate = 'alternate' === $alternate ? '' : 'alternate';
                $i ++;
                $classes   = $alternate;
                $rec->link = $url;

                echo '<tr id="' . esc_attr('record_' . $rec->ID) . '" class="' . esc_attr($classes) . '" >';

                foreach ($columns as $column_name => $column_display_name) {
                    $class = sprintf('class="%1$s column-%1$s"', esc_attr($column_name));
                    $style = '';

                    if (in_array($column_name, $hidden)) {
                        $style = ' style="display:none;"';
                    }

                    $attributes = $class . $style;

                    switch ($column_name) {
                        case 'cb':
                            echo '<th scope="row" class="check-column">';
                            echo '<input id="' . esc_attr('cb-select-' . $rec->ID) . '"
                             class="metaseo_post" type="checkbox" name="post[]" value="' . esc_attr($rec->ID) . '">';
                            echo '</th>';

                            break;

                        case 'col_id':
                            echo '<td class="col_id" >';
                            echo esc_html($i);
                            echo '</td>';

                            break;

                        case 'col_title':
                            $post_title = stripslashes($rec->post_title);
                            if ($post_title === '') {
                                $post_title = esc_html__('(no title)', 'wp-meta-seo');
                            }

                            echo sprintf(
                                '<td %2$s><div class="action-wrapper">
<strong id="' . esc_attr('post-title-' . $rec->ID) . '">%1$s</strong>',
                                esc_html($post_title),
                                $attributes // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
                            );

                            $post_type_object = get_post_type_object($rec->post_type);
                            $can_edit_post    = current_user_can($post_type_object->cap->edit_post, $rec->ID);

                            $actions = array();

                            if ($can_edit_post && 'trash' !== $rec->post_status) {
                                $actions['edit'] = '<a href="' . esc_url(get_edit_post_link($rec->ID, true)) . '"
                                 title="' . esc_attr__('Edit this item', 'wp-meta-seo') . '"
                                 >' . esc_html__('Edit', 'wp-meta-seo') . '</a>';
                            }

                            if ($post_type_object->public) {
                                if (in_array($rec->post_status, array('pending', 'draft', 'future'))) {
                                    if ($can_edit_post) {
                                        $hr              = esc_url(add_query_arg('preview', 'true', get_permalink($rec->ID)));
                                        $t               = esc_attr(
                                            sprintf(
                                                esc_html__('Preview &#8220;%s&#8221;', 'wp-meta-seo'),
                                                $rec->post_title
                                            )
                                        );
                                        $actions['view'] = '<a href="' . esc_url($hr) . '" title="' . esc_html($t) . '" rel="permalink"
                                        >' . esc_html__('Preview', 'wp-meta-seo') . '</a>';
                                    }
                                } elseif ('trash' !== $rec->post_status) {
                                    $t               = esc_attr(
                                        sprintf(
                                            esc_html__('View &#8220;%s&#8221;', 'wp-meta-seo'),
                                            $rec->post_title
                                        )
                                    );
                                    $actions['view'] = '<a target="_blank" href="' . esc_url(get_permalink($rec->ID)) . '"
                                     title="' . esc_attr($t) . '" rel="permalink">' . esc_html__('View', 'wp-meta-seo') . '</a>';
                                }
                            }

                            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
                            echo $this->row_actions($actions);
                            echo '</div></td>';

                            break;

                        case 'col_snippet':
                            echo '<td><div class="snippet-wrapper">';
                            echo '<div class="snippet">
                                       <a id="' . esc_attr('snippet_title' . $rec->ID) . '" class="snippet_metatitle">
                                       ' . (!empty($rec->metatitle) ? esc_html($rec->metatitle) : esc_html($rec->post_title)) . '</a>';

                            echo '<span class="snippet_metalink" id="' . esc_attr('snippet_metalink_' . $rec->ID) . '">
                            ' . esc_html($rec->link) . '</span>';

                            echo '<p id="' . esc_attr('snippet_desc' . $rec->ID) . '" class="snippet_metades">
                            ' . esc_html($rec->metadesc) . '</p></div>';
                            echo '<img class="' . esc_attr('wpms_loader' . $rec->ID . ' wpms_content_loader') . '"
                             src=' . esc_url(WPMETASEO_PLUGIN_URL) . 'img/update_loading.gif>';
                            echo '<span id="' . esc_attr('savedInfo' . $rec->ID) . '"
 style="position: relative; display: block;float:right"
 class="saved-info metaseo-msg-success"><span style="position:absolute; float:right" class="spinner"></span></span>';
                            echo '</div></td>';
                            break;

                        case 'col_meta_title':
                            $input = sprintf(
                                '</br><textarea class="large-text metaseo-metatitle"
 rows="3" id="%1$s" name="%2$s" autocomplete="off">%3$s</textarea>',
                                esc_attr('metaseo-metatitle-' . $rec->ID),
                                esc_attr('metatitle[' . $rec->ID . ']'),
                                ($rec->metatitle) ? esc_textarea($rec->metatitle) : ''
                            );
                            $input .= sprintf(
                                '<div class="title-len" id="%1$s"></div>',
                                esc_attr('metaseo-metatitle-len' . $rec->ID)
                            );
                            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
                            echo sprintf('<td %2$s>%1$s</td>', $input, $attributes);
                            break;

                        case 'col_meta_keywords':
                            $input = sprintf(
                                '</br><textarea class="large-text metaseo-metakeywords"
 rows="3" id="%1$s" name="%2$s" autocomplete="off">%3$s</textarea>',
                                esc_attr('metaseo-metakeywords-' . $rec->ID),
                                esc_attr('metakeywords[' . $rec->ID . ']'),
                                ($rec->metakeywords) ? esc_textarea($rec->metakeywords) : ''
                            );
                            $input .= sprintf(
                                '<div class="keywords-len" id="%1$s"></div>',
                                esc_attr('metaseo-metakeywords-len' . $rec->ID)
                            );
                            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
                            echo sprintf('<td %2$s>%1$s</td>', $input, $attributes);
                            break;

                        case 'col_meta_desc':
                            $input = sprintf(
                                '</br><textarea class="large-text metaseo-metadesc"
 rows="3" id="%1$s" name="%2$s" autocomplete="off">%3$s</textarea>',
                                esc_attr('metaseo-metadesc-' . $rec->ID),
                                esc_attr(' metades[' . $rec->ID . ']'),
                                ($rec->metadesc) ? esc_textarea($rec->metadesc) : ''
                            );
                            $input .= sprintf(
                                '<div class="desc-len" id="%1$s"></div>',
                                esc_attr('metaseo-metadesc-len' . $rec->ID)
                            );
                            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
                            echo sprintf('<td %2$s>%1$s</td>', $input, $attributes);
                            break;

                        case 'col_index':
                            $page_index = get_post_meta($rec->ID, '_metaseo_metaindex', true);
                            if (isset($page_index) && $page_index === 'noindex') {
                                $input = '<input class="metaseo_post_index"
                                 name="index[]" type="checkbox" value="' . esc_attr($rec->ID) . '">';
                            } else {
                                $input = '<input checked class="metaseo_post_index"
                                 name="index[]" type="checkbox" value="' . esc_attr($rec->ID) . '">';
                            }
                            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
                            echo sprintf('<td %2$s>%1$s</td>', $input, $attributes);
                            break;

                        case 'col_follow':
                            $page_follow = get_post_meta($rec->ID, '_metaseo_metafollow', true);
                            if (isset($page_follow) && $page_follow === 'nofollow') {
                                $input = '<input class="metaseo_post_follow"
                                 name="follow[]" type="checkbox" value="' . esc_attr($rec->ID) . '">';
                            } else {
                                $input = '<input checked class="metaseo_post_follow"
                                 name="follow[]" type="checkbox" value="' . esc_attr($rec->ID) . '">';
                            }
                            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in previous line (same function)
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
     *
     * @return void
     */
    public function processAction()
    {
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $redirect    = false;
        // phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
        if (isset($_POST['do_filter'])) {
            if (isset($_POST['post_type_filter'])) {
                $current_url = add_query_arg(array('post_type_filter' => $_POST['post_type_filter']), $current_url);
            }

            if (isset($_POST['wpms_duplicate_meta'])) {
                $current_url = add_query_arg(array('wpms_duplicate_meta' => $_POST['wpms_duplicate_meta']), $current_url);
            }

            if (isset($_POST['wpms_lang_list'])) {
                $current_url = add_query_arg(array('wpms_lang_list' => $_POST['wpms_lang_list']), $current_url);
            }

            $redirect = true;
        }

        if (!empty($_POST['paged'])) {
            $current_url = add_query_arg(array('paged' => intval($_POST['paged'])), $current_url);
            $redirect    = true;
        }

        if (!empty($_POST['metaseo_posts_per_page'])) {
            $current_url = add_query_arg(
                array(
                    'metaseo_posts_per_page' => intval($_POST['metaseo_posts_per_page'])
                ),
                $current_url
            );
            $redirect    = true;
        }

        if (isset($_POST['s'])) {
            $current_url = add_query_arg(array('s' => urlencode($_POST['s'])), $current_url);
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
     * Import meta from other plugin
     *
     * @return void
     */
    public static function importMetaData()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        global $wpdb;
        $meta_metaseo_keys = array('_metaseo_metatitle', '_metaseo_metadesc');
        $key               = array(
            '_aio_'   => array('_aioseop_title', '_aioseop_description'),
            '_yoast_' => array('_yoast_wpseo_title', '_yoast_wpseo_metadesc')
        );

        if (!empty($_POST['plugin']) && in_array(strtolower(trim($_POST['plugin'])), array('_aio_', '_yoast_'))) {
            $plugin   = strtolower(trim($_POST['plugin']));
            $metakeys = '';
            foreach ($meta_metaseo_keys as $k => $mkey) {
                $metakeys .= $wpdb->prepare(' OR meta_key = %s OR meta_key = %s', array($mkey, $key[$plugin][$k]));
            }

            $metakeys = ltrim($metakeys, ' OR ');
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
            $posts_metas = $wpdb->get_results('SELECT post_id as pID, meta_key, meta_value FROM ' . $wpdb->postmeta . ' WHERE  ' . $metakeys . ' ORDER BY meta_key');

            if (is_array($posts_metas) && count($posts_metas) > 0) {
                $_posts_metas = array();
                foreach ($posts_metas as $postmeta) {
                    $_posts_metas[$postmeta->pID][$postmeta->meta_key] = $postmeta->meta_value;
                }
                unset($posts_metas);
                foreach ($_posts_metas as $pID => $pmeta) {
                    foreach ($meta_metaseo_keys as $k => $mkey) {
                        $mvalue     = $pmeta[$mkey];
                        $msynckey   = $key[$plugin][$k];
                        $msyncvalue = $pmeta[$msynckey];

                        if (is_null($mvalue) || ($mvalue === '' && $msynckey !== '')) {
                            update_post_meta($pID, $mkey, $msyncvalue);
                        } elseif (is_null($msyncvalue) || ($msyncvalue === '' && $mvalue !== '')) {
                            update_post_meta($pID, $msynckey, $mvalue);
                        } elseif ($mvalue !== '' && $msyncvalue !== '') {
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
     *
     * @return void
     */
    public static function dismissImport()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!empty($_POST['plugin']) && in_array(strtolower(trim($_POST['plugin'])), array('_aio_', '_yoast_'))) {
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
     *
     * @param integer $meta_id    Meta id
     * @param integer $object_id  Object id
     * @param string  $meta_key   Meta key
     * @param string  $meta_value Meta value
     *
     * @return boolean|null
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
     * Deletes all custom fields with the specified key
     *
     * @param integer $meta_ids   Meta id
     * @param integer $object_id  Object id
     * @param string  $meta_key   Meta key
     * @param string  $meta_value Meta value
     *
     * @return boolean|null
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
     *
     * @param integer $object_id  Object id
     * @param string  $meta_key   Meta key
     * @param string  $meta_value Meta value
     * @param string  $type       Type
     *
     * @return boolean
     */
    private static function doUpdateMetaSync($object_id, $meta_key, $meta_value, $type = '')
    {
        $sync = get_option('plugin_to_sync_with');
        if (!$sync || !in_array($sync, array('_aio_', '_yoast_'))) {
            return false;
        }

        $metakeys = array(
            '_metaseo_' => array('_metaseo_metatitle', '_metaseo_metadesc'),
            '_aio_'     => array('_aioseop_title', '_aioseop_description'),
            '_yoast_'   => array('_yoast_wpseo_title', '_yoast_wpseo_metadesc')
        );

        $_metakeys              = array();
        $_metakeys['_metaseo_'] = $metakeys['_metaseo_'];
        $_metakeys[$sync]       = $metakeys[$sync];
        unset($metakeys);

        foreach ($_metakeys as $identify => $mkeys) {
            foreach ($mkeys as $k => $mkey) {
                if ($meta_key === $mkey) {
                    if ($identify === '_metaseo_') {
                        $mkeysync = $_metakeys[$sync][$k];
                    } else {
                        $mkeysync = $_metakeys['_metaseo_'][$k];
                    }

                    if ($type === 'update') {
                        update_post_meta($object_id, $mkeysync, $meta_value);
                        return true;
                    }

                    if ($type === 'delete') {
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
     *
     * @param string $meta_key Meta key
     *
     * @return boolean
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
