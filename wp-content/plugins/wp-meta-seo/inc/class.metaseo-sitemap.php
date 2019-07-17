<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class MetaSeoSitemap
 * Class that holds most of the sitemap functionality for Meta SEO.
 */
class MetaSeoSitemap
{

    /**
     * Sitemap html
     *
     * @var string
     */
    public $html = '';
    /**
     * WPMS Sitemap xml file
     *
     * @var string
     */
    public $wpms_sitemap_name = 'wpms-sitemap.xml';
    /**
     * Sitemap xml file
     *
     * @var string
     */
    public $wpms_sitemap_default_name = 'sitemap.xml';
    /**
     * List columns sitemap
     *
     * @var array
     */
    public $columns = array('Zezo', 'One', 'Two', 'Three');
    /**
     * Level menu
     *
     * @var array
     */
    public $level = array();
    /**
     * Sitemap settings
     *
     * @var array
     */
    public $settings_sitemap;

    /**
     * MetaSeoSitemap constructor.
     */
    public function __construct()
    {
        $this->getSitemapSettings();
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
        add_filter('the_content', array($this, 'sitemapInContent'));
        add_shortcode('wpms_html_sitemap', array($this, 'sitemapShortcode'));
        add_action('wp_ajax_wpms_regenerate_sitemaps', array($this, 'regenerateSitemaps'));
        add_action('wp_ajax_wpms_save_sitemap_settings', array($this, 'saveSitemapSettings'));
        add_action('wp_ajax_wpms_list_posts_category', array($this, 'listPostsCategory'));
        add_action('wp_update_nav_menu', array($this, 'wpUpdateNavMenu'), 10, 1);
        add_action('wp_update_nav_menu_item', array($this, 'wpAddNavMenuItem'), 10, 3);
    }

    /**
     *  Fires after a navigation menu item has been updated.
     *
     * @param integer $menu_id         Menu ID
     * @param integer $menu_item_db_id Menu item ID
     * @param array   $args            Params
     *
     * @return void
     */
    public function wpAddNavMenuItem($menu_id, $menu_item_db_id, $args)
    {
        $this->autoAddMenuItems($menu_id);
    }

    /**
     * This action is documented in wp-includes/nav-menu.php.
     *
     * @param integer $nav_menu_selected_id Menu ID
     *
     * @return void
     */
    public function wpUpdateNavMenu($nav_menu_selected_id)
    {
        global $pagenow;
        if (isset($pagenow) && $pagenow === 'nav-menus.php') {
            $this->autoAddMenuItems($nav_menu_selected_id);
        }
    }


    /**
     * Auto add menu items
     *
     * @param integer $nav_menu_selected_id Menu ID
     *
     * @return void
     */
    public function autoAddMenuItems($nav_menu_selected_id)
    {
        $settings = get_option('_metaseo_settings_sitemap');
        if (isset($settings['check_all_menu_items']) && in_array($nav_menu_selected_id, $settings['check_all_menu_items'])) {
            $list_submenu_id = get_objects_in_term($nav_menu_selected_id, 'nav_menu');
            $args            = array(
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'posts_per_page' => - 1,
                'post_type'      => 'nav_menu_item',
                'post_status'    => 'any',
                'post__in'       => $list_submenu_id,
                'meta_key'       => '_menu_item_menu_item_parent',
                'meta_value'     => 0
            );

            $query    = new WP_Query($args);
            $submenus = $query->get_posts();
            foreach ($submenus as $menu) {
                if (empty($settings['wpms_sitemap_menus'][$menu->ID])) {
                    $settings['wpms_sitemap_menus'][$menu->ID] = array(
                        'menu_id'   => $menu->ID,
                        'priority'  => '1',
                        'frequency' => 'monthly'
                    );
                }
            }

            update_option('_metaseo_settings_sitemap', $settings);
            $this->regenerateSitemaps('submit');
        }
    }

    /**
     * Get sitemap settings
     *
     * @return void
     */
    public function getSitemapSettings()
    {
        $this->settings_sitemap = array(
            'wpms_sitemap_add'           => 0,
            'wpms_sitemap_root'          => 0,
            'wpms_sitemap_author'        => 0,
            'wpms_sitemap_taxonomies'    => array(),
            'wpms_category_link'         => array(),
            'check_all_menu_items'       => array(),
            'wpms_html_sitemap_page'     => 0,
            'wpms_html_sitemap_column'   => 1,
            'wpms_html_sitemap_theme'    => 'default',
            'wpms_html_sitemap_position' => 'after',
            'wpms_display_column_menus'  => array(0),
            'wpms_display_column_posts'  => 1,
            'wpms_display_column_pages'  => 1,
            'wpms_display_order_menus'   => 1,
            'wpms_display_order_posts'   => 2,
            'wpms_display_order_pages'   => 3,
            'wpms_display_order_urls'    => 4,
            'wpms_public_name_pages'     => '',
            'wpms_public_name_posts'     => '',
            'wpms_sitemap_posts'         => array(),
            'wpms_sitemap_pages'         => array(),
            'wpms_sitemap_include_lang'  => array()
        );

        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            $this->settings_sitemap['wpms_sitemap_customUrl']        = array();
            $this->settings_sitemap['wpms_display_column_customUrl'] = 1;
            $this->settings_sitemap['wpms_public_name_customUrl']    = '';
            $custom_post_types                                       = get_post_types(
                array(
                    'public'              => true,
                    'exclude_from_search' => false,
                    '_builtin'            => false
                )
            );
            if (!empty($custom_post_types)) {
                foreach ($custom_post_types as $post_type => $label) {
                    $this->settings_sitemap['wpms_display_column_' . $post_type] = 1;
                    $this->settings_sitemap['wpms_public_name_' . $post_type]    = '';
                    $this->settings_sitemap['wpms_sitemap_' . $post_type]        = array();
                }
            }
        }

        $settings = get_option('_metaseo_settings_sitemap');
        if ((isset($settings['wpms_sitemap_pages']) && is_object($settings['wpms_sitemap_pages'])) || (isset($settings['wpms_sitemap_posts']) && is_object($settings['wpms_sitemap_posts']))
            || (isset($settings['wpms_sitemap_menus']) && is_object($settings['wpms_sitemap_menus']))) {
            $settings_array = json_decode(json_encode($settings), true);
            update_option('_metaseo_settings_sitemap', $settings_array);
        }

        $settings = get_option('_metaseo_settings_sitemap');
        if (is_array($settings)) {
            $this->settings_sitemap = array_merge($this->settings_sitemap, $settings);
        }
    }

    /**
     * Load metaseo script and style front-end
     *
     * @return void
     */
    public function enqueueScripts()
    {
        global $post;
        if (empty($post)) {
            return;
        }

        if (!empty($this->settings_sitemap) && (int) $this->settings_sitemap['wpms_html_sitemap_page'] !== (int) $post->ID) {
            return;
        }

        wp_enqueue_script(
            'site-jPages-js',
            plugins_url('assets/js/site-jPages.js', dirname(__FILE__)),
            array('jquery'),
            WPMSEO_VERSION,
            true
        );
        wp_localize_script(
            'site-jPages-js',
            'wpms_avarible',
            $this->localizeScript()
        );
        wp_enqueue_script(
            'jpage-js',
            plugins_url('assets/js/jPages.js', dirname(__FILE__)),
            array('jquery'),
            WPMSEO_VERSION,
            true
        );
        wp_enqueue_style(
            'jpage-css',
            plugins_url('assets/css/jPages.css', dirname(__FILE__)),
            array(),
            WPMSEO_VERSION
        );
    }

    /**
     * Localize a script
     *
     * @return array
     */
    public function localizeScript()
    {
        $custom_post_types = get_post_types(
            array(
                'public'              => true,
                'exclude_from_search' => false,
                '_builtin'            => false
            )
        );
        $arrays            = array(
            'wpms_display_column_menus' => $this->settings_sitemap['wpms_display_column_menus'],
            'post_type'                 => $custom_post_types
        );
        return $arrays;
    }

    /**
     * Load metaseo script and style back-end
     *
     * @return void
     */
    public function adminEnqueueScripts()
    {
        global $current_screen;
        if (!empty($current_screen) && $current_screen->base !== 'wp-meta-seo_page_metaseo_google_sitemap') {
            return;
        }

        $custom_post_types = get_post_types(
            array(
                'public'              => true,
                'exclude_from_search' => false,
                '_builtin'            => false
            )
        );

        wp_enqueue_script(
            'metaseositemap',
            plugins_url('assets/js/metaseo_sitemap.js', dirname(__FILE__)),
            array('jquery'),
            WPMSEO_VERSION,
            true
        );
        wp_localize_script(
            'metaseositemap',
            'wpmseositemap',
            array(
                'post_type' => $custom_post_types
            )
        );
        wp_enqueue_script(
            'jpage-js',
            plugins_url('assets/js/jPages.js', dirname(__FILE__)),
            array('jquery'),
            WPMSEO_VERSION,
            true
        );
        wp_enqueue_style(
            'metaseositemapstyle',
            plugins_url('assets/css/metaseo_sitemap.css', dirname(__FILE__)),
            array(),
            WPMSEO_VERSION
        );
        wp_enqueue_style(
            'jpage-css',
            plugins_url('assets/css/jPages.css', dirname(__FILE__)),
            array(),
            WPMSEO_VERSION
        );
        wp_enqueue_style(
            'm-style-qtip',
            plugins_url('assets/css/jquery.qtip.css', dirname(__FILE__)),
            array(),
            WPMSEO_VERSION
        );

        wp_enqueue_script(
            'jquery-qtip',
            plugins_url('assets/js/jquery.qtip.min.js', dirname(__FILE__)),
            array('jquery'),
            '2.2.1',
            true
        );
    }

    /**
     * Display field sitemap link
     *
     * @return void
     */
    public function sitemapLink()
    {
        echo '<input id="wpms_check_firstsave" name="_metaseo_settings_sitemap[wpms_check_firstsave]"
 type="hidden" value="1">';
        if (is_multisite()) {
            $home_url = preg_replace(
                '/[^a-zA-ZА-Яа-я0-9\s]/',
                '_',
                str_replace('http://', '', str_replace('https://', '', ABSPATH))
            );
            if ((int)$this->settings_sitemap['wpms_sitemap_root'] === 1) {
                $value    = trim(ABSPATH, '/') . '/sitemap_' . $home_url . '.xml';
                $link     = get_option('siteurl') . '/sitemap_' . $home_url . '.xml';
            } else {
                $value    = trim(ABSPATH, '/') . '/wpms-sitemap_' . $home_url . '.xml';
                $link     = get_option('siteurl') . '/wpms-sitemap_' . $home_url . '.xml';
            }
        } else {
            if ((int)$this->settings_sitemap['wpms_sitemap_root'] === 1) {
                $value = trim(ABSPATH, '/') . '/' . $this->wpms_sitemap_default_name;
                $link  = get_option('siteurl') . '/' . $this->wpms_sitemap_default_name;
            } else {
                $value = trim(ABSPATH, '/') . '/' . $this->wpms_sitemap_name;
                $link  = get_option('siteurl') . '/' . $this->wpms_sitemap_name;
            }
        }
        echo '<input readonly id="wpms_sitemap_link" name="_metaseo_settings_sitemap[wpms_sitemap_link]"
         type="text" value="' . esc_attr($link) . '" size="50" class="wpms-large-input wpms-no-margin wpms_width_90" />';
        echo '<a class="wpms-open-xml-sitemap ju-button orange-button waves-effect waves-light wpms-small-btn" href="' . esc_url($link) . '" target="_blank">' . esc_html__('Open', 'wp-meta-seo') . '</a>';
    }

    /**
     * Display field sitemap lang
     *
     * @return void
     */
    public function sitemapIncludeLanguages()
    {
        $lang    = $this->settings_sitemap['wpms_sitemap_include_lang'];
        $sl_lang = apply_filters('wpms_get_languagesList', '', $lang, 'multiple');
        // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in the method MetaSeoAddonAdmin::listLanguageSelect
        echo $sl_lang;
    }

    /**
     * Display field sitemap link check
     *
     * @return void
     */
    public function checkLink()
    {
        ?>
        <a href="#links-check-list" title="<?php esc_attr_e('Sitemap links', 'wp-meta-seo') ?>"
           class="ju-button orange-button wpms-small-btn wpms_run_linkcheck m-t-5"
        ><?php esc_html_e('Run a link check', 'wp-meta-seo') ?></a>
        <?php
    }

    /**
     * Display field sitemap author
     *
     * @return void
     */
    public function sitemapAuthor()
    {
        ?>
        <input id="wpms_sitemap_author" type="checkbox" name="_metaseo_settings_sitemap[wpms_sitemap_author]"
               value="1" <?php checked(1, $this->settings_sitemap['wpms_sitemap_author']); ?>>
        <?php
    }

    /**
     * Display field sitemap taxonomies
     *
     * @return void
     */
    public function sitemapTaxonomies()
    {
        $wpms_taxonomies = array(
            'category' => 'Post category',
            'post_tag' => 'Post tag'
        );
        foreach ($wpms_taxonomies as $key => $value) {
            ?>
            <div class="pure-checkbox wpms_left wpms_width_50">
                <?php if (in_array($key, $this->settings_sitemap['wpms_sitemap_taxonomies'])) : ?>
                    <input title class="wpms_sitemap_taxonomies"
                           id="<?php echo esc_attr('wpms_sitemap_taxonomies_' . $key); ?>"
                           type="checkbox"
                           name="_metaseo_settings_sitemap[wpms_sitemap_taxonomies][]"
                           value="<?php echo esc_attr($key) ?>" checked>
                <?php else : ?>
                    <input title class="wpms_sitemap_taxonomies"
                           id="<?php echo esc_attr('wpms_sitemap_taxonomies_' . $key); ?>"
                           type="checkbox"
                           name="_metaseo_settings_sitemap[wpms_sitemap_taxonomies][]"
                           value="<?php echo esc_attr($key) ?>">
                <?php endif; ?>
                <label class="wpms-text"
                       for="<?php echo esc_attr('wpms_sitemap_taxonomies_' . $key); ?>"><?php echo esc_html($value) ?></label>
            </div>
            <?php
        }
    }

    /**
     * Display field sitemap list page
     *
     * @return void
     */
    public function sitemapPage()
    {
        global $wpdb;
        $pages        = get_pages();
        $sitemap_page = $wpdb->get_row($wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'posts WHERE post_title = %s AND post_excerpt = %s AND post_type = %s',
            array(
                'WPMS HTML Sitemap',
                'metaseo_html_sitemap',
                'page'
            )
        ));

        if (empty($this->settings_sitemap['wpms_html_sitemap_page']) && !empty($sitemap_page)) {
            $this->settings_sitemap['wpms_html_sitemap_page'] = $sitemap_page->ID;
        }
        ?>
        <select id="wpms_html_sitemap_page" name="_metaseo_settings_sitemap[wpms_html_sitemap_page]"
                class="wpms-large-input wpms_width_90">
            <option value="0"><?php esc_html_e('- Choose Your Sitemap Page -', 'wp-meta-seo') ?></option>
            <?php
            foreach ($pages as $page) {
                if ((int) $this->settings_sitemap['wpms_html_sitemap_page'] === (int) $page->ID) {
                    echo '<option selected value="' . esc_attr($page->ID) . '">' . esc_html($page->post_title) . '</option>';
                } else {
                    echo '<option value="' . esc_attr($page->ID) . '">' . esc_html($page->post_title) . '</option>';
                }
            }
            ?>
        </select>
        <?php
        if (!empty($this->settings_sitemap['wpms_html_sitemap_page'])) {
            echo '<a class="ju-button orange-button waves-effect waves-light wpms-open-html-sitemap wpms-no-margin wpms-small-btn" href="' . esc_url(get_post_permalink($this->settings_sitemap['wpms_html_sitemap_page'])) . '"
             target="_blank">' . esc_html__('Open', 'wp-meta-seo') . '</a>';
        }
        ?>
        <?php
    }

    /**
     * Display field sitemap column
     *
     * @return void
     */
    public function sitemapColumn()
    {
        ?>
        <label>
            <select id="wpms_html_sitemap_column" name="_metaseo_settings_sitemap[wpms_html_sitemap_column]"
                    class="wpms-large-input wpms_width_100">
                <option <?php selected($this->settings_sitemap['wpms_html_sitemap_column'], 1) ?>
                        value="1"><?php esc_html_e('1 column', 'wp-meta-seo') ?></option>
                <option <?php selected($this->settings_sitemap['wpms_html_sitemap_column'], 2) ?>
                        value="2"><?php esc_html_e('2 columns', 'wp-meta-seo') ?></option>
                <option <?php selected($this->settings_sitemap['wpms_html_sitemap_column'], 3) ?>
                        value="3"><?php esc_html_e('3 columns', 'wp-meta-seo') ?></option>
            </select>
        </label>
        <?php
    }

    /**
     * Display field sitemap position
     *
     * @return void
     */
    public function sitemapTheme()
    {
        ?>
        <label>
            <select id="wpms_html_sitemap_theme" name="_metaseo_settings_sitemap[wpms_html_sitemap_theme]"
                    class="wpms-large-input wpms_width_100">
                <option <?php selected($this->settings_sitemap['wpms_html_sitemap_theme'], 'default') ?>
                        value="default"><?php esc_html_e('Simple list', 'wp-meta-seo') ?></option>
                <option <?php selected($this->settings_sitemap['wpms_html_sitemap_theme'], 'accordions') ?>
                        value="accordions"><?php esc_html_e('List with accordions', 'wp-meta-seo') ?></option>
                <option <?php selected($this->settings_sitemap['wpms_html_sitemap_theme'], 'tab') ?>
                        value="tab"><?php esc_html_e('Tab layout', 'wp-meta-seo') ?></option>
            </select>
        </label>
        <?php
    }

    /**
     * Display field sitemap position
     *
     * @return void
     */
    public function sitemapPosition()
    {
        ?>
        <select id="wpms_html_sitemap_position" name="_metaseo_settings_sitemap[wpms_html_sitemap_position]"
                class="wpms-large-input wpms_width_100">
            <option <?php selected($this->settings_sitemap['wpms_html_sitemap_position'], 'replace') ?>
                    value="replace"><?php esc_html_e('Replace the Page Content', 'wp-meta-seo') ?></option>
            <option <?php selected($this->settings_sitemap['wpms_html_sitemap_position'], 'before') ?>
                    value="before"><?php esc_html_e('Before Page Content', 'wp-meta-seo') ?></option>
            <option <?php selected($this->settings_sitemap['wpms_html_sitemap_position'], 'after') ?>
                    value="after"><?php esc_html_e('After Page Content', 'wp-meta-seo') ?></option>
        </select>
        <?php
    }

    /**
     * Get info of sitemap file xml
     *
     * @return array
     */
    public function getPathSitemapFile()
    {
        if (is_multisite()) {
            $home_url = preg_replace(
                '/[^a-zA-ZА-Яа-я0-9\s]/',
                '_',
                str_replace('http://', '', str_replace('https://', '', site_url()))
            );
            $xml_file = 'wpms-sitemap_' . $home_url . '.xml';
        } else {
            $xml_file = $this->wpms_sitemap_name;
        }
        $xml_path = ABSPATH . $xml_file;
        return array('path' => $xml_path, 'name' => $xml_file);
    }

    /**
     * Create sitemap
     *
     * @param string $sitemap_xml_name Sitemap file name
     *
     * @return void
     */
    public function createSitemap($sitemap_xml_name)
    {
        global $wpdb;
        $taxonomies = array();
        $list_links = array();
        foreach ($this->settings_sitemap['wpms_sitemap_taxonomies'] as $val) {
            $taxonomies[] = $val;
        }

        $xml                 = new DomDocument('1.0', 'utf-8');
        $xml_stylesheet_path = content_url();
        if (defined('WP_PLUGIN_DIR')) {
            $xml_stylesheet_path .= '/' . basename(WP_PLUGIN_DIR) . '/wp-meta-seo/wpms-sitemap.xsl';
        } else {
            $xml_stylesheet_path .= '/plugins/wp-meta-seo/sitemap.xsl';
        }

        $xslt = $xml->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . $xml_stylesheet_path . '"');
        $xml->appendChild($xslt);
        $gglstmp_urlset = $xml->appendChild(
            $xml->createElementNS(
                'http://www.sitemaps.org/schemas/sitemap/0.9',
                'urlset'
            )
        );
        $gglstmp_urlset->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        /* add home page */
        $list_links[] = home_url('/');
        $url          = $gglstmp_urlset->appendChild($xml->createElement('url'));
        $loc          = $url->appendChild($xml->createElement('loc'));
        $loc->appendChild($xml->createTextNode(home_url('/')));
        $lastmod = $url->appendChild($xml->createElement('lastmod'));
        $lastmod->appendChild($xml->createTextNode(date('Y-m-d\TH:i:sP', time())));
        $changefreq = $url->appendChild($xml->createElement('changefreq'));
        $changefreq->appendChild($xml->createTextNode('monthly'));
        $priority = $url->appendChild($xml->createElement('priority'));
        $priority->appendChild($xml->createTextNode(1.0));

        // add menus post custom
        $menus = $this->getAllMenus();
        $res   = $menus['posts_custom'];
        if (!empty($res)) {
            foreach ($res as $val) {
                $menu_object = $wpdb->get_results($wpdb->prepare(
                    'SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE meta_key=%s AND meta_value=%d',
                    array('_menu_item_object_id', $val->ID)
                ));
                if (!empty($menu_object)) {
                    foreach ($menu_object as $menu) {
                        $id         = $menu->post_id;
                        $type       = get_post_meta($id, '_menu_item_type', true);
                        $check_type = get_post_meta($id, '_menu_item_object', true);
                        $permalink  = $this->getPermalinkSitemap($check_type, $val->ID);
                        if ($permalink !== '#') {
                            if (strpos($permalink, '#') !== false) {
                                // Check anchor links
                                $permalink = strstr($permalink, '#', true);
                            }
                            if (!in_array($permalink, $list_links)) {
                                $list_links[] = $permalink;
                                if ($type !== 'taxonomy') {
                                    $gglstmp_url = $gglstmp_urlset->appendChild($xml->createElement('url'));
                                    $loc         = $gglstmp_url->appendChild($xml->createElement('loc'));
                                    $loc->appendChild($xml->createTextNode($permalink));
                                    $lastmod = $gglstmp_url->appendChild($xml->createElement('lastmod'));
                                    $now     = $val->post_modified;
                                    $date    = date('Y-m-d\TH:i:sP', strtotime($now));
                                    $lastmod->appendChild($xml->createTextNode($date));
                                    $changefreq = $gglstmp_url->appendChild($xml->createElement('changefreq'));
                                    if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                                        $changefreq->appendChild($xml->createTextNode('monthly'));
                                    } else {
                                        if (empty($this->settings_sitemap['wpms_sitemap_menus'][$id]['frequency'])) {
                                            $this->settings_sitemap['wpms_sitemap_menus'][$id]['frequency'] = 'monthly';
                                        }
                                        $changefreq->appendChild(
                                            $xml->createTextNode(
                                                $this->settings_sitemap['wpms_sitemap_menus'][$id]['frequency']
                                            )
                                        );
                                    }

                                    $priority = $gglstmp_url->appendChild($xml->createElement('priority'));
                                    if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                                        $priority->appendChild($xml->createTextNode('1.0'));
                                    } else {
                                        if (empty($this->settings_sitemap['wpms_sitemap_menus'][$id]['priority'])) {
                                            $this->settings_sitemap['wpms_sitemap_menus'][$id]['priority'] = '1.0';
                                        }
                                        $priority->appendChild(
                                            $xml->createTextNode(
                                                $this->settings_sitemap['wpms_sitemap_menus'][$id]['priority']
                                            )
                                        );
                                    }

                                    $this->createXmlLang(
                                        $xml,
                                        $id,
                                        'post_post',
                                        $loc
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        // add menus category
        $res = $menus['categories'];
        if (!empty($res)) {
            foreach ($res as $k => $val) {
                $menu_object = $wpdb->get_results($wpdb->prepare(
                    'SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE meta_key=%s AND meta_value=%d',
                    array(
                        '_menu_item_object_id',
                        $val
                    )
                ));
                if (!empty($menu_object)) {
                    foreach ($menu_object as $menu) {
                        $id         = $menu->post_id;
                        $type       = get_post_meta($id, '_menu_item_type', true);
                        $check_type = get_post_meta($id, '_menu_item_object', true);
                        $permalink  = get_term_link((int) $val, $check_type);
                        if (empty($permalink)) {
                            $permalink = get_permalink($id);
                        }
                        if (strpos($permalink, '#') !== false) {
                            // Check anchor links
                            $permalink = strstr($permalink, '#', true);
                        }
                        if (!in_array($permalink, $list_links)) {
                            $list_links[] = $permalink;
                            if ($type === 'taxonomy') {
                                $gglstmp_url = $gglstmp_urlset->appendChild($xml->createElement('url'));
                                $loc         = $gglstmp_url->appendChild($xml->createElement('loc'));
                                $loc->appendChild($xml->createTextNode($permalink));
                                $lastmod = $gglstmp_url->appendChild($xml->createElement('lastmod'));
                                $ps      = get_post($id);

                                if (!empty($ps)) {
                                    $now  = $ps->post_modified;
                                    $date = date('Y-m-d\TH:i:sP', strtotime($now));
                                    $lastmod->appendChild($xml->createTextNode($date));
                                }

                                $changefreq = $gglstmp_url->appendChild($xml->createElement('changefreq'));
                                if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                                    $changefreq->appendChild($xml->createTextNode('monthly'));
                                } else {
                                    if (empty($this->settings_sitemap['wpms_sitemap_menus'][$id]['frequency'])) {
                                        $menufre = 'monthly';
                                    } else {
                                        $menufre = $this->settings_sitemap['wpms_sitemap_menus'][$id]['frequency'];
                                    }
                                    $changefreq->appendChild($xml->createTextNode($menufre));
                                }

                                $priority = $gglstmp_url->appendChild($xml->createElement('priority'));
                                if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                                    $priority->appendChild($xml->createTextNode('1.0'));
                                } else {
                                    if (empty($this->settings_sitemap['wpms_sitemap_menus'][$id]['priority'])) {
                                        $menupriority = '1.0';
                                    } else {
                                        $menupriority = $this->settings_sitemap['wpms_sitemap_menus'][$id]['priority'];
                                    }
                                    $priority->appendChild($xml->createTextNode($menupriority));
                                }
                                $this->createXmlLang($xml, $id, 'post_post', $loc);
                            }
                        }
                    }
                }
            }
        }

        // add posts
        $res = $this->getPostsSitemap();
        if (!empty($res)) {
            foreach ($res as $val) {
                /* get translation post id */
                $permalink = get_permalink($val->ID);
                if (strpos($permalink, '#') !== false) {
                    // Check anchor links
                    $permalink = strstr($permalink, '#', true);
                }
                if (!in_array($permalink, $list_links)) {
                    $list_links[] = $permalink;
                    $gglstmp_url  = $gglstmp_urlset->appendChild($xml->createElement('url'));
                    $loc          = $gglstmp_url->appendChild($xml->createElement('loc'));
                    $loc->appendChild($xml->createTextNode($permalink));
                    $lastmod = $gglstmp_url->appendChild($xml->createElement('lastmod'));
                    $now     = $val->post_modified;
                    $date    = date('Y-m-d\TH:i:sP', strtotime($now));
                    $lastmod->appendChild($xml->createTextNode($date));
                    $changefreq = $gglstmp_url->appendChild($xml->createElement('changefreq'));
                    if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                        $changefreq->appendChild($xml->createTextNode('monthly'));
                    } else {
                        if (empty($this->settings_sitemap['wpms_sitemap_posts'][$val->ID]['frequency'])) {
                            $postfrequency = 'monthly';
                        } else {
                            $postfrequency = $this->settings_sitemap['wpms_sitemap_posts'][$val->ID]['frequency'];
                        }
                        $changefreq->appendChild($xml->createTextNode($postfrequency));
                    }

                    $priority = $gglstmp_url->appendChild($xml->createElement('priority'));
                    if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                        $priority->appendChild($xml->createTextNode('1.0'));
                    } else {
                        if (empty($this->settings_sitemap['wpms_sitemap_posts'][$val->ID]['priority'])) {
                            $postpriority = '1.0';
                        } else {
                            $postpriority = $this->settings_sitemap['wpms_sitemap_posts'][$val->ID]['priority'];
                        }
                        $priority->appendChild($xml->createTextNode($postpriority));
                    }

                    $this->createXmlLang($xml, $val->ID, 'post_post', $loc);
                }
            }
        }

        // run when WP Meta SEO Addon active
        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            // add custom post type
            $custom_post_types = get_post_types(
                array(
                    'public'              => true,
                    'exclude_from_search' => false,
                    '_builtin'            => false
                )
            );
            if (!empty($custom_post_types)) {
                foreach ($custom_post_types as $pt => $label) {
                    $ids              = array(0);
                    $settings_sitemap = get_option('_metaseo_settings_sitemap');
                    if (!empty($settings_sitemap['wpms_sitemap_' . $pt])) {
                        foreach ((array) $settings_sitemap['wpms_sitemap_' . $pt] as $k => $v) {
                            if (!empty($v['post_id'])) {
                                $ids[] = (int) $v['post_id'];
                            }
                        }
                    }

                    $posts = $wpdb->get_results($wpdb->prepare('SELECT ID, post_modified FROM ' . $wpdb->posts . ' WHERE   post_status = %s AND post_type = %s AND ID IN (' . implode(',', esc_sql($ids)) . ') ORDER BY post_date DESC', array(
                        'publish',
                        $pt
                    )));
                    if (!empty($posts)) {
                        foreach ($posts as $val) {
                            $permalink = get_permalink($val->ID);
                            if (strpos($permalink, '#') !== false) {
                                // Check anchor links
                                $permalink = strstr($permalink, '#', true);
                            }
                            if (!in_array($permalink, $list_links)) {
                                $list_links[] = $permalink;
                                $gglstmp_url  = $gglstmp_urlset->appendChild($xml->createElement('url'));
                                $loc          = $gglstmp_url->appendChild($xml->createElement('loc'));
                                $loc->appendChild($xml->createTextNode($permalink));
                                $lastmod = $gglstmp_url->appendChild($xml->createElement('lastmod'));
                                $now     = $val->post_modified;
                                $date    = date('Y-m-d\TH:i:sP', strtotime($now));
                                $lastmod->appendChild($xml->createTextNode($date));
                                $changefreq = $gglstmp_url->appendChild($xml->createElement('changefreq'));
                                if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                                    $changefreq->appendChild($xml->createTextNode('monthly'));
                                } else {
                                    if (empty($this->settings_sitemap['wpms_sitemap_' . $pt][$val->ID]['frequency'])) {
                                        $postfr = 'monthly';
                                    } else {
                                        $postfr = $this->settings_sitemap['wpms_sitemap_' . $pt][$val->ID]['frequency'];
                                    }
                                    $changefreq->appendChild($xml->createTextNode($postfr));
                                }

                                $priority = $gglstmp_url->appendChild($xml->createElement('priority'));
                                if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                                    $priority->appendChild($xml->createTextNode('1.0'));
                                } else {
                                    if (empty($this->settings_sitemap['wpms_sitemap_' . $pt][$val->ID]['priority'])) {
                                        $postpri = '1.0';
                                    } else {
                                        $postpri = $this->settings_sitemap['wpms_sitemap_' . $pt][$val->ID]['priority'];
                                    }
                                    $priority->appendChild($xml->createTextNode($postpri));
                                }

                                $this->createXmlLang(
                                    $xml,
                                    $val->ID,
                                    'post_post',
                                    $loc
                                );
                            }
                        }
                    }
                }
            }

            // add custom Url
            $listUrls = get_option('wpms_customUrls_list');
            $settings = get_option('_metaseo_settings_sitemap');
            if (!empty($settings['wpms_sitemap_customUrl']) && $settings['wpms_sitemap_customUrl'] !== '{}') {
                foreach ($settings['wpms_sitemap_customUrl'] as $key => $customUrl) {
                    if (!empty($listUrls[$key])) {
                        if (strpos($listUrls[$key]['link'], '#') !== false) {
                            // Check anchor links
                            $listUrls[$key]['link'] = strstr($listUrls[$key]['link'], '#', true);
                        }
                        if (!in_array($listUrls[$key]['link'], $list_links)) {
                            $list_links[] = $listUrls[$key]['link'];
                            $gglstmp_url  = $gglstmp_urlset->appendChild($xml->createElement('url'));
                            $loc          = $gglstmp_url->appendChild($xml->createElement('loc'));

                            $loc->appendChild($xml->createTextNode($listUrls[$key]['link']));
                            $lastmod = $gglstmp_url->appendChild($xml->createElement('lastmod'));
                            $date    = date('Y-m-d\TH:i:sP', $key);
                            $lastmod->appendChild($xml->createTextNode($date));
                            $changefreq = $gglstmp_url->appendChild($xml->createElement('changefreq'));

                            if (empty($customUrl['frequency'])) {
                                $pagefrequency = 'monthly';
                            } else {
                                $pagefrequency = $customUrl['frequency'];
                            }
                            $changefreq->appendChild($xml->createTextNode($pagefrequency));

                            $priority = $gglstmp_url->appendChild($xml->createElement('priority'));

                            if (empty($customUrl['priority'])) {
                                $pagepriority = '1.0';
                            } else {
                                $pagepriority = $customUrl['priority'];
                            }
                            $priority->appendChild($xml->createTextNode($pagepriority));
                        }
                    }
                }
            }
        }
        // ========================================
        // add pages
        $res = $this->getPagesSitemap();
        if (!empty($res)) {
            foreach ($res as $val) {
                /* get translation post id */
                $permalink = get_permalink($val->ID);
                if (strpos($permalink, '#') !== false) {
                    // Check anchor links
                    $permalink = strstr($permalink, '#', true);
                }
                if (!in_array($permalink, $list_links)) {
                    $list_links[] = $permalink;
                    $gglstmp_url  = $gglstmp_urlset->appendChild($xml->createElement('url'));
                    $loc          = $gglstmp_url->appendChild($xml->createElement('loc'));

                    $loc->appendChild($xml->createTextNode($permalink));
                    $lastmod = $gglstmp_url->appendChild($xml->createElement('lastmod'));
                    $now     = $val->post_modified;
                    $date    = date('Y-m-d\TH:i:sP', strtotime($now));
                    $lastmod->appendChild($xml->createTextNode($date));
                    $changefreq = $gglstmp_url->appendChild($xml->createElement('changefreq'));
                    if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                        $changefreq->appendChild($xml->createTextNode('monthly'));
                    } else {
                        if (empty($this->settings_sitemap['wpms_sitemap_pages'][$val->ID]['frequency'])) {
                            $pagefrequency = 'monthly';
                        } else {
                            $pagefrequency = $this->settings_sitemap['wpms_sitemap_pages'][$val->ID]['frequency'];
                        }
                        $changefreq->appendChild($xml->createTextNode($pagefrequency));
                    }
                    $priority = $gglstmp_url->appendChild($xml->createElement('priority'));
                    if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                        $priority->appendChild($xml->createTextNode('1.0'));
                    } else {
                        if (empty($this->settings_sitemap['wpms_sitemap_pages'][$val->ID]['priority'])) {
                            $pagepriority = '1.0';
                        } else {
                            $pagepriority = $this->settings_sitemap['wpms_sitemap_pages'][$val->ID]['priority'];
                        }
                        $priority->appendChild($xml->createTextNode($pagepriority));
                    }

                    $this->createXmlLang($xml, $val->ID, 'post_page', $loc);
                }
            }
        }

        // add category
        if (!empty($taxonomies)) {
            foreach ($taxonomies as $value) {
                $terms = get_terms($value, 'hide_empty=1');
                if (!empty($terms) && !is_wp_error($terms)) {
                    foreach ($terms as $term_value) {
                        $permalink = get_term_link((int) $term_value->term_id, $value);
                        if (strpos($permalink, '#') !== false) {
                            // Check anchor links
                            $permalink = strstr($permalink, '#', true);
                        }
                        if (!in_array($permalink, $list_links)) {
                            $list_links[] = $permalink;
                            $gglstmp_url  = $gglstmp_urlset->appendChild($xml->createElement('url'));
                            $loc          = $gglstmp_url->appendChild($xml->createElement('loc'));

                            $loc->appendChild($xml->createTextNode($permalink));
                            $lastmod = $gglstmp_url->appendChild($xml->createElement('lastmod'));

                            $now  = $wpdb->get_var(
                                $wpdb->prepare('SELECT post_modified FROM ' . $wpdb->posts . ', ' . $wpdb->term_relationships . ' WHERE post_status = %s AND term_taxonomy_id = %d AND $wpdb->posts.ID = $wpdb->term_relationships.object_id ORDER BY post_modified DESC', array(
                                    'publish',
                                    $term_value->term_taxonomy_id
                                ))
                            );
                            $date = date('Y-m-d\TH:i:sP', strtotime($now));
                            $lastmod->appendChild($xml->createTextNode($date));
                            $changefreq = $gglstmp_url->appendChild($xml->createElement('changefreq'));
                            $changefreq->appendChild($xml->createTextNode('monthly'));
                            $priority = $gglstmp_url->appendChild($xml->createElement('priority'));
                            $priority->appendChild($xml->createTextNode(1.0));
                        }
                    }
                }
            }
        }

        $xml->formatOutput = true;

        if (!is_writable(ABSPATH)) {
            chmod(ABSPATH, 0755);
        }

        /**
         * Filter run before save sitemap to xml file
         *
         * @param object The current xml object
         *
         * @return object
         */
        $xml = apply_filters('wpms_save_sitemap_xml', $xml);

        if (is_multisite()) {
            $home_url = preg_replace(
                '/[^a-zA-ZА-Яа-я0-9\s]/',
                '_',
                str_replace('http://', '', str_replace('https://', '', site_url()))
            );
            $xml->save(ABSPATH . 'sitemap_' . $home_url . '.xml');
        } else {
            $xml->save(ABSPATH . $sitemap_xml_name);
        }
        $this->sitemapInfos();
    }

    /**
     * Create xml language
     *
     * @param object  $xml     XML
     * @param integer $id      Element Id
     * @param string  $el_type Element type
     * @param object  $loc     Node Info
     *
     * @return void
     */
    public function createXmlLang($xml, $id, $el_type, $loc)
    {
        if (is_plugin_active(WPMSEO_ADDON_FILENAME) && is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
            if (!empty($this->settings_sitemap['wpms_sitemap_include_lang'])) {
                global $sitepress;
                $trid = $sitepress->get_element_trid($id, $el_type);
                if ($trid) {
                    // get post $translation
                    $translations = $sitepress->get_element_translations($trid, $el_type, true, true, true);
                    foreach ($translations as $translation) {
                        if (isset($translation->language_code)
                            && in_array(
                                $translation->language_code,
                                $this->settings_sitemap['wpms_sitemap_include_lang']
                            )) {
                            $permalink = get_permalink($translation->element_id);
                            // can use
                            // $xml->appendChild($xml->createElementNS('http://www.w3.org/1999/xhtml', 'xhtml:link'));
                            $node = $xml->appendChild($xml->createElement('xhtml:link'));
                            $node->setAttribute('rel', 'alternate');
                            $node->setAttribute('hreflang', $translation->language_code);
                            $node->setAttribute('href', $permalink);
                            $loc->parentNode->appendChild($node);
                        }
                    }
                }
            }
        } elseif (is_plugin_active(WPMSEO_ADDON_FILENAME) && is_plugin_active('polylang/polylang.php')) {
            if (!empty($this->settings_sitemap['wpms_sitemap_include_lang'])) {
                global $polylang;
                $model      = $polylang->filters->links_model->model;
                $model_post = $polylang->filters->links_model->model->post;
                foreach ($model->get_languages_list() as $language) {
                    $value = $model_post->get_translation($id, $language);
                    if ($value) {
                        $lang = pll_get_post_language($value);
                        if (isset($lang) && in_array($lang, $this->settings_sitemap['wpms_sitemap_include_lang'])) {
                            $permalink = get_permalink($value);
                            $node      = $xml->appendChild(
                                $xml->createElementNS('http://www.w3.org/1999/xhtml', 'xhtml:link')
                            );
                            $node->setAttribute('rel', 'alternate');
                            $node->setAttribute('hreflang', $lang);
                            $node->setAttribute('href', $permalink);
                            $loc->parentNode->appendChild($node);
                        }
                    }
                }
            }
        }
    }

    /**
     * Retrieves the full permalink for the current post or post ID
     *
     * @param string  $type Element type
     * @param integer $id   Element id
     *
     * @return false|mixed|string
     */
    public function getPermalinkSitemap($type, $id)
    {
        if (isset($type) && $type === 'custom') {
            $permalink = get_post_meta($id, '_menu_item_url', true);
        } elseif ($type === 'taxonomy') {
            $permalink = get_category_link($id);
        } else {
            $permalink = get_permalink($id);
        }
        return $permalink;
    }

    /**
     * Update sitemap setting
     *
     * @return void
     */
    public function sitemapInfos()
    {
        $settings  = get_option('_metaseo_settings_sitemap');
        $info_file = $this->getPathSitemapFile();
        $xml_url   = site_url('/') . $info_file['name'];
        if (file_exists($info_file['path'])) {
            $settings['sitemap'] = array(
                'file'    => $info_file['name'],
                'path'    => $info_file['path'],
                'loc'     => $xml_url,
                'lastmod' => date('Y-m-d\TH:i:sP', filemtime($info_file['path']))
            );
            update_option('_metaseo_settings_sitemap', $settings);
        }
    }

    /**
     * Display sitemap posts by column in front-end
     *
     * @return string
     */
    public function displayPosts()
    {
        $html      = '';
        $postTitle = get_post_type_object('post');
        $postTitle = $postTitle->label;

        if (get_option('show_on_front') === 'page') {
            $postsURL  = get_permalink(get_option('page_for_posts'));
            $postTitle = get_the_title(get_option('page_for_posts'));
        } else {
            $postsURL = get_bloginfo('url');
        }

        if (!empty($this->settings_sitemap['wpms_public_name_posts'])) {
            $postTitle = $this->settings_sitemap['wpms_public_name_posts'];
        }
        $html .= '<div id="sitemap_posts" class="wpms_sitemap_posts"><h4>';
        if ($postsURL !== '' && $postsURL !== get_permalink($this->settings_sitemap['wpms_html_sitemap_page'])) {
            $html .= '<a href="' . $postsURL . '">' . $postTitle . '</a>';
        } else {
            $html .= $postTitle;
        }
        $html .= '</h4><ul>';

        //Categories
        $ids = array(0);
        if (!empty($this->settings_sitemap['wpms_sitemap_posts'])) {
            foreach ((array) $this->settings_sitemap['wpms_sitemap_posts'] as $k => $v) {
                if (!empty($v['post_id'])) {
                    $ids[] = $k;
                }
            }
        }

        $cats = get_categories(array('taxonomy' => 'category', 'hide_empty' => true));
        foreach ($cats as $cat) {
            if (in_array($cat->cat_ID, $this->settings_sitemap['wpms_category_link'])) {
                $cat_link = '<a href="' . esc_url(get_term_link($cat)) . '">' . esc_html($cat->cat_name) . '</a>';
            } else {
                $cat_link = $cat->cat_name;
            }
            $html .= '<li class="wpms_li_cate"><div class="cat_name">' . $cat_link . '</div></li>';

            if (!empty($this->settings_sitemap['wpms_sitemap_posts'])) {
                query_posts(array('post__in' => $ids, 'posts_per_page' => - 1, 'cat' => $cat->cat_ID));
                while (have_posts()) {
                    the_post();
                    if ((get_post_meta(get_the_ID(), '_yoast_wpseo_meta-robots-noindex', true) === '1'
                         && get_post_meta(get_the_ID(), '_yoast_wpseo_sitemap-include', true) !== 'always')
                        || (get_post_meta(get_the_ID(), '_yoast_wpseo_sitemap-include', true) === 'never')
                        || (get_post_meta(get_the_ID(), '_yoast_wpms_redirect', true) !== '')) {
                        continue;
                    }

                    $category = get_the_category();
                    // Only display a post link once, even if it's in multiple categories
                    if ((int) $category[0]->cat_ID === (int) $cat->cat_ID) {
                        $html .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
                    }
                }
                wp_reset_query();
            }
        }
        $html .= '</ul></div>';
        $html .= '<div class="holder holder_sitemaps_posts"></div>';

        return $html;
    }

    /**
     * Display sitemap pages by column in front-end
     *
     * @return string
     */
    public function displayPages()
    {
        $html = '';
        if (!empty($this->settings_sitemap['wpms_sitemap_pages'])) {
            $pageTitle = get_post_type_object('page');
            $pageTitle = $pageTitle->label;
            if (!empty($this->settings_sitemap['wpms_public_name_pages'])) {
                $pageTitle = $this->settings_sitemap['wpms_public_name_pages'];
            }
            $html     .= '<div id="sitemap_pages" class="wpms_sitemap_pages"><h4>' . $pageTitle . '</h4>
                <ul>';
            $pageInc  = '';
            $getPages = $this->getPagesSitemap();
            foreach ($getPages as $page) {
                if (isset($this->settings_sitemap['wpms_html_sitemap_page'])
                    && (int) $page->ID !== (int) $this->settings_sitemap['wpms_html_sitemap_page']) {
                    if ((get_post_meta($page->ID, '_yoast_wpseo_meta-robots-noindex', true) === '1'
                         && get_post_meta($page->ID, '_yoast_wpseo_sitemap-include', true) !== 'always')
                        || (get_post_meta($page->ID, '_yoast_wpseo_sitemap-include', true) === 'never')
                        || (get_post_meta($page->ID, '_yoast_wpms_redirect', true) !== '')) {
                        continue;
                    }
                    if ($pageInc === '') {
                        $pageInc = $page->ID;
                        continue;
                    }
                    $pageInc .= ', ' . $page->ID;
                }
            }

            if ($pageInc !== '') {
                $html .= wp_list_pages(
                    array(
                        'include'     => $pageInc,
                        'title_li'    => '',
                        'sort_column' => 'post_title',
                        'sort_order'  => 'ASC',
                        'echo'        => false
                    )
                );
            }

            $html .= '</ul></div>';
            $html .= '<div class="holder holder_sitemaps_pages"></div>';
        }
        return $html;
    }

    /**
     * Display sitemap customUrl by column in front-end
     *
     * @return string
     */
    public function displayCustomUrl()
    {
        $html  = '';
        $lists = $this->settings_sitemap['wpms_sitemap_customUrl'];
        $links = get_option('wpms_customUrls_list');
        if (!empty($lists)) {
            $html .= '<div id="sitemap_customUrl" class="wpms_sitemap_customUrl">';
            if (!empty($this->settings_sitemap['wpms_public_name_customUrl'])) {
                $publictitle = $this->settings_sitemap['wpms_public_name_customUrl'];
            } else {
                $publictitle = esc_html__('Custom Url', 'wp-meta-seo');
            }

            if (!empty($lists) && $lists !== '{}') {
                $html .= '<h4>' . $publictitle . '</h4>';
                $html .= '<ul>';
                foreach ($lists as $key => $list) {
                    if (!empty($links[$key])) {
                        $html .= '<li>';
                        $html .= '<a href="' . esc_url($links[$key]['link']) . '">' . esc_html($links[$key]['title']) . '</a>';
                        $html .= '</li>';
                    }
                }

                $html .= '</ul>';
            }

            $html .= '</div>';
            $html .= '<div class="holder holder_sitemaps_customUrl"></div>';
        }

        return $html;
    }

    /**
     * Render sitemap theme default
     *
     * @return string
     */
    public function sitemapThemeDefault()
    {
        $html = '';
        $html .= '<div id="wpms_sitemap"
         class="' . esc_attr('theme_default columns_' . $this->settings_sitemap['wpms_html_sitemap_column']) . '">';
        $arrs = array('displayPosts' => 'wpms_display_column_posts', 'displayPages' => 'wpms_display_column_pages');
        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            $arrs['displayCustomUrl'] = 'wpms_display_column_customUrl';
        }
        $checkColumn = array();
        for ($i = 1; $i <= (int) $this->settings_sitemap['wpms_html_sitemap_column']; $i ++) {
            $html .= '<div class="' . esc_attr('wpms_column wpms_column_' . $i) . '" style="width:33%;float:left;">';
            if ((int) $i === 1) {
                // Authors
                if ((int) $this->settings_sitemap['wpms_sitemap_author'] === 1) {
                    $html .= '<div id="sitemap_authors"><h3>' . esc_html__('Authors', 'wp-meta-seo') . '</h3>
                        <ul>';

                    $authEx = implode(
                        ', ',
                        get_users('orderby=nicename&meta_key=wpms_excludeauthorsitemap&meta_value=on')
                    );
                    $html   .= wp_list_authors(array('exclude_admin' => false, 'exclude' => $authEx, 'echo' => false));
                    $html   .= '</ul></div>';
                }
            }

            foreach ($arrs as $ar => $arr) {
                if (empty($this->settings_sitemap[$arr])) {
                    $this->settings_sitemap[$arr] = 1;
                }

                if (!in_array($ar, $checkColumn)) {
                    if ((int) $i === (int) $this->settings_sitemap[$arr]) {
                        $checkColumn[] = $ar;
                        $output        = $this->{$ar}();
                        $html          .= $output;
                    }
                }
            }
            // custom post type
            if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
                $custom_post_types = get_post_types(
                    array(
                        'public'              => true,
                        'exclude_from_search' => false,
                        '_builtin'            => false
                    )
                );
                if (!empty($custom_post_types)) {
                    foreach ($custom_post_types as $post_type => $label) {
                        if (!empty($this->settings_sitemap['wpms_display_column_' . $post_type])
                            && (int) $i === (int) $this->settings_sitemap['wpms_display_column_' . $post_type]) {
                            //=====================================================================================
                            if (isset($this->settings_sitemap['wpms_public_name_' . $post_type])
                                && $this->settings_sitemap['wpms_public_name_' . $post_type] !== '') {
                                $postTitle = $this->settings_sitemap['wpms_public_name_' . $post_type];
                            } else {
                                $postTitle = get_post_type_object($post_type);
                                $postTitle = $postTitle->label;
                            }

                            global $wpdb;
                            if ($post_type === 'product') {
                                $taxonomy_objects = array('product_cat');
                            } else {
                                $taxonomy_objects = get_object_taxonomies($post_type, 'names');
                            }
                            $ids = array(0);
                            if (!empty($this->settings_sitemap['wpms_sitemap_' . $post_type])) {
                                $html .= '<div id="sitemap_' . $post_type . '" class="wpms_sitemap_' . $post_type . '"><h4>';
                                $html .= esc_html($postTitle);
                                $html .= '</h4><ul>';
                                foreach ((array) $this->settings_sitemap['wpms_sitemap_' . $post_type] as $k => $v) {
                                    if (!empty($v['post_id'])) {
                                        $ids[] = (int) $v['post_id'];
                                    }
                                }
                            }

                            $list_links = array();
                            if (!empty($taxonomy_objects)) {
                                foreach ($taxonomy_objects as $taxo) {
                                    $categorys = get_categories(array('hide_empty' => true, 'taxonomy' => $taxo));
                                    foreach ($categorys as $cat) {
                                        $results = $wpdb->get_results($wpdb->prepare('SELECT p.ID as ID,p.post_title as post_title   
FROM ' . $wpdb->posts . ' AS p
INNER JOIN ' . $wpdb->term_relationships . ' AS tr ON (p.ID = tr.object_id)
INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
INNER JOIN ' . $wpdb->terms . ' AS t ON (t.term_id = tt.term_id)
WHERE   p.post_status = %s 
    AND p.post_type = %s
    AND tt.taxonomy = %s AND t.slug = %s AND ID IN (' . implode(',', esc_sql($ids)) . ')   
ORDER BY p.post_date DESC', array('publish', $post_type, $taxo, $cat->slug)));
                                        if (!empty($results)) {
                                            if (in_array($cat->cat_ID, $this->settings_sitemap['wpms_category_link'])) {
                                                $cat_link = '<a href="' . esc_url(get_term_link($cat)) . '">
                                            ' . esc_html($cat->cat_name) . '</a>';
                                                $html     .= '<li class="wpms_li_cate wpms_li_cate">';
                                                $html     .= '<div class="cat_name">' . $cat_link . '</div>';
                                                $html     .= '</li>';
                                            } else {
                                                $cat_link = esc_html($cat->cat_name);
                                                if (!empty($this->settings_sitemap['wpms_sitemap_' . $post_type])) {
                                                    $html .= '<li class="wpms_li_cate wpms_li_cate">';
                                                    $html .= '<div class="cat_name">' . $cat_link . '</div>';
                                                    $html .= '</li>';
                                                }
                                            }

                                            if (!empty($this->settings_sitemap['wpms_sitemap_' . $post_type])) {
                                                foreach ($results as $p) {
                                                    $i    = $cat->cat_ID . '-' . $p->ID;
                                                    $link = get_permalink($p->ID);
                                                    if (!in_array($link, $list_links)) {
                                                        $list_links[] = $link;
                                                        $html         .= '<li><a href="' . esc_url($link) . '">' . esc_html($p->post_title) . '</a></li>';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                $results = $wpdb->get_results($wpdb->prepare('SELECT ID, post_title FROM ' . $wpdb->posts . ' WHERE   post_status = %s AND post_type = %s AND ID IN (' . implode(',', esc_sql($ids)) . ') ORDER BY post_date DESC', array(
                                    'publish',
                                    $post_type
                                )));
                                if (!empty($results)) {
                                    if (!empty($this->settings_sitemap['wpms_sitemap_' . $post_type])) {
                                        $html .= '<ul>';
                                        foreach ($results as $p) {
                                            $link = get_permalink($p->ID);
                                            if (!in_array($link, $list_links)) {
                                                $list_links[] = $link;
                                                $html         .= '<li><a href="' . esc_url(get_permalink($p->ID)) . '">' . esc_html($p->post_title) . '</a></li>';
                                            }
                                        }
                                        $html .= '</ul>';
                                    }
                                }
                            }

                            if (!empty($this->settings_sitemap['wpms_sitemap_' . $post_type])) {
                                $html .= '</ul></div>';
                            }
                            $html .= '<div class="holder holder_sitemaps_' . $post_type . '"></div>';
                            //======================================================================================
                        }
                    }
                }
            }
            // ====================

            $ids_menu   = array(0);
            $check_menu = array();
            $terms      = get_terms(array(
                'taxonomy'   => 'nav_menu',
                'hide_empty' => true,
                'orderby'    => 'term_id',
                'order'      => 'ASC'
            ));
            foreach ($terms as $term) {
                $list_submenu_id = get_objects_in_term($term->term_id, 'nav_menu');
                $ids_menu        = array_merge($ids_menu, $list_submenu_id);
            }

            if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                $this->settings_sitemap['wpms_sitemap_menus'] = $ids_menu;
            }

            if (!empty($this->settings_sitemap['wpms_sitemap_menus'])) {
                if (!empty($terms)) {
                    foreach ($terms as $term) {
                        if (!in_array('sitemap_menus_' . $term->term_id, $check_menu)) {
                            if (empty($this->settings_sitemap['wpms_display_column_menus'][$term->term_id])) {
                                $this->settings_sitemap['wpms_display_column_menus'][$term->term_id] = 1;
                            }

                            if ((int) $i === (int) $this->settings_sitemap['wpms_display_column_menus'][$term->term_id]) {
                                $check_menu[] = 'sitemap_menus_' . $term->term_id;
                                $html         .= '<div id="' . esc_attr('sitemap_menus_' . $term->term_id) . '" class="wpms_sitemap_menus">';
                                $viewmenu     = $this->viewMenusFrontend($term, $ids_menu);
                                $html         .= $viewmenu;

                                $html .= '</div>';
                                $html .= '<div class="' . esc_attr('holder holder_sitemaps_menus_' . $term->term_id) . '"></div>';
                            }
                        }
                    }
                }
            }

            $html .= '</div>';
        }

        // ==============================================================================

        $html .= '</div>';
        $html .= '<div class="wpms_clearRow"></div>';
        return $html;
    }

    /**
     * Display html sitemap in front-end
     *
     * @return array|string
     */
    public function sitemapShortcode()
    {
        $html = '';
        // include style
        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            $theme = $this->settings_sitemap['wpms_html_sitemap_theme'];
        } else {
            $theme = 'default';
        }

        $custom_post_types = get_post_types(
            array(
                'public'              => true,
                'exclude_from_search' => false,
                '_builtin'            => false
            )
        );

        if ($theme === 'default') {
            wp_enqueue_style(
                'html-sitemap',
                plugins_url('assets/css/html_sitemap.css', dirname(__FILE__)),
                array(),
                WPMSEO_VERSION
            );

            $html = $this->sitemapThemeDefault();
        } elseif ($theme === 'tab') {
            wp_enqueue_script(
                'wpms_materialize_js',
                plugins_url('assets/js/materialize/materialize.min.js', dirname(__FILE__)),
                array('jquery'),
                WPMSEO_VERSION,
                true
            );
            wp_enqueue_script(
                'wpms_tabs_js',
                plugins_url('assets/js/wpms-tabs.js', dirname(__FILE__)),
                array('jquery'),
                WPMSEO_VERSION,
                true
            );
            wp_enqueue_style(
                'wpms_materialize_style',
                plugins_url('assets/css/materialize/materialize_frontend_tab_theme.css', dirname(__FILE__)),
                array(),
                WPMSEO_VERSION
            );
            echo '<div id="wpms_sitemap" class="theme_tab">';
            require_once(WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/sitemaps/theme/tab/menu_bar.php');
            require_once(WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/sitemaps/theme/tab/source_posts.php');
            require_once(WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/sitemaps/theme/tab/source_pages.php');
            if (!empty($this->settings_sitemap['wpms_sitemap_customUrl'])) {
                require_once(WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/sitemaps/theme/tab/source_urlcustom.php');
            }
            // source menu
            $ids_menu   = array(0);
            $check_menu = array();
            $terms      = get_terms(array('taxonomy' => 'nav_menu', 'hide_empty' => true));
            foreach ($terms as $term) {
                $list_submenu_id = get_objects_in_term($term->term_id, 'nav_menu');
                $ids_menu        = array_merge($ids_menu, $list_submenu_id);
            }

            if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                $this->settings_sitemap['wpms_sitemap_menus'] = $ids_menu;
            }

            if (!empty($this->settings_sitemap['wpms_sitemap_menus'])) {
                if (!empty($terms)) {
                    echo '<div id="menu_source_menus">';
                    foreach ($terms as $term) {
                        if (!in_array('sitemap_menus_' . $term->term_id, $check_menu)) {
                            $check_menu[] = 'sitemap_menus_' . $term->term_id;
                            echo '<div id="' . esc_attr('sitemap_menus_' . $term->term_id) . '" class="wpms_sitemap_menus">';
                            $viewmenu = $this->viewMenusFrontend($term, $ids_menu);
                            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in the method viewMenusFrontend
                            echo $viewmenu;

                            echo '</div>';
                            echo '<div class="' . esc_attr('holder holder_sitemaps_menus_' . $term->term_id) . '"></div>';
                        }
                    }
                    echo '</div>';
                }
            }
            echo '</div>';
        } elseif ($theme === 'accordions') {
            wp_enqueue_script(
                'wpms_materialize_js',
                plugins_url('assets/js/materialize/materialize.min.js', dirname(__FILE__)),
                array('jquery'),
                WPMSEO_VERSION,
                true
            );
            wp_enqueue_style(
                'wpms_materialize_style',
                plugins_url('assets/css/materialize/materialize_frontend_accordions_theme.css', dirname(__FILE__)),
                array(),
                WPMSEO_VERSION
            );
            echo '<div id="wpms_sitemap" class="theme_accordions">';
            echo '<ul class="collapsible wpms_collapsible" data-collapsible="accordion">';
            $arrays = array();
            if (!empty($this->settings_sitemap['wpms_sitemap_menus'])) {
                $arrays['wpms_display_order_menus']
                    = WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/sitemaps/theme/accordions/source_menu.php';
            }
            if (!empty($this->settings_sitemap['wpms_sitemap_posts'])) {
                $arrays['wpms_display_order_posts']
                    = WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/sitemaps/theme/accordions/source_posts.php';
            }
            if (!empty($this->settings_sitemap['wpms_sitemap_pages'])) {
                $arrays['wpms_display_order_pages']
                    = WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/sitemaps/theme/accordions/source_pages.php';
            }
            if (!empty($this->settings_sitemap['wpms_sitemap_customUrl'])) {
                $arrays['wpms_display_order_urls']
                    = WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/sitemaps/theme/accordions/source_urlcustom.php';
            }

            for ($i = 1; $i <= 4; $i ++) {
                foreach ($arrays as $key => $item) {
                    if ((int) $this->settings_sitemap[$key] === (int) $i) {
                        require_once($item);
                    }
                }
            }

            echo '</ul>';
            echo '</div>';
        }

        return $html;
    }

    /**
     * Add wpms_html_sitemap shortcode in content
     *
     * @param string $content Sitemap content
     *
     * @return string
     */
    public function sitemapInContent($content)
    {
        global $wpdb;
        $sitemap_page = $wpdb->get_row($wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'posts
                 WHERE post_title = %s AND post_excerpt = %s AND post_type = %s',
            array(
                'WPMS HTML Sitemap',
                'metaseo_html_sitemap',
                'page'
            )
        ));

        if (empty($this->settings_sitemap['wpms_html_sitemap_page']) && !empty($sitemap_page)) {
            $this->settings_sitemap['wpms_html_sitemap_page'] = $sitemap_page->ID;
        }

        if (!empty($this->settings_sitemap['wpms_html_sitemap_page'])
            && is_page($this->settings_sitemap['wpms_html_sitemap_page'])) {
            $sitemap = '[wpms_html_sitemap]';
            switch ($this->settings_sitemap['wpms_html_sitemap_position']) {
                case 'after':
                    $content .= $sitemap;
                    break;
                case 'before':
                    $content = $sitemap . $content;
                    break;
                case 'replace':
                    $content = $sitemap;
                    break;
                default:
                    $content .= $sitemap;
            }
        }
        return $content;
    }

    /**
     * Get all menu
     *
     * @return array
     */
    public function getAllMenus()
    {
        $settings_sitemap = get_option('_metaseo_settings_sitemap');
        $post_types       = get_post_types('', 'names');
        unset($post_types['revision']);
        unset($post_types['attachment']);
        $ids_posts_custom = array(0);
        $ids_categories   = array();

        if (empty($settings_sitemap['wpms_check_firstsave'])) {
            $args       = array(
                'posts_per_page' => - 1,
                'post_type'      => 'nav_menu_item',
                'post_status'    => 'publish'
            );
            $query      = new WP_Query($args);
            $posts_menu = $query->get_posts();
            foreach ($posts_menu as $k => $v) {
                $type                = get_post_meta($v->ID, '_menu_item_type', true);
                $post_meta_object_id = get_post_meta($v->ID, '_menu_item_object_id', true);
                if ($type !== 'taxonomy') {
                    $ids_posts_custom[] = $post_meta_object_id;
                } else {
                    $ids_categories[] = $post_meta_object_id;
                }
            }
        } else {
            if (!empty($settings_sitemap['wpms_sitemap_menus'])) {
                foreach ($settings_sitemap['wpms_sitemap_menus'] as $k => $v) {
                    if (!empty($v['menu_id'])) {
                        $type                = get_post_meta($k, '_menu_item_type', true);
                        $post_meta_object_id = get_post_meta($k, '_menu_item_object_id', true);
                        if ($type !== 'taxonomy') {
                            $ids_posts_custom[] = $post_meta_object_id;
                        } else {
                            $ids_categories[] = $post_meta_object_id;
                        }
                    }
                }
            }
        }

        $args              = array(
            'posts_per_page' => - 1,
            'post_type'      => $post_types,
            'post__in'       => $ids_posts_custom,
            'post_status'    => 'publish'
        );
        $query             = new WP_Query($args);
        $menus_post_custom = $query->get_posts();
        return array('posts_custom' => $menus_post_custom, 'categories' => $ids_categories);
    }

    /**
     * Get posts selected in sitemap setting
     *
     * @return array
     */
    public function getPostsSitemap()
    {
        $post_types       = $this->getPostTypes();
        $ids              = array(0);
        $settings_sitemap = get_option('_metaseo_settings_sitemap');
        if (!empty($settings_sitemap['wpms_sitemap_posts'])) {
            foreach ((array) $settings_sitemap['wpms_sitemap_posts'] as $k => $v) {
                if (!empty($v['post_id'])) {
                    $ids[] = $k;
                }
            }
        }

        $args  = array(
            'posts_per_page' => - 1,
            'post_type'      => $post_types,
            'post__in'       => $ids,
            'post_status'    => 'publish'
        );
        $query = new WP_Query($args);
        $posts = $query->get_posts();
        return $posts;
    }

    /**
     * Get a list of all registered post type objects.
     *
     * @return array
     */
    public function getPostTypes()
    {
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        unset($post_types['attachment']);
        unset($post_types['page']);
        return $post_types;
    }

    /**
     * Get pages selected in sitemap setting
     *
     * @return array
     */
    public function getPagesSitemap()
    {
        $ids              = array(0);
        $settings_sitemap = get_option('_metaseo_settings_sitemap');
        if (!empty($settings_sitemap['wpms_sitemap_pages'])) {
            foreach ($settings_sitemap['wpms_sitemap_pages'] as $k => $v) {
                if (!empty($v['post_id'])) {
                    $ids[] = $k;
                }
            }
        }

        $args  = array(
            'posts_per_page' => - 1,
            'post_type'      => 'page',
            'post__in'       => $ids,
            'post_status'    => 'publish'
        );
        $query = new WP_Query($args);
        $pages = $query->get_posts();
        return $pages;
    }

    /**
     * Get pages
     *
     * @return array|null|object
     */
    public function getPages()
    {
        global $wpdb;
        $pages = $wpdb->get_results($wpdb->prepare('SELECT ID,post_title FROM ' . $wpdb->posts . ' WHERE
 post_status = %s AND post_type = %s ORDER BY post_date DESC', array('publish', 'page')));
        return $pages;
    }

    /**
     * Get posts by category
     *
     * @return array
     */
    public function getPosts()
    {
        $posts     = array();
        $taxo      = 'category';
        $categorys = get_categories(array('hide_empty' => true, 'taxonomy' => $taxo));
        global $wpdb;
        foreach ($categorys as $cat) {
            $count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(p.ID)    
FROM ' . $wpdb->posts . ' AS p
INNER JOIN ' . $wpdb->term_relationships . ' AS tr ON (p.ID = tr.object_id)
INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
INNER JOIN ' . $wpdb->terms . ' AS t ON (t.term_id = tt.term_id)
WHERE   p.post_status = %s 
    AND p.post_type = %s
    AND tt.taxonomy = %s AND t.slug=%s  
ORDER BY p.post_date DESC', array('publish', 'post', $taxo, $cat->slug)));

            $results       = $wpdb->get_results($wpdb->prepare('SELECT p.ID as ID,p.post_title as post_title   
FROM ' . $wpdb->posts . ' AS p
INNER JOIN ' . $wpdb->term_relationships . ' AS tr ON (p.ID = tr.object_id)
INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
INNER JOIN ' . $wpdb->terms . ' AS t ON (t.term_id = tt.term_id)
WHERE   p.post_status = %s 
    AND p.post_type = %s
    AND tt.taxonomy = %s AND t.slug=%s  
ORDER BY p.post_date DESC LIMIT 10', array('publish', 'post', $taxo, $cat->slug)));
            $obj           = new StdClass();
            $obj->cat_name = $cat->cat_name;
            $obj->cat_ID   = $cat->cat_ID;
            $obj->taxo     = $taxo;
            $obj->slug     = $cat->slug;
            $obj->results  = array();
            if (!empty($results)) {
                $obj->results = $results;
            }
            $obj->count_posts = $count;
            $posts[]          = $obj;
        }

        return $posts;
    }

    /**
     * Get posts by category
     *
     * @param string $post_type Post type
     *
     * @return array
     */
    public function getPostsCustom($post_type)
    {
        global $wpdb;
        $posts = array();

        $results = $wpdb->get_results($wpdb->prepare('SELECT p.ID as ID,p.post_title as post_title   
FROM ' . $wpdb->posts . ' AS p
WHERE   p.post_status = "publish" AND p.post_type = %s   
ORDER BY p.post_date DESC', array($post_type)));
        if (!empty($results)) {
            $obj = new StdClass();
            $obj->cat_name = '';
            $obj->cat_ID = '';
            $obj->taxo = '';
            $obj->slug = '';
            $obj->results = $results;
            $posts[] = $obj;
        }

        return $posts;
    }

    /**
     * Display sitemap menu in front-end
     *
     * @param object $term     Term
     * @param array  $ids_menu List menu id
     *
     * @return string
     */
    public function viewMenusFrontend($term, $ids_menu)
    {
        $html       = '';
        $list_menus = array();
        if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
            $list_menus = $ids_menu;
        } else {
            if (!empty($this->settings_sitemap['wpms_sitemap_menus'])) {
                foreach ($this->settings_sitemap['wpms_sitemap_menus'] as $k => $v) {
                    $list_menus[] = $k;
                }
            }
        }

        $args = array(
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'posts_per_page' => - 1,
            'post_type'      => 'nav_menu_item',
            'post_status'    => 'any',
            'post__in'       => $list_menus,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'nav_menu',
                    'field'    => 'slug',
                    'terms'    => $term->slug,
                ),
            ),
        );

        $query    = new WP_Query($args);
        $submenus = $query->get_posts();
        if (!empty($submenus)) {
            $html .= '<h4>' . esc_html($term->name) . '</h4>';
            $html .= '<ul class="wpms_frontend_menus_sitemap">';
            foreach ($submenus as $menu) {
                $type                   = get_post_meta($menu->ID, '_menu_item_type', true);
                $type_menu              = get_post_meta($menu->ID, '_menu_item_object', true);
                $id_menu                = get_post_meta($menu->ID, '_menu_item_object_id', true);
                $this->level[$menu->ID] = 0;
                $level                  = $this->countParent($menu->ID);
                if ($type === 'taxonomy') {
                    $post_submenu = get_post($menu->ID);
                    $title        = $post_submenu->post_title;
                    if (empty($title)) {
                        $term  = get_term($id_menu, $type_menu);
                        if (empty($term->name)) {
                            continue;
                        }
                        $title = $term->name;
                    }
                } else {
                    $post  = get_post($menu->ID);
                    $title = $post->post_title;
                    if (empty($title)) {
                        $post_submenu = get_post($id_menu);
                        $title        = $post_submenu->post_title;
                    }
                }
                $type      = get_post_meta($menu->ID, '_menu_item_type', true);
                $permalink = $this->getPermalinkSitemap($type, $id_menu);
                $margin    = $level * 10;
                $style     = '';
                if ((int) $level !== 0) {
                    $style = 'style="' . esc_attr('margin-left:' . $margin . 'px') . '"';
                }
                $html .= '<li class="' . esc_attr('wpms_menu_level_' . $level) . '" ' . $style . '>';
                $html .= '<a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a>';
                $html .= '</li>';
            }

            $html .= '</ul>';
        }
        return $html;
    }

    /**
     * Get count parent for a menu
     *
     * @param integer $menuID ID of menu
     *
     * @return integer
     */
    public function countParent($menuID)
    {
        $parent = get_post_meta($menuID, '_menu_item_menu_item_parent', true);
        if ((!empty($this->settings_sitemap['wpms_sitemap_menus'][$parent]) || !empty($this->settings_sitemap['wpms_sitemap_menus']->{$parent})) && !empty($parent)) {
            $this->level[$menuID] += 1;
            $this->loopParent($parent, $menuID);
        } else {
            $this->loopParent($parent, $menuID);
        }

        return (int) $this->level[$menuID];
    }

    /**
     * Get level list menu
     *
     * @param integer $menuID     Current menu id
     * @param integer $menuIDroot Root menu id
     *
     * @return void
     */
    public function loopParent($menuID, $menuIDroot)
    {
        $parent   = get_post_meta($menuID, '_menu_item_menu_item_parent', true);
        $parent_1 = get_post_meta($parent, '_menu_item_menu_item_parent', true);
        if ((!empty($this->settings_sitemap['wpms_sitemap_menus'][$parent]) && !empty($parent))
            || (!empty($parent_1) && !empty($parent))) {
            $this->level[$menuIDroot] += 1;
            $this->loopParent($parent, $menuIDroot);
        }
    }

    /**
     * Display list menu in sitemap settings
     *
     * @param object $term Current menu
     *
     * @return string
     */
    public function viewMenus($term)
    {
        $list_submenu_id = get_objects_in_term($term->term_id, 'nav_menu');
        $args            = array(
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'posts_per_page' => - 1,
            'post_type'      => 'nav_menu_item',
            'post_status'    => 'any',
            'post__in'       => $list_submenu_id,
            'meta_key'       => '_menu_item_menu_item_parent',
            'meta_value'     => 0
        );

        $query    = new WP_Query($args);
        $submenus = $query->get_posts();
        ?>
        <div class="wpms_row_full">
            <div class="ju-settings-option wpms_row" style="margin-top: 20px">
                <div class="wpms_row_full">
                    <label class="ju-setting-label text wpms-uppercase"
                           data-alt="<?php echo esc_attr('Include all elements in the sitemap', 'wp-meta-seo') ?>">
                        <?php echo esc_html($term->name) ?>
                    </label>
                    <div class="ju-switch-button">
                        <label class="switch">
                            <?php if (isset($this->settings_sitemap['check_all_menu_items']) && in_array($term->term_id, $this->settings_sitemap['check_all_menu_items'])) : ?>
                                <input class="xm_cb_all check_all_menu_items" checked
                                       data-category="<?php echo esc_attr('nav_menu' . $term->slug) ?>"
                                       value="<?php echo esc_attr($term->term_id) ?>"
                                       id="<?php echo esc_attr('xm_cb_all_' . $term->slug) ?>" type="checkbox" ?>>
                            <?php else : ?>
                                <input class="xm_cb_all check_all_menu_items"
                                       data-category="<?php echo esc_attr('nav_menu' . $term->slug) ?>"
                                       value="<?php echo esc_attr($term->term_id) ?>"
                                       id="<?php echo esc_attr('xm_cb_all_' . $term->slug) ?>" type="checkbox" ?>>
                            <?php endif; ?>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="ju-settings-option wpms_xmp_custom_column wpms_row wpms_right m-r-0" style="margin-top: 20px">
                <div class="wpms_row_full">
                    <label class="ju-setting-label wpms_left"
                           data-alt="<?php echo esc_attr('Column selection if you’re using the HTML sitemap', 'wp-meta-seo') ?>">
                        <?php esc_html_e('HTML Sitemap column', 'wp-meta-seo') ?>
                    </label>
                    <div class="ju-switch-button">
                        <label>
                            <select class="wpms_display_column wpms_display_column_menus wpms-large-input m-r-10"
                                    data-menu_id="<?php echo esc_attr($term->term_id) ?>">
                                <?php
                                for ($i = 1; $i <= $this->settings_sitemap['wpms_html_sitemap_column']; $i ++) {
                                    if (isset($this->settings_sitemap['wpms_display_column_menus'][$term->term_id])
                                        && (int) $this->settings_sitemap['wpms_display_column_menus'][$term->term_id] === (int) $i) {
                                        echo '<option selected value="' . esc_attr($i) . '">' . esc_html($this->columns[$i]) . '</option>';
                                    } else {
                                        echo '<option value="' . esc_attr($i) . '">' . esc_html($this->columns[$i]) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <?php
        foreach ($submenus as $menu) {
            if (empty($this->settings_sitemap['wpms_sitemap_menus'][$menu->ID]['frequency'])) {
                $this->settings_sitemap['wpms_sitemap_menus'][$menu->ID]['frequency'] = 'monthly';
            }
            if (empty($this->settings_sitemap['wpms_sitemap_menus'][$menu->ID]['priority'])) {
                $this->settings_sitemap['wpms_sitemap_menus'][$menu->ID]['priority'] = '1.0';
            }
            $slpr = $this->viewPriority(
                'priority_menu_' . $menu->ID,
                '_metaseo_settings_sitemap[wpms_sitemap_menus][' . $menu->ID . '][priority]',
                $this->settings_sitemap['wpms_sitemap_menus'][$menu->ID]['priority']
            );
            $slfr = $this->viewFrequency(
                'frequency_menu_' . $menu->ID,
                '_metaseo_settings_sitemap[wpms_sitemap_menus][' . $menu->ID . '][frequency]',
                $this->settings_sitemap['wpms_sitemap_menus'][$menu->ID]['frequency']
            );

            $type      = get_post_meta($menu->ID, '_menu_item_type', true);
            $type_menu = get_post_meta($menu->ID, '_menu_item_object', true);
            $id_menu   = get_post_meta($menu->ID, '_menu_item_object_id', true);
            if ($type === 'taxonomy') {
                $post_submenu = get_post($menu->ID);
                $title        = $post_submenu->post_title;
                if (empty($title)) {
                    $term_menu = get_term($id_menu, $type_menu);
                    $title     = $term_menu->name;
                }
            } else {
                $post  = get_post($menu->ID);
                $title = $post->post_title;
                if (empty($title)) {
                    $post_submenu = get_post($id_menu);
                    $title        = $post_submenu->post_title;
                }
            }
            $level = 1;
            echo '<div class="wpms_row wpms_row_record">';
            $check_type = get_post_meta($menu->ID, '_menu_item_object', true);
            $permalink  = $this->getPermalinkSitemap($check_type, $id_menu);
            echo '<div style="float:left;line-height:30px">';
            if (empty($this->settings_sitemap['wpms_check_firstsave'])) {
                echo '<input class="wpms_sitemap_input_link checked"
                 type="hidden" data-type="menu" value="' . esc_attr($permalink) . '">';
                echo '<div class="pure-checkbox">';
                echo '<input class="' . esc_attr('cb_sitemaps_menu wpms_xmap_menu nav_menu' . $term->slug) . '"
                 id="' . esc_attr('wpms_sitemap_menus_' . $menu->ID) . '" type="checkbox"
                  name="' . esc_attr('_metaseo_settings_sitemap[wpms_sitemap_menus][' . $menu->ID . '][menu_id]') . '"
                   value="' . esc_attr($menu->ID) . '" checked>';
                echo '<label for="' . esc_attr('wpms_sitemap_menus_' . $menu->ID) . '" class="wpms-text">' . esc_html($title) . '</label>';
                echo '</div>';
            } else {
                if (isset($this->settings_sitemap['wpms_sitemap_menus'][$menu->ID]['menu_id'])
                    && (int) $this->settings_sitemap['wpms_sitemap_menus'][$menu->ID]['menu_id'] === (int) $menu->ID) {
                    echo '<input class="wpms_sitemap_input_link checked"
                     type="hidden" data-type="menu" value="' . esc_url($permalink) . '">';
                    echo '<div class="pure-checkbox">';
                    echo '<input class="' . esc_attr('cb_sitemaps_menu wpms_xmap_menu nav_menu' . $term->slug) . '"
                     id="' . esc_attr('wpms_sitemap_menus_' . $menu->ID) . '" type="checkbox"
                      name="' . esc_attr('_metaseo_settings_sitemap[wpms_sitemap_menus][' . $menu->ID . '][menu_id]') . '"
                       value="' . esc_attr($menu->ID) . '" checked>';
                    echo '<label for="' . esc_attr('wpms_sitemap_menus_' . $menu->ID) . '" class="wpms-text">' . esc_html($title) . '</label>';
                    echo '</div>';
                } else {
                    echo '<input class="wpms_sitemap_input_link" type="hidden" data-type="menu"
                     value="' . esc_url($permalink) . '">';
                    echo '<div class="pure-checkbox">';
                    echo '<input class="' . esc_attr('cb_sitemaps_menu wpms_xmap_menu nav_menu' . $term->slug) . '"
                     id="' . esc_attr('wpms_sitemap_menus_' . $menu->ID) . '" type="checkbox"
                      name="' . esc_attr('_metaseo_settings_sitemap[wpms_sitemap_menus][' . $menu->ID . '][menu_id]') . '"
                       value="' . esc_attr($menu->ID) . '">';
                    echo '<label for="' . esc_attr('wpms_sitemap_menus_' . $menu->ID) . '" class="wpms-text">' . esc_html($title) . '</label>';
                    echo '</div>';
                }
            }

            echo '</div>';
            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in the method MetaSeoSitemap::viewFrequency, MetaSeoSitemap::viewPriority
            echo '<div class="wpms_right">' . $slpr . $slfr . '</div>';
            echo '</div>';
            $this->loop($menu->ID, $level + 1, $this->settings_sitemap, $term);
        }

        return $this->html;
    }

    /**
     * Display list menu in sitemap settings
     *
     * @param integer $menuID           ID of menu
     * @param integer $level            Level of menu
     * @param array   $settings_sitemap All settings
     * @param object  $term             Current menu
     *
     * @return void
     */
    public function loop($menuID, $level, $settings_sitemap, $term)
    {
        $args     = array(
            'post_type'      => 'nav_menu_item',
            'posts_per_page' => - 1,
            'meta_key'       => '_menu_item_menu_item_parent',
            'meta_value'     => $menuID,
            'orderby'        => 'menu_order',
            'order'          => 'ASC'
        );
        $query    = new WP_Query($args);
        $submenus = $query->get_posts();

        if (!empty($submenus)) {
            foreach ($submenus as $submenu) {
                $type       = get_post_meta($submenu->ID, '_menu_item_type', true);
                $type_menu  = get_post_meta($submenu->ID, '_menu_item_object', true);
                $post_subid = get_post_meta($submenu->ID, '_menu_item_object_id', true);
                if (empty($settings_sitemap['wpms_sitemap_menus'][$submenu->ID]['frequency'])) {
                    $settings_sitemap['wpms_sitemap_menus'][$submenu->ID]['frequency'] = 'monthly';
                }
                if (empty($settings_sitemap['wpms_sitemap_menus'][$submenu->ID]['priority'])) {
                    $settings_sitemap['wpms_sitemap_menus'][$submenu->ID]['priority'] = '1.0';
                }

                if ($type === 'taxonomy') {
                    $post_submenu = get_post($submenu->ID);
                    $title        = $post_submenu->post_title;
                    if (empty($title)) {
                        $term_sub = get_term($post_subid, $type_menu);
                        $title    = $term_sub->name;
                    }
                } else {
                    $post_submenu = get_post($submenu->ID);
                    $title        = $post_submenu->post_title;
                    if (empty($title)) {
                        $post_submenu = get_post($post_subid);
                        $title        = $post_submenu->post_title;
                    }
                }

                $space = '';
                for ($i = 1; $i <= $level * 3; $i ++) {
                    $space .= '&nbsp;';
                }
                $slpr = $this->viewPriority(
                    'priority_menu_' . $submenu->ID,
                    '_metaseo_settings_sitemap[wpms_sitemap_menus][' . $submenu->post_id . '][priority]',
                    $settings_sitemap['wpms_sitemap_menus'][$submenu->ID]['priority']
                );
                $slfr = $this->viewFrequency(
                    'frequency_menu_' . $submenu->ID,
                    '_metaseo_settings_sitemap[wpms_sitemap_menus][' . $submenu->post_id . '][frequency]',
                    $settings_sitemap['wpms_sitemap_menus'][$submenu->ID]['frequency']
                );

                if (empty($settings_sitemap['wpms_check_firstsave'])) {
                    $checkbox = $space . '<input id="' . esc_attr('wpms_sitemap_menus_' . $submenu->ID) . '"
                     class="' . esc_attr('cb_sitemaps_menu wpms_xmap_menu nav_menu' . $term->slug) . '"
                      checked name="' . esc_attr('_metaseo_settings_sitemap[wpms_sitemap_menus][' . $submenu->ID . '][menu_id]') . '"
                       type="checkbox" value="' . $submenu->ID . '">';
                } else {
                    if (isset($settings_sitemap['wpms_sitemap_menus'][$submenu->ID]['menu_id'])
                        && (int) $settings_sitemap['wpms_sitemap_menus'][$submenu->ID]['menu_id'] === (int) $submenu->ID) {
                        $checkbox = $space . '<input id="' . esc_attr('wpms_sitemap_menus_' . $submenu->ID) . '"
                         class="' . esc_attr('cb_sitemaps_menu wpms_xmap_menu nav_menu' . $term->slug) . '" checked
                          name="' . esc_attr('_metaseo_settings_sitemap[wpms_sitemap_menus][' . $submenu->ID . '][menu_id]') . '"
                           type="checkbox" value="' . esc_attr($submenu->ID) . '">';
                    } else {
                        $checkbox = $space . '<input id="' . esc_attr('wpms_sitemap_menus_' . $submenu->ID) . '"
                         class="' . esc_attr('cb_sitemaps_menu wpms_xmap_menu nav_menu' . $term->slug) . '"
                          name="' . esc_attr('_metaseo_settings_sitemap[wpms_sitemap_menus][' . $submenu->ID . '][menu_id]') . '"
                           type="checkbox" value="' . esc_attr($submenu->ID) . '">';
                    }
                }

                echo '<div class="wpms_row wpms_row_record">';
                echo '<div style="float:left;line-height:30px">';
                echo '<div class="pure-checkbox">';
                // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in this method
                echo $checkbox;
                echo '<label for="' . esc_attr('wpms_sitemap_menus_' . $submenu->ID) . '" class="wpms-text">' . esc_html($title) . '</label>';
                echo '</div>';
                echo '</div>';
                // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in the method MetaSeoSitemap::viewFrequency, MetaSeoSitemap::viewPriority
                echo '<div class="wpms_right">' . $slpr . $slfr . '</div>';
                echo '</div>';
                $this->loop($submenu->ID, $level + 1, $settings_sitemap, $term);
            }
        }
    }

    /**
     * Ajax generate sitemap to xml file
     *
     * @param string $type Type
     *
     * @return void
     */
    public function regenerateSitemaps($type = 'ajax')
    {
        $wpms_url_robot = get_home_path() . 'robots.txt';
        $wpms_url_home  = site_url('/');
        $this->getSitemapSettings();
        $this->createSitemap($this->wpms_sitemap_name);
        if ((int) $this->settings_sitemap['wpms_sitemap_root'] === 1) {
            $this->createSitemap($this->wpms_sitemap_default_name);
        }

        if (isset($this->settings_sitemap['wpms_sitemap_add']) && (int) $this->settings_sitemap['wpms_sitemap_add'] === 1) {
            if (!file_exists($wpms_url_robot) && !is_multisite()) {
                ob_start();
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting -- Turn off all error reporting to write file
                error_reporting(0);
                do_robots();
                $robots_content = ob_get_clean();

                $f = fopen($wpms_url_robot, 'x');
                fwrite($f, $robots_content);
            }
        }

        if (file_exists($wpms_url_robot) && !is_multisite()) {
            if (!is_writable($wpms_url_robot)) {
                chmod($wpms_url_robot, 0755);
            }

            if (is_writable($wpms_url_robot)) {
                $file_content = file_get_contents($wpms_url_robot);
                if (isset($this->settings_sitemap['wpms_sitemap_add'])
                    && (int) $this->settings_sitemap['wpms_sitemap_add'] === 1
                    && !preg_match('|Sitemap: ' . $wpms_url_home . $this->wpms_sitemap_name . '|', $file_content)) {
                    file_put_contents(
                        $wpms_url_robot,
                        $file_content . "\nSitemap: " . $wpms_url_home . $this->wpms_sitemap_name
                    );
                }
            } else {
                $error = esc_html__('Cannot edit "robots.txt". Check your permissions', 'wp-meta-seo');
                if ($type === 'ajax') {
                    wp_send_json(array('status' => false, 'message' => $error));
                }
            }
        }

        if ((int)$this->settings_sitemap['wpms_sitemap_root'] === 1) {
            $sitemapUrl = site_url($this->wpms_sitemap_default_name);
        } else {
            $sitemapUrl = site_url($this->wpms_sitemap_name);
        }

        /**
         * Submit sitemaps, don't ping if blog is not public.
         *
         * @param string Sitemap URL
         */
        do_action('wpms_submit_sitemap', $sitemapUrl);
        if ($type === 'ajax') {
            wp_send_json(array('status' => true, 'message' => 'success'));
        }
    }

    /**
     * Display priority for each item
     *
     * @param string $id       Selectbox id
     * @param string $name     Selectbox name
     * @param string $selected Selected value
     *
     * @return string
     */
    public function viewPriority($id, $name, $selected)
    {
        $values = array('1' => '100%', '0.9' => '90%', '0.8' => '80%', '0.7' => '70%', '0.6' => '60%', '0.5' => '50%');
        $select = '<select id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" class="wpmsleft wpms-large-input">';
        $select .= '<option value="1">' . esc_html__('Priority', 'wp-meta-seo') . '</option>';
        foreach ($values as $k => $v) {
            if ($k === $selected) {
                $select .= '<option selected value="' . esc_attr($k) . '">' . esc_html($v) . '</option>';
            } else {
                $select .= '<option value="' . esc_attr($k) . '">' . esc_html($v) . '</option>';
            }
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * Display frequency for each item
     *
     * @param string $id       Selectbox id
     * @param string $name     Selectbox name
     * @param string $selected Selected value
     *
     * @return string
     */
    public function viewFrequency($id, $name, $selected)
    {
        $values = array(
            'always'  => 'Always',
            'hourly'  => 'Hourly',
            'daily'   => 'Daily',
            'weekly'  => 'Weekly',
            'monthly' => 'Monthly',
            'yearly'  => 'Yearly',
            'never'   => 'Never'
        );
        $select = '<select id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" class="wpmsleft wpms-large-input">';
        $select .= '<option value="monthly">' . esc_html__('Frequency', 'wp-meta-seo') . '</option>';
        foreach ($values as $k => $v) {
            if ($k === $selected) {
                $select .= '<option selected value="' . esc_attr($k) . '">' . esc_html($v) . '</option>';
            } else {
                $select .= '<option value="' . esc_attr($k) . '">' . esc_html($v) . '</option>';
            }
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * Get all posts in a category
     *
     * @return void
     */
    public function listPostsCategory()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }
        set_time_limit(0);
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare('SELECT p.ID as ID,p.post_title as post_title   
FROM ' . $wpdb->posts . ' AS p
INNER JOIN ' . $wpdb->term_relationships . ' AS tr ON (p.ID = tr.object_id)
INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
INNER JOIN ' . $wpdb->terms . ' AS t ON (t.term_id = tt.term_id)
WHERE   p.post_status = "publish"
    AND p.post_type = "post"
    AND tt.taxonomy = "category" AND t.term_id=%s 
ORDER BY p.post_date DESC', array($_POST['category_id'])));

        $settings = get_option('_metaseo_settings_sitemap');
        $html     = '';
        foreach ($results as $num => $p) {
            if ((int) $num < 10) {
                continue;
            }

            if (empty($settings['wpms_sitemap_posts'][$p->ID]['frequency'])) {
                $postfrequency = 'monthly';
            } else {
                $postfrequency = $settings['wpms_sitemap_posts'][$p->ID]['frequency'];
            }
            if (empty($settings['wpms_sitemap_posts'][$p->ID]['priority'])) {
                $postpriority = '1.0';
            } else {
                $postpriority = $settings['wpms_sitemap_posts'][$p->ID]['priority'];
            }
            $slpr      = $this->viewPriority(
                'priority_posts_' . $p->ID,
                '_metaseo_settings_sitemap[wpms_sitemap_posts][' . $p->ID . '][priority]',
                $postpriority
            );
            $slfr      = $this->viewFrequency(
                'frequency_posts_' . $p->ID,
                '_metaseo_settings_sitemap[wpms_sitemap_posts][' . $p->ID . '][frequency]',
                $postfrequency
            );
            $permalink = get_permalink($p->ID);
            $html      .= '<div class="wpms_row wpms_row_record">';
            $html      .= '<div style="float:left;line-height:30px;">';
            if (strlen($p->post_title) > 30) {
                $title = substr($p->post_title, 0, 30);
            } else {
                $title = $p->post_title;
            }
            if (isset($settings['wpms_sitemap_posts'][$p->ID]['post_id'])
                && (int) $settings['wpms_sitemap_posts'][$p->ID]['post_id'] === (int) $p->ID) {
                $html .= '<input class="wpms_sitemap_input_link checked"
                         type="hidden" data-type="post" value="' . esc_attr($permalink) . '">';
                $html .= '<div class="pure-checkbox">';
                $html .= '<input class="' . esc_attr('cb_sitemaps_posts wpms_xmap_posts category' . $_POST['slug']) . '"
                         id="' . esc_attr('wpms_sitemap_posts_' . $p->ID) . '" type="checkbox"
                          name="_metaseo_settings_sitemap[wpms_sitemap_posts]" value="' . esc_attr($p->ID) . '" checked>';
                $html .= '<label for="' . esc_attr('wpms_sitemap_posts_' . $p->ID) . '" class="wpms-text">' . esc_html($title) . '</label>';
                $html .= '</div>';
            } else {
                $html .= '<input class="wpms_sitemap_input_link" type="hidden"
                         data-type="post" value="' . esc_attr($permalink) . '">';
                $html .= '<div class="pure-checkbox">';
                $html .= '<input class="' . esc_attr('cb_sitemaps_posts wpms_xmap_posts category' . $_POST['slug']) . '"
                         id="' . esc_attr('wpms_sitemap_posts_' . $p->ID) . '" type="checkbox"
                          name="_metaseo_settings_sitemap[wpms_sitemap_posts]" value="' . esc_attr($p->ID) . '">';
                $html .= '<label for="' . esc_attr('wpms_sitemap_posts_' . $p->ID) . '" class="wpms-text">' . esc_html($title) . '</label>';
                $html .= '</div>';
            }

            $html .= '</div>';
            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in the method MetaSeoSitemap::viewPriority and MetaSeoSitemap::viewFrequency
            $html .= '<div class="wpms_right">' . $slpr . $slfr . '</div>';
            $html .= '</div>';
        }

        wp_send_json($html);
    }

    /**
     * Ajax update sitemap settings
     *
     * @return void
     */
    public function saveSitemapSettings()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        $settings_sitemap = get_option('_metaseo_settings_sitemap');
        $lists            = array(
            'wpms_sitemap_add'           => 0,
            'wpms_sitemap_root'          => 0,
            'wpms_sitemap_author'        => 0,
            'wpms_html_sitemap_page'     => 0,
            'wpms_html_sitemap_column'   => 1,
            'wpms_html_sitemap_theme'    => 'default',
            'wpms_html_sitemap_position' => 'after',
            'wpms_sitemap_taxonomies'    => array(),
            'wpms_check_firstsave'       => 0,
            'wpms_display_column_posts'  => 1,
            'wpms_display_column_pages'  => 1,
            'wpms_category_link'         => array(),
            'check_all_menu_items'       => array(),
            'wpms_display_order_menus'   => 1,
            'wpms_display_order_posts'   => 2,
            'wpms_display_order_pages'   => 3,
            'wpms_display_order_urls'    => 4
        );

        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            $custom_post_types = get_post_types(
                array(
                    'public'              => true,
                    'exclude_from_search' => false,
                    '_builtin'            => false
                )
            );
            if (!empty($custom_post_types)) {
                foreach ($custom_post_types as $post_type => $label) {
                    $lists['wpms_display_column_' . $post_type] = 1;
                    $lists['wpms_public_name_' . $post_type]    = '';
                    $lists['wpms_sitemap_' . $post_type]        = array();
                }
            }

            $lists['wpms_display_column_customUrl'] = 1;
            $lists['wpms_public_name_customUrl']    = '';
            $lists['wpms_sitemap_customUrl']        = array();
        }

        $wpms_display_column_menus = json_decode(stripslashes($_POST['wpms_display_column_menus']), true);
        if (!empty($wpms_display_column_menus)) {
            $settings_sitemap['wpms_display_column_menus'] = $wpms_display_column_menus;
        }

        foreach ($lists as $k => $v) {
            if (isset($_POST[$k])) {
                $settings_sitemap[$k] = $_POST[$k];
            } else {
                $settings_sitemap[$k] = $lists[$k];
            }
        }

        $lists_selected = array(
            'wpms_sitemap_posts' => array(),
            'wpms_sitemap_pages' => array(),
            'wpms_sitemap_menus' => array()
        );

        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            $custom_post_types = get_post_types(
                array(
                    'public'              => true,
                    'exclude_from_search' => false,
                    '_builtin'            => false
                )
            );
            if (!empty($custom_post_types)) {
                foreach ($custom_post_types as $post_type => $label) {
                    $lists_selected['wpms_sitemap_' . $post_type] = array();
                }
            }

            $lists_selected['wpms_sitemap_customUrl'] = array();

            // save setting include lang
            if (isset($_POST['wpms_lang_list']) && is_array($_POST['wpms_lang_list'])) {
                $settings_sitemap['wpms_sitemap_include_lang'] = $_POST['wpms_lang_list'];
            }
        }

        foreach ($lists_selected as $k => $v) {
            if (isset($_POST[$k]) && $_POST[$k] !== '{}') {
                $values               = json_decode(stripslashes($_POST[$k]), true);
                $settings_sitemap[$k] = $values;
            } else {
                $settings_sitemap[$k] = array();
            }
        }

        if (isset($_POST['wpms_public_name_posts'])) {
            $settings_sitemap['wpms_public_name_posts'] = $_POST['wpms_public_name_posts'];
        }

        if (isset($_POST['wpms_public_name_pages'])) {
            $settings_sitemap['wpms_public_name_pages'] = $_POST['wpms_public_name_pages'];
        }

        update_option('_metaseo_settings_sitemap', $settings_sitemap);

        /**
         * Save sitemap settings
         *
         * @param array Sitemap settings
         */
        do_action('wpms_save_sitemap_settings', $settings_sitemap);
        wp_send_json(true);
    }
}
