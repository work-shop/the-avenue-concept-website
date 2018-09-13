<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class MetaSeoAdmin
 * Class that holds most of the admin functionality for Meta SEO.
 */
class MetaSeoAdmin
{

    /**
     * Current page
     *
     * @var mixed
     */
    private $pagenow;
    /**
     * Max length meta description
     *
     * @var integer
     */
    public static $desc_length = 320;
    /**
     * Max length meta title
     *
     * @var integer
     */
    public static $title_length = 69;
    /**
     * Google client
     *
     * @var object
     */
    public $client;
    /**
     * Default access to connect to google
     *
     * @var array
     */
    public $access = array(WPMS_CLIENTID, WPMS_CLIENTSECRET);
    /**
     * Error timeout
     *
     * @var integer
     */
    public $error_timeout;
    /**
     * List google analytics options
     *
     * @var array
     */
    public $ga_tracking;
    /**
     * Option to check connect status
     *
     * @var array
     */
    public $gaDisconnect;
    /**
     * Google alanytics options
     *
     * @var array
     */
    public $google_alanytics;

    /**
     * All settings for meta seo
     *
     * @var array
     */
    public $settings;

    /**
     * Google service
     *
     * @var object
     */
    public $service;

    /**
     * MetaSeoAdmin constructor.
     */
    public function __construct()
    {
        $this->pagenow = $GLOBALS['pagenow'];
        $this->setErrorTimeout();
        $this->initSettings();
        $this->initGaSettings();
        if (!get_option('_wpms_dash_last_update', false)) {
            update_option('_wpms_dash_last_update', time());
        }

        add_action('admin_init', array($this, 'adminInit'));
        add_action('init', array($this, 'loadLangguage'));
        add_action('admin_menu', array($this, 'addMenuPage'));

        /**
         * Load admin js
         */
        add_action('admin_enqueue_scripts', array($this, 'loadAdminScripts'));
        add_action('added_post_meta', array('MetaSeoContentListTable', 'updateMetaSync'), 99, 4);
        add_action('updated_post_meta', array('MetaSeoContentListTable', 'updateMetaSync'), 99, 4);
        add_action('deleted_post_meta', array('MetaSeoContentListTable', 'deleteMetaSync'), 99, 4);
        if (!get_option('wpms_set_ignore', false)) {
            add_option('wpms_set_ignore', 1, '', 'yes');
        }

        add_action('wp_ajax_wpms_set_ignore', array($this, 'setIgnore'));
        if (0 === (int) get_option('blog_public')) {
            add_action('admin_notices', array($this, 'publicWarning'));
        }
        add_action('wp_enqueue_editor', array($this, 'linkTitleField'), 20);
        add_action('post_updated', array('MetaSeoBrokenLinkTable', 'updatePost'), 10, 3);
        add_action('delete_post', array('MetaSeoBrokenLinkTable', 'deletePost'));
        add_action('edit_comment', array('MetaSeoBrokenLinkTable', 'updateComment'));
        add_action('deleted_comment', array('MetaSeoBrokenLinkTable', 'deletedComment'));

        add_action('admin_footer', array($this, 'editorFooter'));
        add_action('wp_dashboard_setup', array($this, 'addDashboardWidgets'));
        add_action('category_add_form_fields', array($this, 'categoryField'), 10, 2);
        add_action('edit_category_form_fields', array($this, 'editCategoryFields'));
        add_action('edited_category', array($this, 'saveCategoryMeta'), 10, 2);
        add_action('create_category', array($this, 'saveCategoryMeta'), 10, 2);
        add_action('post_updated', array('MetaSeoImageListTable', 'updatePost'), 10, 3);
        add_action('delete_post', array('MetaSeoImageListTable', 'deleteAttachment'));

        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            add_action('product_cat_add_form_fields', array($this, 'categoryField'));
            add_action('product_cat_edit_form_fields', array($this, 'editCategoryFields'), 10);
            add_action('created_term', array($this, 'saveCategoryMeta'), 10, 3);
            add_action('edit_term', array($this, 'saveCategoryMeta'), 10, 3);
        }
        add_action('wp_ajax_wpms', array($this, 'startProcess'));
    }

    /**
     * Init Meta seo settings
     *
     * @return void
     */
    public function initSettings()
    {
        $this->settings = array(
            'metaseo_title_home'     => '',
            'metaseo_desc_home'      => '',
            'metaseo_showfacebook'   => '',
            'metaseo_showfbappid'    => '',
            'metaseo_showtwitter'    => '',
            'metaseo_twitter_card'   => 'summary',
            'metaseo_showkeywords'   => 0,
            'metaseo_showtmetablock' => 1,
            'metaseo_showsocial'     => 1,
            'metaseo_seovalidate'    => 0,
            'metaseo_linkfield'      => 1,
            'metaseo_metatitle_tab'  => 0,
            'metaseo_follow'         => 0,
            'metaseo_index'          => 0,
            'metaseo_overridemeta'   => 1
        );
        $settings       = get_option('_metaseo_settings');

        if (is_array($settings)) {
            $this->settings = array_merge($this->settings, $settings);
        }

        if ((isset($this->settings['metaseo_showtmetablock']) && (int) $this->settings['metaseo_showtmetablock'] === 1)) {
            $this->loadMetaBoxes();
        }
    }

    /**
     * Init google analytics tracking options
     *
     * @return void
     */
    public function initGaSettings()
    {
        $this->ga_tracking = array(
            'wpmsga_dash_tracking'        => 1,
            'wpmsga_dash_tracking_type'   => 'universal',
            'wpmsga_dash_anonim'          => 0,
            'wpmsga_dash_remarketing'     => 0,
            'wpmsga_event_tracking'       => 0,
            'wpmsga_event_downloads'      => 'zip|mp3*|mpe*g|pdf|docx*|pptx*|xlsx*|rar*',
            'wpmsga_aff_tracking'         => 0,
            'wpmsga_event_affiliates'     => '/out/',
            'wpmsga_hash_tracking'        => 0,
            'wpmsga_author_dimindex'      => 0,
            'wpmsga_pubyear_dimindex'     => 0,
            'wpmsga_category_dimindex'    => 0,
            'wpmsga_user_dimindex'        => 0,
            'wpmsga_tag_dimindex'         => 0,
            'wpmsga_speed_samplerate'     => 1,
            'wpmsga_event_bouncerate'     => 0,
            'wpmsga_enhanced_links'       => 0,
            'wpmsga_dash_adsense'         => 0,
            'wpmsga_crossdomain_tracking' => 0,
            'wpmsga_crossdomain_list'     => '',
            'wpmsga_cookiedomain'         => '',
            'wpmsga_cookiename'           => '',
            'wpmsga_cookieexpires'        => '',
            'wpmsga_track_exclude'        => array(),
            'wpmsga_code_tracking'        => ''
        );
    }

    /**
     * Admin init
     *
     * @return void
     */
    public function adminInit()
    {
        $this->fieldSettings();
        $this->createDatabase();
        $this->updateLinksTable();
    }

    /**
     * Add category meta field
     *
     * @return void
     */
    public function categoryField()
    {
        wp_enqueue_style(
            'm-style-qtip',
            plugins_url('css/jquery.qtip.css', dirname(__FILE__)),
            array(),
            WPMSEO_VERSION
        );

        wp_enqueue_script(
            'jquery-qtip',
            plugins_url('js/jquery.qtip.min.js', dirname(__FILE__)),
            array('jquery'),
            '2.2.1',
            true
        );
        wp_enqueue_style('wpms-category-field');
        wp_enqueue_script('wpms-category-field');
        // this will add the custom meta field to the add new term page
        ?>
        <div class="form-field">
            <label class="wpms_custom_cat_field"
                   data-alt="<?php esc_attr_e('This is the title of your content that may be displayed in search engine
                    results (meta title). By default it’s the content title (page title, post title…).
                     69 characters max allowed.', 'wp-meta-seo') ?>">
                <?php esc_html_e('Search engine title', 'wp-meta-seo'); ?>
            </label>
            <label>
                <textarea name="wpms_category_metatitle" class="wpms_category_metatitle"></textarea>
                <br/>
            </label>
            <div class="cat-title-len"><?php echo esc_html(MPMSCAT_TITLE_LENGTH); ?></div>
        </div>

        <?php
        $settings = get_option('_metaseo_settings');
        if (isset($settings['metaseo_showkeywords']) && (int) $settings['metaseo_showkeywords'] === 1) :
            ?>
            <div class="form-field" style="margin-top: 20px;margin-bottom: 20px;">
                <label class="wpms_custom_cat_field"
                       data-alt="<?php esc_attr_e('This is the keywords of your content that may be
                        displayed in search engine results (meta keywords).', 'wp-meta-seo') ?>">
                    <?php esc_html_e('Search engine keywords', 'wp-meta-seo'); ?>
                </label>
                <label>
                    <textarea name="wpms_category_metakeywords" class="wpms_cat_keywords"></textarea><br/>
                </label>
                <div class="cat-keywords-len"><?php echo esc_html(MPMSCAT_KEYWORDS_LENGTH); ?></div>
            </div>
        <?php endif; ?>
        <div class="form-field" style="margin-top: 20px;margin-bottom: 20px;">
            <label for="extra1" class="wpms_custom_cat_field"
                   data-alt="<?php esc_attr_e('This is the title of your content that may be displayed in search
                    engine results (meta title). By default it’s the content title (page title, post title…).
                     69 characters max allowed.', 'wp-meta-seo') ?>">
                <?php esc_html_e('Search engine description', 'wp-meta-seo'); ?>
            </label>
            <label>
                <textarea name="wpms_category_metadesc" class="wpms_category_metadesc"></textarea><br/>
                <input type="hidden" name="wpms_nonce" value="<?php echo esc_attr(wp_create_nonce('wpms_nonce')) ?>">
            </label>
            <div class="cat-desc-len"><?php echo esc_html(MPMSCAT_DESC_LENGTH); ?></div>
        </div>
        <?php
    }

    /**
     * Save category meta
     *
     * @param integer $term_id Current category id
     *
     * @return void
     */
    public function saveCategoryMeta($term_id)
    {
        global $pagenow;
        // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- Nonce used in next lines
        if ($pagenow === 'edit-tags.php' || (isset($_POST['action'], $_POST['screen']) && $_POST['action'] === 'add-tag' && $_POST['screen'] === 'edit-category')) {
            if (empty($_POST['wpms_nonce'])
                || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
                die();
            }

            if (isset($_POST['wpms_category_metatitle'])) {
                update_term_meta($term_id, 'wpms_category_metatitle', $_POST['wpms_category_metatitle']);
            }

            if (isset($_POST['wpms_category_metadesc'])) {
                update_term_meta($term_id, 'wpms_category_metadesc', $_POST['wpms_category_metadesc']);
            }

            $settings = get_option('_metaseo_settings');
            if (isset($settings['metaseo_showkeywords']) && (int) $settings['metaseo_showkeywords'] === 1) {
                if (isset($_POST['wpms_category_metakeywords'])) {
                    update_term_meta($term_id, 'wpms_category_metakeywords', $_POST['wpms_category_metakeywords']);
                }
            }
        }
    }

    /**
     * Add extra fields to category edit form callback function
     *
     * @param object $tag Current category
     *
     * @return void
     */
    public function editCategoryFields($tag)
    {
        wp_enqueue_style(
            'm-style-qtip',
            plugins_url('css/jquery.qtip.css', dirname(__FILE__)),
            array(),
            WPMSEO_VERSION
        );
        wp_enqueue_script(
            'jquery-qtip',
            plugins_url('js/jquery.qtip.min.js', dirname(__FILE__)),
            array('jquery'),
            '2.2.1',
            true
        );

        wp_enqueue_style('wpms-category-field');
        wp_enqueue_script('wpms-category-field');
        $t_id         = $tag->term_id;
        $metatitle    = get_term_meta($t_id, 'wpms_category_metatitle', true);
        $metadesc     = get_term_meta($t_id, 'wpms_category_metadesc', true);
        $metakeywords = get_term_meta($t_id, 'wpms_category_metakeywords', true);
        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label class="wpms_custom_cat_field" data-alt="<?php esc_attr_e('This is the title of your content that may
                 be displayed in search engine results (meta title). By default it’s the content title
                  (page title, post title…).69 characters max allowed.', 'wp-meta-seo') ?>">
                    <?php esc_html_e('Search engine title', 'wp-meta-seo'); ?>
                </label>
            </th>
            <td>
                <label>
                    <?php if ((!empty($metatitle))) : ?>
                        <textarea name="wpms_category_metatitle"
                                  class="wpms_category_metatitle"><?php echo esc_textarea($metatitle); ?></textarea>
                    <?php else : ?>
                        <textarea name="wpms_category_metatitle"
                                  class="wpms_category_metatitle"></textarea>
                    <?php endif; ?>
                    <br/>
                </label>
                <div class="cat-title-len">
                    <?php
                    echo esc_html($metatitle ? MPMSCAT_TITLE_LENGTH - strlen($metatitle) : MPMSCAT_TITLE_LENGTH);
                    ?>
                </div>
            </td>
        </tr>

        <?php
        $settings = get_option('_metaseo_settings');
        if (isset($settings['metaseo_showkeywords']) && (int) $settings['metaseo_showkeywords'] === 1) :
            ?>
            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="extra1" class="wpms_custom_cat_field"
                           data-alt="<?php esc_attr_e('This is the keywords of your content that may be
                            displayed in search engine results (meta keywords).', 'wp-meta-seo') ?>">
                        <?php esc_html_e('Search engine keywords', 'wp-meta-seo'); ?>
                    </label>
                </th>
                <td>
                    <label>
                        <?php if ((!empty($metakeywords))) : ?>
                            <textarea name="wpms_category_metakeywords"
                                      class="wpms_cat_keywords"><?php echo esc_textarea($metakeywords); ?></textarea>
                        <?php else : ?>
                            <textarea name="wpms_category_metakeywords"
                                      class="wpms_cat_keywords"></textarea>
                        <?php endif; ?>
                        <br/>
                    </label>

                    <div class="cat-keywords-len">
                        <?php
                        echo esc_html($metakeywords ? MPMSCAT_KEYWORDS_LENGTH - strlen($metakeywords) : MPMSCAT_KEYWORDS_LENGTH);
                        ?>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="extra1" class="wpms_custom_cat_field"
                       data-alt="<?php esc_attr_e('This is the title of your content that may be displayed in
                        search engine results (meta title). By default it’s the content title (page title, post title…).
                         69 characters max allowed.', 'wp-meta-seo') ?>">
                    <?php esc_html_e('Search engine description', 'wp-meta-seo'); ?>
                </label>
            </th>
            <td>
                <label>
                    <?php if ((!empty($metadesc))) : ?>
                        <textarea name="wpms_category_metadesc"
                                  class="wpms_category_metadesc"><?php echo esc_textarea($metadesc); ?></textarea>
                    <?php else : ?>
                        <textarea name="wpms_category_metadesc"
                                  class="wpms_category_metadesc"></textarea>
                    <?php endif; ?>
                    <br/>
                </label>
                <input type="hidden" name="wpms_nonce" value="<?php echo esc_attr(wp_create_nonce('wpms_nonce')) ?>">
                <div class="cat-desc-len">
                    <?php echo esc_html($metadesc ? MPMSCAT_DESC_LENGTH - strlen($metadesc) : MPMSCAT_DESC_LENGTH); ?>
                </div>
            </td>
        </tr>
        <?php
    }

    /**
     * Function that outputs the contents of the dashboard widget
     *
     * @return void
     */
    public function dashboardWidget()
    {
        wp_enqueue_style(
            'm-style-qtip',
            plugins_url('css/jquery.qtip.css', dirname(__FILE__)),
            array(),
            WPMSEO_VERSION
        );
        wp_enqueue_script(
            'jquery-qtip',
            plugins_url('js/jquery.qtip.min.js', dirname(__FILE__)),
            array('jquery'),
            '2.2.1',
            true
        );
        wp_enqueue_style('wpms-dashboard-widgets');
        wp_enqueue_script(
            'wpms-dashboard-widgets',
            plugins_url('js/dashboard_widgets.js', dirname(__FILE__)),
            array('jquery'),
            WPMSEO_VERSION
        );
        wp_enqueue_style('wpms-myqtip');
        $error_404 = MetaSeoDashboard::get404Link();
        require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard_widgets.php');
    }

    /**
     * Function used in the action hook
     *
     * @return void
     */
    public function addDashboardWidgets()
    {
        wp_add_dashboard_widget(
            'wpms_dashboard_widget',
            esc_html__('WP Meta SEO: Quick SEO preview', 'wp-meta-seo'),
            array(
                $this,
                'dashboardWidget'
            )
        );
    }

    /**
     * Create link dialog
     *
     * @return void
     */
    public function editorFooter()
    {
        if (!class_exists('_WP_Editors', false)) {
            require_once ABSPATH . 'wp-includes/class-wp-editor.php';
            _WP_Editors::wp_link_dialog();
        }
    }

    /**
     * Create or update 404 page
     *
     * @param string $title    Old page title
     * @param string $newtitle New page title
     *
     * @return void
     */
    public function create404Page($title, $newtitle)
    {
        global $wpdb;
        $post_if = $wpdb->get_results($wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'posts
                     WHERE post_title = %s AND post_excerpt = %s AND post_type = %s',
            array($title, 'metaseo_404_page', 'page')
        ));

        if (empty($post_if)) {
            $content  = '<div class="wall"
 style="background-color: #F2F3F7; border: 30px solid #fff; width: 90%; height: 90%; margin: 0 auto;">

        <h1 style="text-align: center; font-family:\'open-sans\', arial;
         color: #444; font-size: 60px; padding: 50px;">ERROR 404 <br />-<br />NOT FOUND</h1>
    <p style="text-align: center; font-family:\'open-sans\', arial; color: #444;
     font-size: 40px; padding: 20px; line-height: 55px;">
    // You may have mis-typed the URL,<br />
    // Or the page has been removed,<br />
    // Actually, there is nothing to see here...</p>
        <p style="text-align: center;"><a style=" font-family:\'open-sans\', arial; color: #444;
         font-size: 20px; padding: 20px; line-height: 30px; text-decoration: none;"
          href="' . get_home_url() . '"><< Go back to home page >></a></p>
    </div>';
            $_page404 = array(
                'post_title'   => $newtitle,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_excerpt' => 'metaseo_404_page',
                'post_type'    => 'page'
            );
            wp_insert_post($_page404);
        } else {
            $my_post = array(
                'ID'         => $post_if[0]->ID,
                'post_title' => $newtitle
            );

            wp_update_post($my_post);
        }
    }

    /**
     * Update links table
     *
     * @return void
     */
    public function updateLinksTable()
    {
        global $wpdb;
        $option_v     = 'metaseo_db_version3.7.2';
        $db_installed = get_option($option_v, false);
        if (!$db_installed) {
            $wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wpms_links MODIFY `link_url` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL');
            update_option($option_v, true);
        }
    }

    /**
     * Create wpms_links table
     *
     * @return void
     */
    public function createDatabase()
    {
        global $wpdb;
        $option_v     = 'metaseo_db_version3.3.0';
        $db_installed = get_option($option_v, false);
        if (!$db_installed) {
            // create table wpms_links
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'wpms_links(
                    `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
                    `link_url` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
                    `link_final_url` text CHARACTER SET latin1 COLLATE latin1_general_cs,
                    `link_url_redirect` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
                    `link_text` text NOT NULL DEFAULT "",
                    `source_id` int(20) DEFAULT "0",
                    `type` varchar(100) DEFAULT "",
                    `status_code` varchar(100) DEFAULT "",
                    `status_text` varchar(250) DEFAULT "",
                    `hit` int(20) NOT NULL DEFAULT "1",
                    `redirect` tinyint(1) NOT NULL DEFAULT "0",
                    `broken_indexed` tinyint(1) unsigned NOT NULL DEFAULT "0",
                    `broken_internal` tinyint(1) unsigned NOT NULL DEFAULT "0",
                    `warning` tinyint(1) unsigned NOT NULL DEFAULT "0",
                    `dismissed` tinyint(1) NOT NULL DEFAULT "0",
                    PRIMARY KEY  (id))';


            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            $row = $wpdb->get_results($wpdb->prepare(
                'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = %s AND column_name = %s  AND TABLE_SCHEMA = %s',
                array($wpdb->prefix . 'wpms_links', 'follow', $wpdb->dbname)
            ));

            if (empty($row)) {
                $wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wpms_links ADD follow tinyint(1) DEFAULT 1');
            }

            $row = $wpdb->get_results($wpdb->prepare(
                'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = %s AND column_name = %s  AND TABLE_SCHEMA = %s',
                array($wpdb->prefix . 'wpms_links', 'meta_title', $wpdb->dbname)
            ));

            if (empty($row)) {
                $wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wpms_links ADD meta_title varchar(250) DEFAULT ""');
            }

            $row = $wpdb->get_results($wpdb->prepare(
                'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = %s AND column_name = %s  AND TABLE_SCHEMA = %s',
                array($wpdb->prefix . 'wpms_links', 'internal', $wpdb->dbname)
            ));

            if (empty($row)) {
                $wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'wpms_links ADD internal tinyint(1) DEFAULT 1');
            }

            // create page 404
            $this->create404Page('WP Meta SEO 404 Page', '404 error page');
            // create sitemap page
            $post_if = $wpdb->get_var(
                $wpdb->prepare('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'posts
                 WHERE post_title = %s AND post_excerpt = %s AND post_type = %s', array(
                    'WPMS HTML Sitemap',
                    'metaseo_html_sitemap',
                    'page'
                ))
            );
            if ($post_if < 1) {
                $_sitemap_page = array(
                    'post_title'   => 'WPMS HTML Sitemap',
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'post_excerpt' => 'metaseo_html_sitemap',
                    'post_type'    => 'page',
                );
                wp_insert_post($_sitemap_page);
            }

            update_option($option_v, true);
        }


        $option_v     = 'metaseo_db_version3.7.3';
        $db_installed = get_option($option_v, false);
        if (!$db_installed) {
            // create page 404
            $this->create404Page('404 error page', '404 Error, content does not exist anymore');
            update_option($option_v, true);
        }
    }

    /**
     * Add field title in dialog link when edit a link
     *
     * @return void
     */
    public function linkTitleField()
    {
        if (isset($this->settings['metaseo_linkfield']) && (int) $this->settings['metaseo_linkfield'] === 1) {
            wp_enqueue_script(
                'wpmslinkTitle',
                plugins_url('js/wpms-link-title-field.js', dirname(__FILE__)),
                array('wplink'),
                WPMSEO_VERSION,
                true
            );
            wp_localize_script('wpmslinkTitle', 'wpmsLinkTitleL10n', array(
                'titleLabel' => esc_html__('Title', 'wp-meta-seo'),
            ));
        }
    }

    /**
     * Update option wpms_set_ignore
     *
     * @return void
     */
    public function setIgnore()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        update_option('wpms_set_ignore', 0);
        wp_send_json(true);
    }

    /**
     * Render message error when disable search engines from indexing this site
     *
     * @return void
     */
    public function publicWarning()
    {
        if ((function_exists('is_network_admin') && is_network_admin())) {
            return;
        }

        if ((int) get_option('wpms_set_ignore') === 0) {
            return;
        }

        printf(
            '<div id="robotsmessage" class="error">
                            <p>
                                    <strong>%1$s</strong>
                                    %2$s
                                    <a href="javascript:wpmsIgnore(\'wpms_public_warning\',\'robotsmessage\',\'%3$s\');"
                                     class="button">%4$s</a>
                            </p>
                    </div>',
            esc_html__('Your website is not indexed by search engine because of your WordPress settings.', 'wp-meta-seo'),
            sprintf(
                esc_html__('%1$sFix it now%2$s', 'wp-meta-seo'),
                sprintf('<a href="%s">', esc_url(admin_url('options-reading.php'))),
                '</a>'
            ),
            esc_js(wp_create_nonce('wpseo-ignore')),
            esc_html__('OK I know that.', 'wp-meta-seo')
        );
    }

    /**
     * Loads translated strings.
     *
     * @return void
     */
    public function loadLangguage()
    {
        load_plugin_textdomain(
            'wp-meta-seo',
            false,
            dirname(plugin_basename(__FILE__)) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR
        );
    }

    /**
     * Create field analysis
     *
     * @param string $data_title   Title
     * @param string $alt          Alt text
     * @param string $dashicon     Type
     * @param string $label        Label
     * @param string $value_hidden Value
     *
     * @return string
     */
    public function createFieldAnalysis($data_title, $alt, $dashicon, $label, $value_hidden)
    {
        $output = '<div class="metaseo_analysis metaseo_tool" data-title="' . esc_attr($data_title) . '" data-alt="' . esc_attr($alt) . '">';
        if ($dashicon === 'done') {
            $output .= '<i class="metaseo-dashicons material-icons dashicons-before icons-mboxdone">done</i>';
        } else {
            $output .= '<i class="metaseo-dashicons material-icons dashicons-before icons-mboxwarning">warning</i>';
        }

        $output .= esc_html($label);
        $output .= '</div>';
        $output .= '<input type="hidden" class="wpms_analysis_hidden"
         name="' . esc_attr('wpms[' . $data_title . ']') . '" value="' . esc_attr($value_hidden) . '">';
        return $output;
    }

    /**
     * Ajax load page analysis
     *
     * @return void
     */
    public function reloadAnalysis()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json(array('status' => false));
        }
        $tooltip_page                     = array();
        $tooltip_page['title_in_heading'] = esc_attr__('Check if a word of this content title
         is also in a title heading (h1, h2...)', 'wp-meta-seo');
        $tooltip_page['title_in_content'] = esc_attr__('Check if a word of this content
         title is also in the text', 'wp-meta-seo');
        $tooltip_page['page_url']         = esc_attr__('Does the page title match with the permalink (URL structure)', 'wp-meta-seo');
        $tooltip_page['meta_title']       = esc_attr__('Is the meta title of this page filled?', 'wp-meta-seo');
        $tooltip_page['meta_desc']        = esc_attr__('Is the meta description of this page filled?', 'wp-meta-seo');
        $tooltip_page['image_resize']     = esc_attr__('Check for image HTML resizing in content
         (usually image resized using handles)', 'wp-meta-seo');
        $tooltip_page['image_alt']        = esc_attr__('Check for image Alt text and title', 'wp-meta-seo');
        if (empty($_POST['datas'])) {
            wp_send_json(false);
        }

        if (isset($_POST['datas']['post_id']) && empty($_POST['datas']['first_load'])) {
            update_post_meta($_POST['datas']['post_id'], 'wpms_validate_analysis', '');
        }

        $meta_analysis = get_post_meta((int) $_POST['datas']['post_id'], 'wpms_validate_analysis', true);
        $check         = 0;
        $output        = '';

        // title heading
        $words_post_title = preg_split(
            '/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/',
            strtolower($_POST['datas']['title']),
            - 1,
            PREG_SPLIT_NO_EMPTY
        );

        // do shortcode js_composer plugin
        if (is_plugin_active('js_composer_theme/js_composer.php')) {
            add_shortcode('mk_fancy_title', 'vc_do_shortcode');
        }

        $content = apply_filters(
            'the_content',
            '<div>' . html_entity_decode(stripcslashes($_POST['datas']['content'])) . '</div>'
        );

        if (isset($_POST['datas']['first_load']) && !empty($meta_analysis) && !empty($meta_analysis['heading_title'])) {
            $output .= $this->createFieldAnalysis(
                'heading_title',
                $tooltip_page['title_in_heading'],
                'done',
                esc_html__('Page title word in content heading', 'wp-meta-seo'),
                1
            );
            $check ++;
        } else {
            if ($content === '') {
                $output .= $this->createFieldAnalysis(
                    'heading_title',
                    $tooltip_page['title_in_heading'],
                    'warning',
                    esc_html__('Page title word in content heading', 'wp-meta-seo'),
                    0
                );
            } else {
                $dom = new DOMDocument;
                libxml_use_internal_errors(true);
                if ($dom->loadHTML($content)) {
                    // Extracting the specified elements from the web page
                    $tags_h1 = $dom->getElementsByTagName('h1');
                    $tags_h2 = $dom->getElementsByTagName('h2');
                    $tags_h3 = $dom->getElementsByTagName('h3');
                    $tags_h4 = $dom->getElementsByTagName('h4');
                    $tags_h5 = $dom->getElementsByTagName('h5');
                    $tags_h6 = $dom->getElementsByTagName('h6');

                    $test = false;
                    if (empty($tags_h1) && empty($tags_h2) && empty($tags_h3)
                        && empty($tags_h4) && empty($tags_h5) && empty($tags_h6)) {
                        $test = false;
                    } else {
                        // check tag h1
                        if (!empty($tags_h1)) {
                            foreach ($tags_h1 as $order => $tagh1) {
                                $words_tagh1 = preg_split(
                                    '/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/',
                                    utf8_decode(strtolower($tagh1->nodeValue)),
                                    - 1,
                                    PREG_SPLIT_NO_EMPTY
                                );
                                if (is_array($words_tagh1) && is_array($words_post_title)) {
                                    foreach ($words_tagh1 as $mh) {
                                        if (in_array($mh, $words_post_title) && $mh !== '') {
                                            $test = true;
                                        }
                                    }
                                }
                            }
                        }

                        // check tag h2
                        if (!empty($tags_h2)) {
                            foreach ($tags_h2 as $order => $tagh2) {
                                $words_tagh2 = preg_split(
                                    '/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/',
                                    utf8_decode(strtolower($tagh2->nodeValue)),
                                    - 1,
                                    PREG_SPLIT_NO_EMPTY
                                );
                                if (is_array($words_tagh2) && is_array($words_post_title)) {
                                    foreach ($words_tagh2 as $mh) {
                                        if (in_array($mh, $words_post_title) && $mh !== '') {
                                            $test = true;
                                        }
                                    }
                                }
                            }
                        }

                        // check tag h3
                        if (!empty($tags_h3)) {
                            foreach ($tags_h3 as $order => $tagh3) {
                                $words_tagh3 = preg_split(
                                    '/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/',
                                    utf8_decode(strtolower($tagh3->nodeValue)),
                                    - 1,
                                    PREG_SPLIT_NO_EMPTY
                                );
                                if (is_array($words_tagh3) && is_array($words_post_title)) {
                                    foreach ($words_tagh3 as $mh) {
                                        if (in_array($mh, $words_post_title) && $mh !== '') {
                                            $test = true;
                                        }
                                    }
                                }
                            }
                        }

                        // check tag h4
                        if (!empty($tags_h4)) {
                            foreach ($tags_h4 as $order => $tagh4) {
                                $words_tagh4 = preg_split(
                                    '/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/',
                                    utf8_decode(strtolower($tagh4->nodeValue)),
                                    - 1,
                                    PREG_SPLIT_NO_EMPTY
                                );
                                if (is_array($words_tagh4) && is_array($words_post_title)) {
                                    foreach ($words_tagh4 as $mh) {
                                        if (in_array($mh, $words_post_title) && $mh !== '') {
                                            $test = true;
                                        }
                                    }
                                }
                            }
                        }

                        // check tag h5
                        if (!empty($tags_h5)) {
                            foreach ($tags_h5 as $order => $tagh5) {
                                $words_tagh5 = preg_split(
                                    '/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/',
                                    utf8_decode(strtolower($tagh5->nodeValue)),
                                    - 1,
                                    PREG_SPLIT_NO_EMPTY
                                );
                                if (is_array($words_tagh5) && is_array($words_post_title)) {
                                    foreach ($words_tagh5 as $mh) {
                                        if (in_array($mh, $words_post_title) && $mh !== '') {
                                            $test = true;
                                        }
                                    }
                                }
                            }
                        }

                        // check tag h6
                        if (!empty($tags_h6)) {
                            foreach ($tags_h6 as $order => $tagh6) {
                                $words_tagh6 = preg_split(
                                    '/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/',
                                    utf8_decode(strtolower($tagh6->nodeValue)),
                                    - 1,
                                    PREG_SPLIT_NO_EMPTY
                                );
                                if (is_array($words_tagh6) && is_array($words_post_title)) {
                                    foreach ($words_tagh6 as $mh) {
                                        if (in_array($mh, $words_post_title) && $mh !== '') {
                                            $test = true;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($test) {
                        $output .= $this->createFieldAnalysis(
                            'heading_title',
                            $tooltip_page['title_in_heading'],
                            'done',
                            esc_html__('Page title word in content heading', 'wp-meta-seo'),
                            1
                        );
                        $check ++;
                    } else {
                        $output .= $this->createFieldAnalysis(
                            'heading_title',
                            $tooltip_page['title_in_heading'],
                            'warning',
                            esc_html__('Page title word in content heading', 'wp-meta-seo'),
                            0
                        );
                    }
                } else {
                    $output .= $this->createFieldAnalysis(
                        'heading_title',
                        $tooltip_page['title_in_heading'],
                        'warning',
                        esc_html__('Page title word in content heading', 'wp-meta-seo'),
                        0
                    );
                }
            }
        }

        // title content
        $words_title        = preg_split(
            '/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/',
            strtolower($_POST['datas']['title']),
            - 1,
            PREG_SPLIT_NO_EMPTY
        );
        $words_post_content = preg_split(
            '/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/',
            strtolower(strip_tags($content)),
            - 1,
            PREG_SPLIT_NO_EMPTY
        );

        $test1 = false;
        if (is_array($words_title) && is_array($words_post_content)) {
            foreach ($words_title as $mtitle) {
                if (in_array($mtitle, $words_post_content) && $mtitle !== '') {
                    $test1 = true;
                    break;
                }
            }
        } else {
            $test1 = false;
        }

        if (isset($_POST['datas']['first_load']) && !empty($meta_analysis) && !empty($meta_analysis['content_title'])) {
            $output .= $this->createFieldAnalysis(
                'content_title',
                $tooltip_page['title_in_content'],
                'done',
                esc_html__('Page title word in content', 'wp-meta-seo'),
                1
            );
            $check ++;
        } else {
            if ($test1) {
                $output .= $this->createFieldAnalysis(
                    'content_title',
                    $tooltip_page['title_in_content'],
                    'done',
                    esc_html__('Page title word in content', 'wp-meta-seo'),
                    1
                );
                $check ++;
            } else {
                $output .= $this->createFieldAnalysis(
                    'content_title',
                    $tooltip_page['title_in_content'],
                    'warning',
                    esc_html__('Page title word in content', 'wp-meta-seo'),
                    0
                );
            }
        }

        // page url matches page title
        $mtitle = $_POST['datas']['title'];
        if (isset($_POST['datas']['first_load']) && !empty($meta_analysis) && !empty($meta_analysis['pageurl'])) {
            $output .= $this->createFieldAnalysis(
                'pageurl',
                $tooltip_page['page_url'],
                'done',
                esc_html__('Page url matches with page title', 'wp-meta-seo'),
                1
            );
            $check ++;
        } else {
            if ($_POST['datas']['editor'] === 'gutenberg') {
                $infos    = pathinfo($_POST['datas']['mpageurl']);
                $mpageurl = $infos['filename'];
            } else {
                $mpageurl = $_POST['datas']['mpageurl'];
            }

            if ($mpageurl === sanitize_title($mtitle)) {
                $output .= $this->createFieldAnalysis(
                    'pageurl',
                    $tooltip_page['page_url'],
                    'done',
                    esc_html__('Page url matches with page title', 'wp-meta-seo'),
                    1
                );
                $check ++;
            } else {
                $output .= $this->createFieldAnalysis(
                    'pageurl',
                    $tooltip_page['page_url'],
                    'warning',
                    esc_html__('Page url matches with page title', 'wp-meta-seo'),
                    0
                );
            }
        }

        // meta title filled
        if (isset($_POST['datas']['first_load']) && !empty($meta_analysis) && !empty($meta_analysis['metatitle'])) {
            $output .= $this->createFieldAnalysis(
                'metatitle',
                $tooltip_page['meta_title'],
                'done',
                esc_html__('Meta title filled', 'wp-meta-seo'),
                1
            );
            $check ++;
        } else {
            if (($_POST['datas']['meta_title'] !== ''
                 && mb_strlen($_POST['datas']['meta_title'], 'UTF-8') <= self::$title_length)) {
                $output .= $this->createFieldAnalysis(
                    'metatitle',
                    $tooltip_page['meta_title'],
                    'done',
                    esc_html__('Meta title filled', 'wp-meta-seo'),
                    1
                );
                $check ++;
            } else {
                $output .= $this->createFieldAnalysis(
                    'metatitle',
                    $tooltip_page['meta_title'],
                    'warning',
                    esc_html__('Meta title filled', 'wp-meta-seo'),
                    0
                );
            }
        }

        // desc filled
        if (isset($_POST['datas']['first_load']) && !empty($meta_analysis) && !empty($meta_analysis['metadesc'])) {
            $output .= $this->createFieldAnalysis(
                'metadesc',
                $tooltip_page['meta_desc'],
                'done',
                esc_html__('Meta description filled', 'wp-meta-seo'),
                1
            );
            $check ++;
        } else {
            if ($_POST['datas']['meta_desc'] !== ''
                && mb_strlen($_POST['datas']['meta_desc'], 'UTF-8') <= self::$desc_length) {
                $output .= $this->createFieldAnalysis(
                    'metadesc',
                    $tooltip_page['meta_desc'],
                    'done',
                    esc_html__('Meta description filled', 'wp-meta-seo'),
                    1
                );
                $check ++;
            } else {
                $output .= $this->createFieldAnalysis(
                    'metadesc',
                    $tooltip_page['meta_desc'],
                    'warning',
                    esc_html__('Meta description filled', 'wp-meta-seo'),
                    0
                );
            }
        }

        // image resize
        if ($content === '') {
            $output .= $this->createFieldAnalysis(
                'imgresize',
                $tooltip_page['image_resize'],
                'done',
                esc_html__('Wrong image resize', 'wp-meta-seo'),
                1
            );
            $output .= $this->createFieldAnalysis(
                'imgalt',
                $tooltip_page['image_alt'],
                'done',
                esc_html__('Image have meta alt', 'wp-meta-seo'),
                1
            );
            $check  += 2;
        } else {
            $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            if ($dom->loadHTML($content)) {
                // Extracting the specified elements from the web page
                $tags          = $dom->getElementsByTagName('img');
                $img_wrong     = false;
                $img_wrong_alt = false;
                foreach ($tags as $order => $tag) {
                    $src     = $tag->getAttribute('src');
                    $imgpath = str_replace(site_url(), ABSPATH, $src);
                    if (!file_exists($imgpath)) {
                        continue;
                    }
                    if (!list($width_origin, $height_origin) = getimagesize($imgpath)) {
                        continue;
                    }

                    if ((int) $tag->getAttribute('width') === 0 && (int) $tag->getAttribute('height') === 0) {
                        $img_wrong = false;
                    } else {
                        if (!empty($width_origin) && !empty($height_origin)) {
                            if (((int) $width_origin !== (int) $tag->getAttribute('width'))
                                || ((int) $height_origin !== (int) $tag->getAttribute('height'))) {
                                $img_wrong = true;
                            }
                        }
                    }

                    $image_alt = $tag->getAttribute('alt');
                    if ($image_alt === '') {
                        $img_wrong_alt = true;
                    }
                }

                if (isset($_POST['datas']['first_load']) && !empty($meta_analysis) && !empty($meta_analysis['imgresize'])) {
                    $output .= $this->createFieldAnalysis(
                        'imgresize',
                        $tooltip_page['image_resize'],
                        'done',
                        esc_html__('Wrong image resize', 'wp-meta-seo'),
                        1
                    );
                    $check ++;
                } else {
                    if (!$img_wrong) {
                        $output .= $this->createFieldAnalysis(
                            'imgresize',
                            $tooltip_page['image_resize'],
                            'done',
                            esc_html__('Wrong image resize', 'wp-meta-seo'),
                            1
                        );
                        $check ++;
                    } else {
                        $output .= $this->createFieldAnalysis(
                            'imgresize',
                            $tooltip_page['image_resize'],
                            'warning',
                            esc_html__('Wrong image resize', 'wp-meta-seo'),
                            0
                        );
                    }
                }

                if (isset($_POST['datas']['first_load']) && !empty($meta_analysis) && !empty($meta_analysis['imgalt'])) {
                    $output .= $this->createFieldAnalysis(
                        'imgalt',
                        $tooltip_page['image_alt'],
                        'done',
                        esc_html__('Image have meta alt', 'wp-meta-seo'),
                        1
                    );
                    $check ++;
                } else {
                    if (!$img_wrong_alt) {
                        $output .= $this->createFieldAnalysis(
                            'imgalt',
                            $tooltip_page['image_alt'],
                            'done',
                            esc_html__('Image have meta alt', 'wp-meta-seo'),
                            1
                        );
                        $check ++;
                    } else {
                        $output .= $this->createFieldAnalysis(
                            'imgalt',
                            $tooltip_page['image_alt'],
                            'warning',
                            esc_html__('Image have meta alt', 'wp-meta-seo'),
                            0
                        );
                    }
                }
            } else {
                $output .= $this->createFieldAnalysis(
                    'imgresize',
                    $tooltip_page['image_resize'],
                    'warning',
                    esc_html__('Wrong image resize', 'wp-meta-seo'),
                    0
                );
                $output .= $this->createFieldAnalysis(
                    'imgalt',
                    $tooltip_page['image_alt'],
                    'warning',
                    esc_html__('Image have meta alt', 'wp-meta-seo'),
                    0
                );
            }
        }

        $circliful = ceil(100 * ($check) / 7);
        wp_send_json(array('circliful' => $circliful, 'output' => $output, 'check' => $check));
    }

    /**
     * Validate propertyin page optimization
     *
     * @return void
     */
    public function validateAnalysis()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        $post_id  = $_POST['post_id'];
        $key      = 'wpms_validate_analysis';
        $analysis = get_post_meta($post_id, $key, true);
        if (empty($analysis)) {
            $analysis = array();
        }

        $analysis[$_POST['field']] = 1;
        update_post_meta($post_id, $key, $analysis);
        wp_send_json(true);
    }

    /**
     * Remove link in source 404
     *
     * @return void
     */
    public function removeLink()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json(array('status' => false));
        }

        global $wpdb;
        if (isset($_POST['link_id'])) {
            $wpdb->delete($wpdb->prefix . 'wpms_links', array('id' => (int) $_POST['link_id']));
            wp_send_json(array('status' => true));
        }

        wp_send_json(array('status' => false));
    }

    /**
     * Ajax update link meta title and content editor
     *
     * @return void
     */
    public function updateLink()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        if (isset($_POST['link_id'])) {
            global $wpdb;
            $link_detail = $wpdb->get_row($wpdb->prepare(
                'SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE id=%d',
                array(
                    (int) $_POST['link_id']
                )
            ));
            if (empty($link_detail)) {
                wp_send_json(false);
            }

            $value = array('meta_title' => $_POST['meta_title']);
            $wpdb->update(
                $wpdb->prefix . 'wpms_links',
                $value,
                array('id' => (int) $_POST['link_id'])
            );


            $post = get_post($link_detail->source_id);
            if (!empty($post)) {
                $content   = $post->post_content;
                $links = MetaSeoBrokenLinkTable::extractTags($post->post_content, 'a', false, true);
                foreach ($links as $link) {
                    if ($link['contents'] === $link_detail->link_text) {
                        $new_html = '<a';

                        foreach ($link['attributes'] as $name => $value) {
                            //Skip special keys like '#raw' and '#offset'
                            if (substr($name, 0, 1) === '#') {
                                continue;
                            }

                            if (!isset($link['attributes']['title'])) {
                                $new_html .= sprintf(' %s="%s"', 'title', esc_attr($_POST['meta_title']));
                            }

                            if ($name === 'title') {
                                $new_html .= sprintf(' %s="%s"', $name, esc_attr($_POST['meta_title']));
                            } else {
                                $new_html .= sprintf(' %s="%s"', $name, esc_attr($value));
                            }
                        }
                        $new_html .= '>' . $link_detail->link_text . '</a>';
                        $content = str_replace($link['full_tag'], $new_html, $content);
                    }
                }

                $my_post = array(
                    'ID'           => (int) $link_detail->source_id,
                    'post_content' => $content
                );
                remove_action('post_updated', array('MetaSeoBrokenLinkTable', 'updatePost'));
                wp_update_post($my_post);
                wp_send_json(array('status' => true));
            }
        }
        wp_send_json(false);
    }

    /**
     * Ajax update meta index for page
     *
     * @return void
     */
    public function updatePageIndex()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json(array('status' => false));
        }
        if (isset($_POST['page_id']) && isset($_POST['index'])) {
            update_post_meta($_POST['page_id'], '_metaseo_metaindex', $_POST['index']);
            wp_send_json(array('status' => true));
        }
        wp_send_json(array('status' => false));
    }

    /**
     * Ajax update meta follow for page
     *
     * @return void
     */
    public function updatePageFollow()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json(array('status' => false));
        }
        if (isset($_POST['page_id']) && isset($_POST['follow'])) {
            update_post_meta((int) $_POST['page_id'], '_metaseo_metafollow', $_POST['follow']);
            wp_send_json(array('status' => true));
        }
        wp_send_json(array('status' => false));
    }

    /**
     * Ajax update meta follow for link
     *
     * @return void
     */
    public function updateFollow()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json(array('status' => false));
        }
        if (isset($_POST['link_id'])) {
            $this->doUpdateFollow($_POST['link_id'], $_POST['follow']);
            wp_send_json(array('status' => true));
        }
        wp_send_json(array('status' => false));
    }

    /**
     * Ajax update multitle meta follow for link
     *
     * @return void
     */
    public function updateMultipleFollow()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json(array('status' => false));
        }
        global $wpdb;
        $follow_value = $_POST['follow_value'];
        $limit        = 20;

        switch ($follow_value) {
            case 'follow_selected':
                if (empty($_POST['linkids'])) {
                    wp_send_json(array('status' => true));
                }

                $follow = 1;
                foreach ($_POST['linkids'] as $linkId) {
                    $this->doUpdateFollow($linkId, $follow);
                }
                break;

            case 'follow_all':
                $follow = 1;
                $i      = 0;
                $links  = $wpdb->get_results(
                    'SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE follow=0 AND type="url"'
                );
                foreach ($links as $link) {
                    if ($i > $limit) {
                        wp_send_json(array('status' => false, 'message' => 'limit'));
                    } else {
                        $this->doUpdateFollow($link->id, $follow);
                        $i ++;
                    }
                }

                break;

            case 'nofollow_selected':
                $follow = 0;
                if (empty($_POST['linkids'])) {
                    wp_send_json(array('status' => true));
                }

                foreach ($_POST['linkids'] as $linkId) {
                    $this->doUpdateFollow($linkId, $follow);
                }
                break;

            case 'nofollow_all':
                $follow = 0;
                $i      = 0;
                $links  = $wpdb->get_results(
                    'SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE follow=1 AND type="url"'
                );
                foreach ($links as $link) {
                    if ($i > $limit) {
                        wp_send_json(array('status' => false, 'message' => 'limit'));
                    } else {
                        $this->doUpdateFollow($link->id, $follow);
                        $i ++;
                    }
                }
                break;
        }
        wp_send_json(array('status' => true));
    }

    /**
     * Ajax update meta follow for link
     *
     * @param integer $linkId Link id
     * @param integer $follow Follow value
     *
     * @return void
     */
    public function doUpdateFollow($linkId, $follow)
    {
        global $wpdb;
        $link_detail = $wpdb->get_row($wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'wpms_links WHERE id=%d',
            array((int) $linkId)
        ));
        if (empty($link_detail)) {
            wp_send_json(array('status' => false));
        }

        $value = array('follow' => $follow);
        $wpdb->update(
            $wpdb->prefix . 'wpms_links',
            $value,
            array('id' => (int) $linkId)
        );

        $post = get_post($link_detail->source_id);
        if (!empty($post)) {
            $old_value   = $post->post_content;
            $edit_result = $this->editLinkHtml(
                $old_value,
                $link_detail->link_url,
                $link_detail->link_url,
                $link_detail->meta_title,
                $follow
            );
            $my_post     = array(
                'ID'           => (int) $link_detail->source_id,
                'post_content' => $edit_result['content']
            );
            remove_action('post_updated', array('MetaSeoBrokenLinkTable', 'updatePost'));
            wp_update_post($my_post);
        }
    }

    /**
     * Render new content when edit a link
     *
     * @param string  $content    Post content
     * @param string  $new_url    New url
     * @param string  $old_url    Old url
     * @param string  $meta_title Meta title
     * @param integer $follow     Follow value
     * @param null    $new_text   New text of link
     *
     * @return array
     */
    public function editLinkHtml($content, $new_url, $old_url, $meta_title, $follow, $new_text = null)
    {
        //Save the old & new URLs for use in the edit callback.
        $args = array(
            'old_url'    => $old_url,
            'new_url'    => $new_url,
            'new_text'   => $new_text,
            'meta_title' => $meta_title,
            'follow'     => $follow
        );

        //Find all links and replace those that match $old_url.
        $content = MetaSeoBrokenLinkTable::multiEdit(
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
     * Update option wpms_settings_404
     *
     * @return void
     */
    public function save404Settings()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }

        if (isset($_POST['wpms_redirect'])) {
            update_option('wpms_settings_404', $_POST['wpms_redirect']);
        }

        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            $params      = array('enable', 'numberFrequency', 'showlinkFrequency');
            $settinglink = array();
            foreach ($params as $param) {
                if (isset($_POST[$param])) {
                    $settinglink[$param] = $_POST[$param];
                }
            }

            if (empty($settinglink['wpms_lastRun_scanlink'])) {
                $settinglink['wpms_lastRun_scanlink'] = time();
            }
            update_option('wpms_link_settings', $settinglink);
        }

        wp_send_json(true);
    }

    /**
     * Update breadcrumb settings
     *
     * @return void
     */
    public function saveBreadcrumbSettings()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }

        $params      = array('separator', 'include_home', 'home_text', 'clickable', 'home_text_default');
        $settinglink = array();
        foreach ($params as $param) {
            if (isset($_POST[$param])) {
                $settinglink[$param] = $_POST[$param];
            }
        }

        update_option('_metaseo_breadcrumbs', $settinglink);
        wp_send_json(true);
    }

    /**
     * Add a new field to a section of a settings page
     *
     * @return void
     */
    public function fieldSettings()
    {
        register_setting('Wp Meta SEO', '_metaseo_settings');
        add_settings_section('metaseo_dashboard', '', array($this, 'showSettings'), 'metaseo_settings');
        add_settings_field(
            'metaseo_title_home',
            esc_html__('Homepage meta title', 'wp-meta-seo'),
            array($this, 'titleHome'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('You can define your home page meta title in the content
                         itself (a page, a post category…),
                          if for some reason it’s not possible, use this setting', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_desc_home',
            esc_html__('Homepage meta description', 'wp-meta-seo'),
            array($this, 'descHome'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('You can define your home page meta description in the content
                         itself (a page, a post category…),
                         if for some reason it’s not possible, use this setting', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_showfacebook',
            esc_html__('Facebook profile URL', 'wp-meta-seo'),
            array($this, 'showfacebook'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Used as profile in case of social sharing content on Facebook', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_showfbappid',
            esc_html__('Facebook App ID', 'wp-meta-seo'),
            array($this, 'showfbappid'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Used as facebook app ID in case of
                         social sharing content on Facebook', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_showtwitter',
            esc_html__('Twitter Username', 'wp-meta-seo'),
            array($this, 'showtwitter'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Used as profile in case of social sharing content on Twitter', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_twitter_card',
            esc_html__('The default card type to use', 'wp-meta-seo'),
            array($this, 'showtwittercard'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Choose the Twitter card size generated when sharing a content', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_metatitle_tab',
            esc_html__('Meta title as page title', 'wp-meta-seo'),
            array($this, 'showmetatitletab'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Usually not recommended as meta information is
                 for search engines and content title for readers, but in some case... :)', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_showkeywords',
            esc_html__('Meta keywords', 'wp-meta-seo'),
            array($this, 'showkeywords'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Not used directly by search engine to index content,
                 but in some case it can be helpful (multilingual is an example)', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_showtmetablock',
            esc_html__('Meta block edition', 'wp-meta-seo'),
            array($this, 'showtmetablock'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Load the onpage meta edition and analysis block', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_showsocial',
            esc_html__('Social sharing block', 'wp-meta-seo'),
            array($this, 'showsocial'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Activate the custom social sharing tool, above the meta block', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_seovalidate',
            esc_html__('Force SEO validation', 'wp-meta-seo'),
            array($this, 'showseovalidate'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Possibility to force a criteria validation
                 in the content analysis tool', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_linkfield',
            esc_html__('Link text field', 'wp-meta-seo'),
            array($this, 'showlinkfield'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Add the link title field in the text editor and
                 in the bulk link edition view', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_follow',
            esc_html__('Post/Page follow', 'wp-meta-seo'),
            array($this, 'showfollow'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Add an option to setup Follow/Nofollow instruction for each content', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_index',
            esc_html__('Post/Page index', 'wp-meta-seo'),
            array($this, 'showindex'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Add an option to say to search engine: hey!
                 Do not index this content', 'wp-meta-seo')
            )
        );
        add_settings_field(
            'metaseo_overridemeta',
            esc_html__('Auto override Meta', 'wp-meta-seo'),
            array($this, 'showoverridemeta'),
            'metaseo_settings',
            'metaseo_dashboard',
            array(
                'label_for' => esc_html__('Auto override image meta in post content when update meta', 'wp-meta-seo')
            )
        );
    }

    /**
     * Display metatitle_tab input
     *
     * @return void
     */
    public function showmetatitletab()
    {
        echo '<input name="_metaseo_settings[metaseo_metatitle_tab]" type="hidden" value="0"/>';
        ?>
        <label>
            <?php esc_html_e('When meta title is filled use it as page title instead of the content title', 'wp-meta-seo'); ?>
        </label>
        <div class="switch-optimization">
            <label class="switch switch-optimization">
                <?php
                if (isset($this->settings['metaseo_metatitle_tab']) && (int) $this->settings['metaseo_metatitle_tab'] === 1) :
                    ?>
                    <input type="checkbox" id="metaseo_metatitle_tab" name="_metaseo_settings[metaseo_metatitle_tab]"
                           value="1" checked>
                <?php else : ?>
                    <input type="checkbox" id="metaseo_metatitle_tab" name="_metaseo_settings[metaseo_metatitle_tab]"
                           value="1">
                <?php endif; ?>
                <span class="slider round"></span>
            </label>
        </div>
        <?php
    }

    /**
     * Show setting title
     *
     * @return void
     */
    public function showSettings()
    {
    }

    /**
     * Display title_home input
     *
     * @return void
     */
    public function titleHome()
    {
        $home_title = isset($this->settings['metaseo_title_home']) ? $this->settings['metaseo_title_home'] : '';
        echo '<input id="metaseo_title_home" name="_metaseo_settings[metaseo_title_home]"
         type="text" value="' . esc_attr($home_title) . '" size="50"/>';
    }

    /**
     * Display desc_home input
     *
     * @return void
     */
    public function descHome()
    {
        $home_desc = isset($this->settings['metaseo_desc_home']) ? $this->settings['metaseo_desc_home'] : '';
        echo '<input id="metaseo_desc_home" name="_metaseo_settings[metaseo_desc_home]"
         type="text" value="' . esc_attr($home_desc) . '" size="50"/>';
    }

    /**
     * Display showkeywords input
     *
     * @return void
     */
    public function showkeywords()
    {
        echo '<input name="_metaseo_settings[metaseo_showkeywords]" type="hidden" value="0"/>';
        ?>
        <label><?php esc_html_e('Active meta keywords', 'wp-meta-seo'); ?></label>
        <div class="switch-optimization">
            <label class="switch switch-optimization">
                <?php
                if (isset($this->settings['metaseo_showkeywords']) && (int) $this->settings['metaseo_showkeywords'] === 1) :
                    ?>
                    <input type="checkbox" id="metaseo_showkeywords" name="_metaseo_settings[metaseo_showkeywords]"
                           value="1" checked>
                <?php else : ?>
                    <input type="checkbox" id="metaseo_showkeywords" name="_metaseo_settings[metaseo_showkeywords]"
                           value="1">
                <?php endif; ?>
                <span class="slider round"></span>
            </label>
        </div>
        <?php
    }

    /**
     * Display showtmetablock input
     *
     * @return void
     */
    public function showtmetablock()
    {
        echo '<input name="_metaseo_settings[metaseo_showtmetablock]" type="hidden" value="0"/>';
        ?>
        <label><?php esc_html_e('Activate meta block edition below content', 'wp-meta-seo'); ?></label>
        <div class="switch-optimization">
            <label class="switch switch-optimization">
                <?php
                if (isset($this->settings['metaseo_showtmetablock'])
                    && (int) $this->settings['metaseo_showtmetablock'] === 1) :
                    ?>
                    <input type="checkbox" id="metaseo_showtmetablock" name="_metaseo_settings[metaseo_showtmetablock]"
                           value="1" checked>
                <?php else : ?>
                    <input type="checkbox" id="metaseo_showtmetablock" name="_metaseo_settings[metaseo_showtmetablock]"
                           value="1">
                <?php endif; ?>
                <span class="slider round"></span>
            </label>
        </div>
        <?php
    }

    /**
     * Display showsocial input
     *
     * @return void
     */
    public function showsocial()
    {
        echo '<input name="_metaseo_settings[metaseo_showsocial]" type="hidden" value="0"/>';
        ?>
        <label><?php esc_html_e('Activate social edition', 'wp-meta-seo'); ?></label>
        <div class="switch-optimization">
            <label class="switch switch-optimization">
                <?php
                if (isset($this->settings['metaseo_showsocial']) && (int) $this->settings['metaseo_showsocial'] === 1) :
                    ?>
                    <input type="checkbox" id="metaseo_showsocial" name="_metaseo_settings[metaseo_showsocial]"
                           value="1" checked>
                <?php else : ?>
                    <input type="checkbox" id="metaseo_showsocial" name="_metaseo_settings[metaseo_showsocial]"
                           value="1">
                <?php endif; ?>
                <span class="slider round"></span>
            </label>
        </div>
        <?php
    }

    /**
     * Display seovalidate input
     *
     * @return void
     */
    public function showseovalidate()
    {
        echo '<input name="_metaseo_settings[metaseo_seovalidate]" type="hidden" value="0"/>';
        ?>
        <label>
            <?php esc_html_e('Allow user to force on page SEO criteria validation by clicking on the icon', 'wp-meta-seo'); ?>
        </label>
        <div class="switch-optimization">
            <label class="switch switch-optimization">
                <?php
                if (isset($this->settings['metaseo_seovalidate']) && (int) $this->settings['metaseo_seovalidate'] === 1) :
                    ?>
                    <input type="checkbox" id="metaseo_seovalidate" name="_metaseo_settings[metaseo_seovalidate]"
                           value="1" checked>
                <?php else : ?>
                    <input type="checkbox" id="metaseo_seovalidate" name="_metaseo_settings[metaseo_seovalidate]"
                           value="1">
                <?php endif; ?>
                <span class="slider round"></span>
            </label>
        </div>
        <?php
    }

    /**
     * Display linkfield input
     *
     * @return void
     */
    public function showlinkfield()
    {
        echo '<input name="_metaseo_settings[metaseo_linkfield]" type="hidden" value="0"/>';
        ?>
        <label><?php esc_html_e('Adds back the missing title field in the Insert/Edit URL box', 'wp-meta-seo'); ?></label>
        <div class="switch-optimization">
            <label class="switch switch-optimization">
                <?php
                if (isset($this->settings['metaseo_linkfield']) && (int) $this->settings['metaseo_linkfield'] === 1) :
                    ?>
                    <input type="checkbox" id="metaseo_linkfield" name="_metaseo_settings[metaseo_linkfield]"
                           value="1" checked>
                <?php else : ?>
                    <input type="checkbox" id="metaseo_linkfield" name="_metaseo_settings[metaseo_linkfield]"
                           value="1">
                <?php endif; ?>
                <span class="slider round"></span>
            </label>
        </div>
        <?php
    }

    /**
     * Display follow input
     *
     * @return void
     */
    public function showfollow()
    {
        echo '<input name="_metaseo_settings[metaseo_follow]" type="hidden" value="0"/>';
        ?>
        <label>
            <?php esc_html_e('Provides a way for webmasters to tell
             search engines don\'t follow links on the page.', 'wp-meta-seo'); ?>
        </label>
        <div class="switch-optimization">
            <label class="switch switch-optimization">
                <?php
                if (isset($this->settings['metaseo_follow']) && (int) $this->settings['metaseo_follow'] === 1) :
                    ?>
                    <input type="checkbox" id="metaseo_follow" name="_metaseo_settings[metaseo_follow]"
                           value="1" checked>
                <?php else : ?>
                    <input type="checkbox" id="metaseo_follow" name="_metaseo_settings[metaseo_follow]"
                           value="1">
                <?php endif; ?>
                <span class="slider round"></span>
            </label>
        </div>
        <?php
    }

    /**
     * Display index input
     *
     * @return void
     */
    public function showindex()
    {
        echo '<input name="_metaseo_settings[metaseo_index]" type="hidden" value="0"/>';
        ?>
        <label>
            <?php esc_html_e('Provides show or do not show this page in search
             results in search results.', 'wp-meta-seo'); ?>
        </label>
        <div class="switch-optimization">
            <label class="switch switch-optimization">
                <?php
                if (isset($this->settings['metaseo_index']) && (int) $this->settings['metaseo_index'] === 1) :
                    ?>
                    <input type="checkbox" id="metaseo_index" name="_metaseo_settings[metaseo_index]"
                           value="1" checked>
                <?php else : ?>
                    <input type="checkbox" id="metaseo_index" name="_metaseo_settings[metaseo_index]"
                           value="1">
                <?php endif; ?>
                <span class="slider round"></span>
            </label>
        </div>
        <?php
    }

    /**
     * Display override meta
     *
     * @return void
     */
    public function showoverridemeta()
    {
        echo '<input name="_metaseo_settings[metaseo_overridemeta]" type="hidden" value="0"/>';
        ?>
        <label><?php esc_html_e('Override meta image in post content when update meta', 'wp-meta-seo'); ?></label>
        <div class="switch-optimization">
            <label class="switch switch-optimization">
                <?php
                if (isset($this->settings['metaseo_overridemeta']) && (int) $this->settings['metaseo_overridemeta'] === 1) :
                    ?>
                    <input type="checkbox" id="metaseo_overridemeta" name="_metaseo_settings[metaseo_overridemeta]"
                           value="1" checked>
                <?php else : ?>
                    <input type="checkbox" id="metaseo_overridemeta" name="_metaseo_settings[metaseo_overridemeta]"
                           value="1">
                <?php endif; ?>
                <span class="slider round"></span>
            </label>
        </div>
        <?php
    }

    /**
     * Display showfacebook input
     *
     * @return void
     */
    public function showfacebook()
    {
        $face = isset($this->settings['metaseo_showfacebook']) ? $this->settings['metaseo_showfacebook'] : '';
        echo '<input id="metaseo_showfacebook" name="_metaseo_settings[metaseo_showfacebook]"
         type="text" value="' . esc_attr($face) . '" size="50"/>';
    }

    /**
     * Display fb app id input
     *
     * @return void
     */
    public function showfbappid()
    {
        $appid = isset($this->settings['metaseo_showfbappid']) ? $this->settings['metaseo_showfbappid'] : '';
        echo '<input id="metaseo_showfbappid" name="_metaseo_settings[metaseo_showfbappid]"
         type="text" value="' . esc_attr($appid) . '" size="50"/>';
    }

    /**
     * Display showtwitter input
     *
     * @return void
     */
    public function showtwitter()
    {
        $twitter = isset($this->settings['metaseo_showtwitter']) ? $this->settings['metaseo_showtwitter'] : '';
        echo '<input id="metaseo_showtwitter" name="_metaseo_settings[metaseo_showtwitter]"
         type="text" value="' . esc_attr($twitter) . '" size="50"/>';
    }

    /**
     * Display twitter_card input
     *
     * @return void
     */
    public function showtwittercard()
    {
        if (isset($this->settings['metaseo_twitter_card'])) {
            $twitter_card = $this->settings['metaseo_twitter_card'];
        } else {
            $twitter_card = 'summary';
        }
        ?>
        <label>
            <select class="select" name="_metaseo_settings[metaseo_twitter_card]" id="metaseo_twitter_card">
                <option <?php selected($twitter_card, 'summary') ?>
                        value="summary"><?php esc_html_e('Summary', 'wp-meta-seo'); ?></option>
                <option <?php selected($twitter_card, 'summary_large_image') ?>
                        value="summary_large_image"><?php esc_html_e('Summary with large image', 'wp-meta-seo'); ?></option>
            </select>
        </label>
        <?php
    }

    /**
     * Show meta box in single post
     *
     * @return void
     */
    private function loadMetaBoxes()
    {
        if (in_array($this->pagenow, array(
                'edit.php',
                'post.php',
                'post-new.php',
            )) || apply_filters('wpmseo_always_register_metaboxes_on_admin', false)
        ) {
            require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-metabox.php');
            $GLOBALS['wpmseo_metabox'] = new WPMSEOMetabox;
        }
    }

    /**
     * Update meta title , meta description , meta keyword for content
     *
     * @return void
     */
    public function updateContentMetaCallback()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json(array('status' => false));
        }
        $_POST         = stripslashes_deep($_POST);
        $response      = new stdClass();
        $metakey       = strtolower(trim($_POST['metakey']));
        $postID        = intval($_POST['postid']);
        $value         = trim($_POST['value']);
        $response->msg = esc_html__('Modification was saved', 'wp-meta-seo');
        if ($metakey === 'metatitle') {
            if (!update_post_meta($postID, '_metaseo_metatitle', $value)) {
                $response->updated = false;
                $response->msg     = esc_html__('Meta title was not saved', 'wp-meta-seo');
            } else {
                $response->updated = true;
                $response->msg     = esc_html__('Meta title was saved', 'wp-meta-seo');
            }
        }

        if ($metakey === 'metadesc') {
            if (!update_post_meta($postID, '_metaseo_metadesc', $value)) {
                $response->updated = false;
                $response->msg     = esc_html__('Meta description was not saved', 'wp-meta-seo');
            } else {
                $response->updated = true;
                $response->msg     = esc_html__('Meta description was saved', 'wp-meta-seo');
            }
        }

        if ($metakey === 'metakeywords') {
            if (!update_post_meta($postID, '_metaseo_metakeywords', $value)) {
                $response->updated = false;
                $response->msg     = esc_html__('Meta keywords was not saved', 'wp-meta-seo');
            } else {
                $response->updated = true;
                $response->msg     = esc_html__('Meta keywords was saved', 'wp-meta-seo');
            }
        }
        update_option('wpms_last_update_post', time());
        echo json_encode($response);
        wp_die();
    }

    /**
     * Loads js/ajax scripts
     *
     * @return void
     */
    public function loadAdminScripts()
    {
        global $pagenow, $current_screen;
        wp_enqueue_script('jquery');
        $array_menu = array(
            'wp-meta-seo_page_metaseo_dashboard',
            'wp-meta-seo_page_metaseo_image_optimize',
            'wp-meta-seo_page_metaseo_google_sitemap',
            'wp-meta-seo_page_metaseo_image_compression',
            'wp-meta-seo_page_metaseo_broken_link',
            'wp-meta-seo_page_metaseo_settings',
            'wp-meta-seo_page_metaseo_content_meta',
            'wp-meta-seo_page_metaseo_image_meta',
            'wp-meta-seo_page_metaseo_link_meta'
        );

        $lists_pages = array(
            'toplevel_page_metaseo_dashboard',
            'wp-meta-seo_page_metaseo_content_meta',
            'wp-meta-seo_page_metaseo_google_sitemap',
            'wp-meta-seo_page_metaseo_image_meta',
            'wp-meta-seo_page_metaseo_link_meta',
            'wp-meta-seo_page_metaseo_broken_link',
            'wp-meta-seo_page_metaseo_console',
            'wp-meta-seo_page_metaseo_google_analytics',
            'wp-meta-seo_page_metaseo_sendemail',
            'wp-meta-seo_page_metaseo_settings'
        );
        if (in_array($current_screen->base, $lists_pages)) {
            wp_enqueue_style(
                'metaseo-google-icon',
                '//fonts.googleapis.com/icon?family=Material+Icons'
            );
            wp_enqueue_style(
                'wpms_materialize_style',
                plugins_url('css/materialize/materialize.css', dirname(__FILE__)),
                array(),
                WPMSEO_VERSION
            );
            wp_enqueue_script(
                'wpms_materialize_js',
                plugins_url('js/materialize/materialize.min.js', dirname(__FILE__)),
                array('jquery'),
                WPMSEO_VERSION,
                true
            );
        }

        wp_enqueue_script(
            'wpmetaseoAdmin',
            plugins_url('js/metaseo_admin.js', dirname(__FILE__)),
            array('jquery'),
            WPMSEO_VERSION,
            true
        );

        if (in_array($current_screen->base, $array_menu) || $pagenow === 'post.php' || $pagenow === 'post-new.php') {
            wp_enqueue_style(
                'wpmetaseoAdmin',
                plugins_url('css/metaseo_admin.css', dirname(__FILE__)),
                array(),
                WPMSEO_VERSION
            );
            wp_enqueue_style(
                'tooltip-metaimage',
                plugins_url('/css/tooltip-metaimage.css', dirname(__FILE__)),
                array(),
                WPMSEO_VERSION
            );
            wp_enqueue_style('style', plugins_url('/css/style.css', dirname(__FILE__)), array(), WPMSEO_VERSION);
        }

        if ($current_screen->base === 'wp-meta-seo_page_metaseo_image_meta'
            || $current_screen->base === 'wp-meta-seo_page_metaseo_content_meta') {
            wp_enqueue_script(
                'wpms-bulk',
                plugins_url('js/wpms-bulk-action.js', dirname(__FILE__)),
                array('jquery'),
                WPMSEO_VERSION,
                true
            );
            wp_localize_script('wpms-bulk', 'wpmseobulkL10n', $this->localizeScript());
        }

        if ($current_screen->base === 'wp-meta-seo_page_metaseo_broken_link') {
            wp_enqueue_style(
                'wpms_brokenlink_style',
                plugins_url('css/broken_link.css', dirname(__FILE__)),
                array(),
                WPMSEO_VERSION
            );
        }

        if ($current_screen->base === 'toplevel_page_metaseo_dashboard') {
            wp_enqueue_script(
                'Chart',
                plugins_url('js/Chart.js', dirname(__FILE__)),
                array('jquery'),
                WPMSEO_VERSION,
                true
            );
            wp_enqueue_script(
                'jquery-knob',
                plugins_url('js/jquery.knob.js', dirname(__FILE__)),
                array('jquery'),
                WPMSEO_VERSION,
                true
            );
            wp_enqueue_script(
                'metaseo-dashboard',
                plugins_url('js/dashboard.js', dirname(__FILE__)),
                array('jquery'),
                WPMSEO_VERSION,
                true
            );
            wp_enqueue_style(
                'chart',
                plugins_url('/css/chart.css', dirname(__FILE__))
            );
            wp_enqueue_style(
                'metaseo-quirk',
                plugins_url('/css/metaseo-quirk.css', dirname(__FILE__))
            );
            wp_enqueue_style(
                'm-style-dashboard',
                plugins_url('css/dashboard.css', dirname(__FILE__)),
                array(),
                WPMSEO_VERSION
            );
            wp_enqueue_style(
                'm-font-awesome',
                plugins_url('css/font-awesome.css', dirname(__FILE__)),
                array(),
                WPMSEO_VERSION
            );
        }

        $lists_pages = array(
            'toplevel_page_metaseo_dashboard',
            'wp-meta-seo_page_metaseo_google_sitemap',
            'wp-meta-seo_page_metaseo_link_meta',
            'wp-meta-seo_page_metaseo_broken_link',
            'wp-meta-seo_page_metaseo_google_analytics'
        );
        if (in_array($current_screen->base, $lists_pages)) {
            wp_enqueue_style(
                'wpms_notification_style',
                plugins_url('css/notification.css', dirname(__FILE__)),
                array(),
                WPMSEO_VERSION
            );
            wp_enqueue_script(
                'wpms_notification_script',
                plugins_url('js/notification.js', dirname(__FILE__)),
                array(),
                WPMSEO_VERSION
            );
        }

        wp_register_style(
            'wpms-dashboard-widgets',
            plugins_url('css/dashboard_widgets.css', dirname(__FILE__)),
            null,
            WPMSEO_VERSION
        );
        wp_register_style(
            'wpms-category-field',
            plugins_url('css/category_field.css', dirname(__FILE__)),
            null,
            WPMSEO_VERSION
        );
        wp_register_script(
            'wpms-category-field',
            plugins_url('js/category_field.js', dirname(__FILE__)),
            array('jquery'),
            WPMSEO_VERSION,
            true
        );
        wp_register_style(
            'm-style-qtip',
            plugins_url('css/jquery.qtip.css', dirname(__FILE__)),
            array(),
            WPMSEO_VERSION
        );
        wp_register_script(
            'jquery-qtip',
            plugins_url('js/jquery.qtip.min.js', dirname(__FILE__)),
            array('jquery'),
            '2.2.1',
            true
        );
        wp_register_style(
            'wpms-myqtip',
            plugins_url('css/my_qtip.css', dirname(__FILE__)),
            array(),
            WPMSEO_VERSION
        );
        wp_register_script(
            'wpms-broken-link',
            plugins_url('js/wpms-broken-link.js', dirname(__FILE__)),
            array('jquery'),
            WPMSEO_VERSION,
            true
        );

        wp_register_style('metaseo-google-icon', '//fonts.googleapis.com/icon?family=Material+Icons');
        if ($current_screen->base === 'wp-meta-seo_page_metaseo_image_meta') {
            wp_enqueue_script(
                'mautosize',
                plugins_url('js/autosize.js', dirname(__FILE__)),
                array('jquery'),
                '0.1',
                true
            );
        }

        if ($current_screen->base === 'wp-meta-seo_page_metaseo_google_analytics') {
            $lang = get_bloginfo('language');
            $lang = explode('-', $lang);
            $lang = $lang[0];
            wp_enqueue_style(
                'wpms-nprogress',
                plugins_url('css/google-analytics/nprogress.css', dirname(__FILE__)),
                null,
                WPMSEO_VERSION
            );

            wp_register_style(
                'wpms-backend-item-reports',
                plugins_url('css/google-analytics/admin-widgets.css', dirname(__FILE__)),
                null,
                WPMSEO_VERSION
            );
            wp_register_style(
                'wpms-backend-tracking-code',
                plugins_url('css/google-analytics/wpms-tracking-code.css', dirname(__FILE__)),
                null,
                WPMSEO_VERSION
            );

            wp_register_style(
                'jquery-ui-tooltip-html',
                plugins_url('css/google-analytics/jquery.ui.tooltip.html.css', dirname(__FILE__))
            );

            wp_enqueue_style('jquery-ui-tooltip-html');

            wp_enqueue_script(
                'wpmsgooglejsapi',
                'https://www.google.com/jsapi?autoload=%7B%22modules%22%3A%5B%7B%22name%22%3A%22visualization%22%2C%22version%22%3A%221%22%2C%22language%22%3A%22' . $lang . '%22%2C%22packages%22%3A%5B%22corechart%22%2C%20%22table%22%2C%20%22orgchart%22%2C%20%22geochart%22%5D%7D%5D%7D%27',
                array(),
                null
            );

            wp_enqueue_script(
                'wpms-nprogress',
                plugins_url('js/google-analytics/nprogress.js', dirname(__FILE__)),
                array('jquery'),
                WPMSEO_VERSION
            );

            wp_enqueue_script(
                'wpms-google-analytics',
                plugins_url('js/google-analytics/google_analytics.js', dirname(__FILE__)),
                array('jquery'),
                WPMSEO_VERSION,
                true
            );

            /* @formatter:off */
            wp_localize_script(
                'wpms-google-analytics',
                'wpmsItemData',
                array(
                    'ajaxurl'         => admin_url('admin-ajax.php'),
                    'security'        => wp_create_nonce('wpms_backend_item_reports'),
                    'dateList'        => array(
                        'realtime'    => esc_html__('Real-Time', 'wp-meta-seo'),
                        'today'       => esc_html__('Today', 'wp-meta-seo'),
                        'yesterday'   => esc_html__('Yesterday', 'wp-meta-seo'),
                        '7daysAgo'    => sprintf(esc_html__('Last %d Days', 'wp-meta-seo'), 7),
                        '14daysAgo'   => sprintf(esc_html__('Last %d Days', 'wp-meta-seo'), 14),
                        '30daysAgo'   => sprintf(esc_html__('Last %d Days', 'wp-meta-seo'), 30),
                        '90daysAgo'   => sprintf(esc_html__('Last %d Days', 'wp-meta-seo'), 90),
                        '365daysAgo'  => sprintf(_n('%s Year', '%s Years', 1, 'wp-meta-seo'), esc_html__('One', 'wp-meta-seo')),
                        '1095daysAgo' => sprintf(
                            _n('%s Year', '%s Years', 3, 'wp-meta-seo'),
                            esc_html__('Three', 'wp-meta-seo')
                        ),
                    ),
                    'reportList'      => array(
                        'sessions'          => esc_html__('Sessions', 'wp-meta-seo'),
                        'users'             => esc_html__('Users', 'wp-meta-seo'),
                        'organicSearches'   => esc_html__('Organic', 'wp-meta-seo'),
                        'pageviews'         => esc_html__('Page Views', 'wp-meta-seo'),
                        'visitBounceRate'   => esc_html__('Bounce Rate', 'wp-meta-seo'),
                        'locations'         => esc_html__('Location', 'wp-meta-seo'),
                        'contentpages'      => esc_html__('Pages', 'wp-meta-seo'),
                        'referrers'         => esc_html__('Referrers', 'wp-meta-seo'),
                        'searches'          => esc_html__('Searches', 'wp-meta-seo'),
                        'trafficdetails'    => esc_html__('Traffic', 'wp-meta-seo'),
                        'technologydetails' => esc_html__('Technology', 'wp-meta-seo'),
                    ),
                    'i18n'            => array(
                        esc_html__('A JavaScript Error is blocking plugin resources!', 'wp-meta-seo'), //0
                        esc_html__('Traffic Mediums', 'wp-meta-seo'),
                        esc_html__('Visitor Type', 'wp-meta-seo'),
                        esc_html__('Search Engines', 'wp-meta-seo'),
                        esc_html__('Social Networks', 'wp-meta-seo'),
                        esc_html__('Sessions', 'wp-meta-seo'),
                        esc_html__('Users', 'wp-meta-seo'),
                        esc_html__('Page Views', 'wp-meta-seo'),
                        esc_html__('Bounce Rate', 'wp-meta-seo'),
                        esc_html__('Organic Search', 'wp-meta-seo'),
                        esc_html__('Pages/Session', 'wp-meta-seo'),
                        esc_html__('Invalid response', 'wp-meta-seo'),
                        esc_html__('Not enough data collected', 'wp-meta-seo'),
                        esc_html__('This report is unavailable', 'wp-meta-seo'),
                        esc_html__('report generated by', 'wp-meta-seo'), //14
                        esc_html__('This plugin needs an authorization:', 'wp-meta-seo') . '
                         <a href="' . esc_html(admin_url('admin.php?page=metaseo_google_analytics&view=wpmsga_trackcode')) . '">
                         ' . esc_html__('authorize the plugin', 'wp-meta-seo') . '</a>.',
                        esc_html__('Browser', 'wp-meta-seo'), //16
                        esc_html__('Operating System', 'wp-meta-seo'),
                        esc_html__('Screen Resolution', 'wp-meta-seo'),
                        esc_html__('Mobile Brand', 'wp-meta-seo'),
                        esc_html__('REFERRALS', 'wp-meta-seo'), //20
                        esc_html__('KEYWORDS', 'wp-meta-seo'),
                        esc_html__('SOCIAL', 'wp-meta-seo'),
                        esc_html__('CAMPAIGN', 'wp-meta-seo'),
                        esc_html__('DIRECT', 'wp-meta-seo'),
                        esc_html__('NEW', 'wp-meta-seo'), //25
                        esc_html__('You need select a profile:', 'wp-meta-seo') . '
                         <a href="' . esc_html(admin_url('admin.php?page=metaseo_google_analytics&view=wpmsga_trackcode')) . '">
                         ' . esc_html__('authorize the plugin', 'wp-meta-seo') . '</a>.',
                    ),
                    'rtLimitPages'    => 10,
                    'colorVariations' => array(
                        '#1e73be',
                        '#0459a4',
                        '#378cd7',
                        '#51a6f1',
                        '#00408b',
                        '#6abfff',
                        '#002671'
                    ),
                    'region'          => false,
                    'language'        => get_bloginfo('language'),
                    'viewList'        => false,
                    'scope'           => 'admin-widgets',
                    'admin_url'       => admin_url()
                )
            );
        }

        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            $addon_active = 1;
        } else {
            $addon_active = 0;
        }
        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        wp_localize_script('wpmetaseoAdmin', 'wpms_localize', array(
            'addon_active'                 => $addon_active,
            'ajax_url'                     => admin_url('admin-ajax.php'),
            'settings'                     => $this->settings,
            'wpms_cat_metatitle_length'    => MPMSCAT_TITLE_LENGTH,
            'wpms_cat_metadesc_length'     => MPMSCAT_DESC_LENGTH,
            'wpms_cat_metakeywords_length' => MPMSCAT_KEYWORDS_LENGTH,
            'wpms_nonce'                   => wp_create_nonce('wpms_nonce'),
            'home_url'                     => home_url()
        ));
    }

    /**
     * Localize a script.
     *
     * Works only if the script has already been added.
     *
     * @return array
     */
    public function localizeScript()
    {
        return array(
            'metaseo_message_false_copy' => esc_html__('Warning, you\'re about to replace existing image
             alt or tile content, are you sire about that?', 'wp-meta-seo'),
        );
    }

    /**
     * Add a top-level menu page.
     *
     * This function takes a capability which will be used to determine whether
     * or not a page is included in the menu.
     *
     * @return void
     */
    public function addMenuPage()
    {
        // Add main page
        add_menu_page(
            esc_html__('WP Meta SEO: Dashboard', 'wp-meta-seo'),
            esc_html__('WP Meta SEO', 'wp-meta-seo'),
            'manage_options',
            'metaseo_dashboard',
            array(
                $this,
                'loadPage'
            ),
            'dashicons-chart-area'
        );

        /**
         * Filter: 'metaseo_manage_options_capability'
         * - Allow changing the capability users need to view the settings pages
         *
         * @api string unsigned The capability
         */
        $manage_options_cap = apply_filters('metaseo_manage_options_capability', 'manage_options');

        // Sub menu pages
        $submenu_pages = array(
            array(
                'metaseo_dashboard',
                '',
                esc_html__('Dashboard', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_dashboard',
                array($this, 'loadPage'),
                null
            ),
            array(
                'metaseo_dashboard',
                '',
                esc_html__('Content meta', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_content_meta',
                array($this, 'loadPage'),
                null
            ),
            array(
                'metaseo_dashboard',
                '',
                esc_html__('Sitemap', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_google_sitemap',
                array($this, 'loadPage'),
                null,
            ),
            array(
                'metaseo_dashboard',
                '',
                esc_html__('Image information', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_image_meta',
                array($this, 'loadPage'),
                null
            ),
            array(
                'metaseo_dashboard',
                '',
                esc_html__('Image compression', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_image_compression',
                array($this, 'loadPage'),
                null
            ),
            array(
                'metaseo_dashboard',
                '',
                esc_html__('Link editor', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_link_meta',
                array($this, 'loadPage'),
                null
            ),
            array(
                'metaseo_dashboard',
                '',
                esc_html__('404 & Redirects', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_broken_link',
                array($this, 'loadPage'),
                null
            ),
            array(
                'metaseo_dashboard',
                '',
                esc_html__('Google Analytics', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_google_analytics',
                array($this, 'loadPage'),
                null
            )

        );

        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            global $metaseo_addon;
            $submenu_pages[] = array(
                'metaseo_dashboard',
                esc_html__('WP Meta SEO Send email', 'wp-meta-seo'),
                esc_html__('Email report', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_sendemail',
                array($metaseo_addon, 'loadPageSendEmail'),
                null
            );

            $submenu_pages[] = array(
                'metaseo_dashboard',
                esc_html__('Search Console', 'wp-meta-seo'),
                esc_html__('Search Console', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_console',
                array($metaseo_addon->admin_features['metaseo_gsc'], 'display'),
                null
            );
        }

        $submenu_pages[] = array(
            'metaseo_dashboard',
            '',
            esc_html__('Settings', 'wp-meta-seo'),
            $manage_options_cap,
            'metaseo_settings',
            array($this, 'loadPage'),
            null
        );

        // Allow submenu pages manipulation
        $submenu_pages = apply_filters('metaseo_submenu_pages', $submenu_pages);

        // Loop through submenu pages and add them
        if (count($submenu_pages)) {
            foreach ($submenu_pages as $submenu_page) {
                // Add submenu page
                $admin_page = add_submenu_page(
                    $submenu_page[0],
                    $submenu_page[2] . ' - ' . esc_html__('WP Meta SEO:', 'wp-meta-seo'),
                    $submenu_page[2],
                    $submenu_page[3],
                    $submenu_page[4],
                    $submenu_page[5]
                );

                // Check if we need to hook
                if (isset($submenu_page[6]) && null !== $submenu_page[6]
                    && is_array($submenu_page[6]) && count($submenu_page[6]) > 0) {
                    foreach ($submenu_page[6] as $submenu_page_action) {
                        add_action('load-' . $admin_page, $submenu_page_action);
                    }
                }
            }
        }
    }

    /**
     * Set error time out
     *
     * @return void
     */
    public function setErrorTimeout()
    {
        $midnight            = strtotime('tomorrow 00:00:00'); // UTC midnight
        $midnight            = $midnight + 8 * 3600; // UTC 8 AM
        $this->error_timeout = $midnight - time();
    }

    /**
     * Retrieves all Google Analytics Views with details
     *
     * @return array
     */
    public function refreshProfiles()
    {
        try {
            $ga_dash_profile_list = array();
            $startindex           = 1;
            $totalresults         = 65535; // use something big
            while ($startindex < $totalresults) {
                $profiles = $this->service->management_profiles->listManagementProfiles(
                    '~all',
                    '~all',
                    array(
                        'start-index' => $startindex
                    )
                );
                $items    = $profiles->getItems();

                $totalresults = $profiles->getTotalResults();

                if ($totalresults > 0) {
                    foreach ($items as $profile) {
                        $timetz                 = new DateTimeZone($profile->getTimezone());
                        $localtime              = new DateTime('now', $timetz);
                        $timeshift              = strtotime($localtime->format('Y-m-d H:i:s')) - time();
                        $ga_dash_profile_list[] = array(
                            $profile->getName(),
                            $profile->getId(),
                            $profile->getwebPropertyId(),
                            $profile->getwebsiteUrl(),
                            $timeshift,
                            $profile->getTimezone(),
                            $profile->getDefaultPage()
                        );
                        $startindex ++;
                    }
                }
            }

            if (empty($ga_dash_profile_list)) {
                WpmsGaTools::setCache(
                    'last_error',
                    date('Y-m-d H:i:s') . ': No properties were found in this account!',
                    $this->error_timeout
                );
            } else {
                WpmsGaTools::deleteCache('last_error');
            }
            return $ga_dash_profile_list;
        } catch (Google_IO_Exception $e) {
            WpmsGaTools::setCache(
                'last_error',
                date('Y-m-d H:i:s') . ': ' . esc_html($e),
                $this->error_timeout
            );
            return $ga_dash_profile_list;
        } catch (Google_Service_Exception $e) {
            WpmsGaTools::setCache(
                'last_error',
                date('Y-m-d H:i:s') . ': ' . esc_html('(' . $e->getCode() . ') ' . $e->getMessage()),
                $this->error_timeout
            );
            WpmsGaTools::setCache(
                'gapi_errors',
                $e->getCode(),
                $this->error_timeout
            );
            return $ga_dash_profile_list;
        } catch (Exception $e) {
            WpmsGaTools::setCache(
                'last_error',
                date('Y-m-d H:i:s') . ': ' . esc_html($e),
                $this->error_timeout
            );
            return $ga_dash_profile_list;
        }
    }

    /**
     * Load the form for a WPSEO admin page
     *
     * @return void
     */
    public function loadPage()
    {
        if (isset($_GET['page'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- No action, nonce is not required
            switch ($_GET['page']) {
                case 'metaseo_google_analytics':
                    echo "<div class='error wpms_msg_ublock'><p>";
                    esc_html_e('It seems that you use an adblocker, you need to
                     deactivate it for this page in order to load the Google Analytics scripts. ', 'wp-meta-seo');
                    echo '</p></div>';
                    $this->google_alanytics = get_option('wpms_google_alanytics');
                    if (isset($_POST['_metaseo_ggtracking_settings'])) {
                        if (empty($_POST['gadash_security'])
                            || !wp_verify_nonce($_POST['gadash_security'], 'gadash_form')) {
                            die();
                        }
                        update_option('_metaseo_ggtracking_settings', $_POST['_metaseo_ggtracking_settings']);
                        echo '<div id="setting-error-settings_updated"
 class="updated settings-error notice is-dismissible"> 
<p><strong>' . esc_html__('Settings saved.', 'wp-meta-seo') . '</strong></p><button type="button" class="notice-dismiss">
<span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                    }

                    if (!empty($_POST['tableid_jail'])) {
                        if (empty($_POST['wpms_nonce'])
                            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
                            die();
                        }
                        $this->google_alanytics['tableid_jail'] = $_POST['tableid_jail'];
                        update_option('wpms_google_alanytics', $this->google_alanytics);
                    }

                    $ga_tracking = get_option('_metaseo_ggtracking_settings');
                    if (is_array($ga_tracking)) {
                        $this->ga_tracking = array_merge($this->ga_tracking, $ga_tracking);
                    }
                    include_once(WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmstools.php');
                    include_once(WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmsgapi.php');
                    wp_enqueue_style('m-style-qtip');
                    wp_enqueue_script('jquery-qtip');
                    if (isset($_GET['view']) && $_GET['view'] === 'wpmsga_trackcode') {
                        wp_enqueue_style('wpms-backend-tracking-code');
                        include_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/google-analytics/ga-trackcode.php');
                    } else {
                        wp_enqueue_style('wpms-backend-item-reports');
                        if (isset($_POST['wpmsga_dash_clientid']) && isset($_POST['wpmsga_dash_clientsecret'])) {
                            if (empty($_POST['wpms_nonce'])
                                || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
                                die();
                            }
                            $this->google_alanytics['wpmsga_dash_clientid']     = $_POST['wpmsga_dash_clientid'];
                            $this->google_alanytics['wpmsga_dash_clientsecret'] = $_POST['wpmsga_dash_clientsecret'];
                            update_option('wpms_google_alanytics', $this->google_alanytics);
                        }

                        require_once 'autoload.php';
                        $config = new Google_Config();
                        $config->setCacheClass('Google_Cache_Null');
                        $this->client = new Google_Client($config);
                        $this->client->setScopes('https://www.googleapis.com/auth/analytics.readonly');
                        $this->client->setAccessType('offline');
                        $this->client->setApplicationName('WP Meta SEO');
                        $this->client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
                        $this->setErrorTimeout();
                        $this->client  = WpmsGaTools::setClient($this->client, $this->google_alanytics, $this->access);
                        $authUrl       = $this->client->createAuthUrl();
                        $this->service = new Google_Service_Analytics($this->client);
                        $controller    = new WpmsGapiController();

                        if (!empty($_POST['wpms_ga_code'])) {
                            $wpms_ga_code = $_POST['wpms_ga_code'];
                            if (!stripos('x' . $wpms_ga_code, 'UA-', 1)) {
                                WpmsGaTools::deleteCache('gapi_errors');
                                WpmsGaTools::deleteCache('last_error');
                                WpmsGaTools::clearCache();
                                try {
                                    $this->client->authenticate($wpms_ga_code);
                                    $getAccessToken = $this->client->getAccessToken();
                                    if ($getAccessToken) {
                                        try {
                                            $this->client->setAccessToken($getAccessToken);
                                            $this->google_alanytics['googleCredentials']
                                                = $this->client->getAccessToken();
                                        } catch (Google_IO_Exception $e) {
                                            WpmsGaTools::setCache(
                                                'wpmsga_dash_lasterror',
                                                date('Y-m-d H:i:s') . ': ' . esc_html($e),
                                                $this->error_timeout
                                            );
                                        } catch (Google_Service_Exception $e) {
                                            WpmsGaTools::setCache(
                                                'wpmsga_dash_lasterror',
                                                date('Y-m-d H:i:s') . ': ' . esc_html('(' . $e->getCode() . ') ' . $e->getMessage()),
                                                $this->error_timeout
                                            );
                                            WpmsGaTools::setCache(
                                                'wpmsga_dash_gapi_errors',
                                                $e->getCode(),
                                                $this->error_timeout
                                            );
                                        } catch (Exception $e) {
                                            WpmsGaTools::setCache(
                                                'wpmsga_dash_lasterror',
                                                date('Y-m-d H:i:s') . ': ' . esc_html($e),
                                                $this->error_timeout
                                            );
                                        }
                                    }

                                    if (!empty($this->google_alanytics['profile_list'])) {
                                        $profiles = $this->google_alanytics['profile_list'];
                                    } else {
                                        $profiles = $this->refreshProfiles();
                                    }

                                    $this->google_alanytics['code']              = $wpms_ga_code;
                                    $this->google_alanytics['googleCredentials'] = $getAccessToken;
                                    $this->google_alanytics['profile_list']      = $profiles;
                                    update_option('wpms_google_alanytics', $this->google_alanytics);
                                } catch (Google_IO_Exception $e) {
                                    echo '';
                                } catch (Google_Service_Exception $e) {
                                    echo '';
                                } catch (Exception $e) {
                                    echo '';
                                }
                            } else {
                                echo '<div class="error"><p>' . esc_html__('The access code is 
<strong>NOT</strong> your <strong>Tracking ID</strong>
 (UA-XXXXX-X). Try again, and use the red link to get your access code', 'wp-meta-seo') . '.</p></div>';
                            }

                            update_option('wpms_google_alanytics', $this->google_alanytics);
                            wp_redirect(admin_url('admin.php?page=metaseo_google_analytics&view=wpmsga_trackcode'));
                            exit;
                        }
                        $this->gaDisconnect = array(
                            'wpms_ga_uax_reference'     => '',
                            'wpmsga_dash_tracking_type' => 'classic',
                            'wpmsga_code_tracking'      => ''
                        );
                        $gaDisconnect       = get_option('_metaseo_ggtracking_disconnect_settings');
                        if (is_array($gaDisconnect)) {
                            $this->gaDisconnect = array_merge(
                                $this->gaDisconnect,
                                $gaDisconnect
                            );
                        }

                        if (isset($_POST['_metaseo_ga_disconnect'])) {
                            $_metaseo_ga_disconnect                         = $_POST['_metaseo_ga_disconnect'];
                            $_metaseo_ga_disconnect['wpmsga_code_tracking'] = stripslashes($_metaseo_ga_disconnect['wpmsga_code_tracking']);
                            update_option(
                                '_metaseo_ggtracking_disconnect_settings',
                                $_metaseo_ga_disconnect
                            );
                            $gaDisconnect = get_option('_metaseo_ggtracking_disconnect_settings');
                            if (is_array($gaDisconnect)) {
                                $this->gaDisconnect = array_merge(
                                    $this->gaDisconnect,
                                    $gaDisconnect
                                );
                            }
                        }

                        $this->google_alanytics = get_option('wpms_google_alanytics');
                        if (!empty($this->google_alanytics['googleCredentials'])) {
                            if (empty($this->ga_tracking['wpmsga_dash_tracking'])) {
                                echo '<div class="error"><p>' . esc_html__('The tracking component is
 disabled. You should set <strong>Tracking Options</strong> to <strong>Enabled</strong>
 ', 'wp-meta-seo') . '.</p></div>';
                            }
                            echo '<div class="wrap wpmsga_wrap">';
                            echo '<div>';
                            require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/google-analytics/menu.php');
                            echo '<h2>' . esc_html__('Google Analytics Settings', 'wp-meta-seo') . '</h2>';
                            echo '<div id="wpms-window-1"></div>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            if (empty($this->ga_tracking['wpmsga_dash_tracking'])) {
                                echo '<div class="error"><p>' . esc_html__('The tracking component is disabled.
 You should set <strong>Tracking Options</strong> to <strong>Enabled</strong>
 ', 'wp-meta-seo') . '.</p></div>';
                            }

                            if (isset($this->google_alanytics['wpmsga_dash_userapi'])
                                && (int) $this->google_alanytics['wpmsga_dash_userapi'] === 1) {
                                require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/google-analytics/form-connect.php');
                            } else {
                                require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/google-analytics/google-analytics.php');
                            }
                        }
                    }

                    $w               = '99%';
                    $text            = esc_html__('Bring your WordPress website SEO to the next level with the PRO Addon: Email Report,
                     Google Search Console Connect, Automatic Redirect, Advanced Sitemaps and more!', 'wp-meta-seo');
                    $class_btn_close = 'close_gga';
                    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/notification.php');
                    break;
                case 'metaseo_google_sitemap':
                    if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
                        // remove filter by lang
                        if (is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
                            global $sitepress;
                            remove_filter('terms_clauses', array($sitepress, 'terms_clauses'));
                        } elseif (is_plugin_active('polylang/polylang.php')) {
                            global $polylang;
                            $filters_term = $polylang->filters_term;
                            remove_filter('terms_clauses', array($filters_term, 'terms_clauses'));
                        }
                    }

                    if (!class_exists('MetaSeoSitemap')) {
                        require_once(WPMETASEO_PLUGIN_DIR . '/inc/class.metaseo-sitemap.php');
                    }

                    $sitemap = new MetaSeoSitemap();
                    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/sitemaps/metaseo-google-sitemap.php');
                    break;
                case 'metaseo_image_compression':
                    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/metaseo-image-compression.php');
                    break;
                case 'metaseo_broken_link':
                    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/metaseo-broken-link.php');
                    break;
                case 'metaseo_settings':
                    $posts     = get_posts(array('post_type' => 'page', 'posts_per_page' => - 1, 'numberposts' => - 1));
                    $types_404 = array(
                        'none'             => 'None',
                        'wp-meta-seo-page' => esc_html__('WP Meta SEO page', 'wp-meta-seo'),
                        'custom_page'      => esc_html__('Custom page', 'wp-meta-seo')
                    );

                    // get settings 404
                    $defaul_settings_404 = array(
                        'wpms_redirect_homepage' => 0,
                        'wpms_type_404'          => 'none',
                        'wpms_page_redirected'   => 'none'
                    );
                    $wpms_settings_404   = get_option('wpms_settings_404');
                    if (is_array($wpms_settings_404)) {
                        $defaul_settings_404 = array_merge($defaul_settings_404, $wpms_settings_404);
                    }

                    // get settings breadcrumb
                    $home_title = get_the_title(get_option('page_on_front'));
                    if (empty($home_title)) {
                        $home_title = get_bloginfo('title');
                    }
                    $breadcrumbs         = array(
                        'separator'         => ' &gt; ',
                        'include_home'      => 1,
                        'home_text'         => $home_title,
                        'home_text_default' => 0,
                        'clickable'         => 1
                    );
                    $breadcrumb_settings = get_option('_metaseo_breadcrumbs');
                    if (is_array($breadcrumb_settings)) {
                        $breadcrumbs = array_merge($breadcrumbs, $breadcrumb_settings);
                    }

                    // email settings
                    $email_settings = array(
                        'enable'          => 0,
                        'host'            => 'smtp.gmail.com',
                        'type_encryption' => 'ssl',
                        'port'            => '465',
                        'autentication'   => 'yes',
                        'username'        => '',
                        'password'        => '',
                    );

                    $mailsettings = get_option('wpms_email_settings');
                    if (is_array($mailsettings)) {
                        $email_settings = array_merge($email_settings, $mailsettings);
                    }

                    $html_tabemail = apply_filters('wpmsaddon_emailsettings', '', $email_settings);

                    // link settings
                    $link_settings = array(
                        'enable'            => 0,
                        'numberFrequency'   => 1,
                        'showlinkFrequency' => 'month'
                    );

                    $linksettings = get_option('wpms_link_settings');
                    if (is_array($linksettings)) {
                        $link_settings = array_merge($link_settings, $linksettings);
                    }

                    $link_settings_html = apply_filters('wpmsaddon_linksettings', '', $link_settings);

                    // local business settings
                    $local_business = array(
                        'enable'     => 0,
                        'logo'       => '',
                        'type_name'  => '',
                        'country'    => '',
                        'address'    => '',
                        'city'       => '',
                        'state'      => '',
                        'phone'      => '',
                        'pricerange' => '$$'
                    );

                    $business = get_option('wpms_local_business');
                    if (is_array($business)) {
                        $local_business = array_merge($local_business, $business);
                    }
                    $countrys            = apply_filters('wpms_get_countryList', array());
                    $local_business_html = apply_filters('wpmsaddon_local_business', '', $local_business, $countrys);
                    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/settings.php');
                    break;

                case 'metaseo_content_meta':
                    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/content-meta.php');
                    break;


                case 'metaseo_image_meta':
                    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/image-meta.php');
                    break;

                case 'metaseo_link_meta':
                    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/link-meta.php');
                    break;

                case 'metaseo_image_optimize':
                    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/image-optimize.php');
                    break;

                case 'metaseo_dashboard':
                default:
                    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard.php');
                    break;
            }
        }
    }

    /**
     * Ajax check attachment have alt empty
     *
     * @return void
     */
    public function checkExist()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (isset($_POST['type'])) {
            if ($_POST['type'] === 'alt') {
                $margs = array(
                    'posts_per_page' => - 1,
                    'post_type'      => 'attachment',
                    'post_status'    => 'any',
                    'meta_query'     => array(
                        'relation' => 'OR',
                        array(
                            'key'     => '_wp_attachment_image_alt',
                            'value'   => '',
                            'compare' => '!='
                        )
                    )
                );

                $m_newquery       = new WP_Query($margs);
                $mposts_empty_alt = $m_newquery->get_posts();
                if (!empty($mposts_empty_alt)) {
                    wp_send_json(true);
                } else {
                    wp_send_json(false);
                }
            } else {
                global $wpdb;
                $check_title = $wpdb->get_var('SELECT COUNT(posts.ID) as total FROM ' . $wpdb->prefix . 'posts as posts
                     WHERE posts.post_type = "attachment" AND post_title != ""');
                if ($check_title > 0) {
                    wp_send_json(true);
                } else {
                    wp_send_json(false);
                }
            }
        }
    }

    /**
     * Ajax update image alt and image title
     *
     * @return void
     */
    public function bulkImageCopy()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        global $wpdb;
        if (empty($_POST['mtype'])) {
            wp_send_json(false);
        }

        if (isset($_POST['sl_bulk']) && $_POST['sl_bulk'] === 'all') {
            // select all
            $limit = 500;
            // query attachment and update meta
            $total = $wpdb->get_var('SELECT COUNT(posts.ID) as total FROM ' . $wpdb->prefix . 'posts as posts
                     WHERE posts.post_type = "attachment"');

            $j = ceil((int) $total / $limit);
            for ($i = 0; $i <= $j; $i ++) {
                $ofset       = $i * $limit;
                $attachments = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'posts as posts
                       WHERE posts.post_type = %s LIMIT %d OFFSET %d', array('attachment', $limit, $ofset)));

                foreach ($attachments as $attachment) {
                    $i_info_url = pathinfo($attachment->guid);
                    switch ($_POST['mtype']) {
                        case 'image_alt':
                            update_post_meta($attachment->ID, '_wp_attachment_image_alt', $i_info_url['filename']);
                            break;

                        case 'image_title':
                            wp_update_post(array('ID' => $attachment->ID, 'post_title' => $i_info_url['filename']));
                            break;
                    }
                }
            }

            wp_send_json(true);
        } else {
            // selected
            if (isset($_POST['ids'])) {
                $ids   = $_POST['ids'];
                $margs = array(
                    'posts_per_page' => - 1,
                    'post_type'      => 'attachment',
                    'post_status'    => 'any',
                    'post__in'       => $ids
                );

                $m_newquery         = new WP_Query($margs);
                $mposts_empty_alt   = $m_newquery->get_posts();
                $mposts_empty_title = $wpdb->get_results($wpdb->prepare('SELECT *
                        FROM ' . $wpdb->posts . ' WHERE post_type = %s
                               AND post_mime_type LIKE %s AND ID IN (' . implode(',', esc_sql($ids)) . ') 
                        ', array('attachment', '%image%')));
                switch ($_POST['mtype']) {
                    case 'image_alt':
                        if (!empty($mposts_empty_alt)) {
                            foreach ($mposts_empty_alt as $post) {
                                $i_info_url = pathinfo($post->guid);
                                update_post_meta($post->ID, '_wp_attachment_image_alt', $i_info_url['filename']);
                            }
                        } else {
                            wp_send_json(false);
                        }
                        break;

                    case 'image_title':
                        if (!empty($mposts_empty_title)) {
                            foreach ($mposts_empty_title as $post) {
                                $i_info_url = pathinfo($post->guid);
                                wp_update_post(array('ID' => $post->ID, 'post_title' => $i_info_url['filename']));
                            }
                        } else {
                            wp_send_json(false);
                        }
                        break;
                }
                wp_send_json(true);
            } else {
                wp_send_json(false);
            }
        }
    }

    /**
     * Ajax bulk update meta title for a post/page
     *
     * @return void
     */
    public function bulkPostCopyTitle()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        unset($post_types['attachment']);
        $margs = array();
        if (isset($_POST['sl_bulk']) && $_POST['sl_bulk'] === 'all') { // for select a;;
            $margs = array(
                'posts_per_page' => - 1,
                'post_type'      => $post_types,
                'post_status'    => array(
                    'publish',
                    'pending',
                    'draft',
                    'auto-draft',
                    'future',
                    'private',
                    'inherit',
                    'trash'
                ),
                'meta_query'     => array(
                    'relation' => 'OR',
                    array(
                        'key'     => '_metaseo_metatitle',
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'key'   => '_metaseo_metatitle',
                        'value' => false,
                        'type'  => 'BOOLEAN'
                    ),
                )
            );
        } else { // for select some post
            if (isset($_POST['ids'])) {
                $ids   = $_POST['ids'];
                $margs = array(
                    'posts_per_page' => - 1,
                    'post_type'      => $post_types,
                    'post_status'    => array(
                        'publish',
                        'pending',
                        'draft',
                        'auto-draft',
                        'future',
                        'private',
                        'inherit',
                        'trash'
                    ),
                    'post__in'       => $ids,
                    'meta_query'     => array(
                        'relation' => 'OR',
                        array(
                            'key'     => '_metaseo_metatitle',
                            'compare' => 'NOT EXISTS',
                        ),
                        array(
                            'key'   => '_metaseo_metatitle',
                            'value' => false,
                            'type'  => 'BOOLEAN'
                        ),
                    )
                );
            } else {
                wp_send_json(false);
            }
        }

        $m_newquery = new WP_Query($margs);
        $mposts     = $m_newquery->get_posts();
        if (!empty($mposts)) {
            foreach ($mposts as $post) {
                update_post_meta($post->ID, '_metaseo_metatitle', $post->post_title);
            }
            wp_send_json(true);
        } else {
            wp_send_json(false);
        }
    }

    /**
     * Set cookie notification
     *
     * @return void
     */
    public function setcookieNotification()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (isset($_POST['page'])) {
            setcookie($_POST['page'], time(), time() + (86400 * 30), '/');
            wp_send_json(true);
        }
        wp_send_json(false);
    }

    /**
     * Run ajax
     *
     * @return void
     */
    public function startProcess()
    {
        if (empty($_POST['wpms_nonce']) || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        if (isset($_REQUEST['task'])) {
            switch ($_REQUEST['task']) {
                case 'updateContentMeta':
                    $this->updateContentMetaCallback();
                    break;
                case 'scanPosts':
                    MetaSeoImageListTable::scanPostsCallback();
                    break;
                case 'load_posts':
                    MetaSeoImageListTable::loadPostsCallback();
                    break;
                case 'optimize_imgs':
                    MetaSeoImageListTable::optimizeImages();
                    break;
                case 'updateMeta':
                    MetaSeoImageListTable::updateMetaCallback();
                    break;
                case 'import_meta_data':
                    MetaSeoContentListTable::importMetaData();
                    break;
                case 'dismiss_import_meta':
                    MetaSeoContentListTable::dismissImport();
                    break;
                case 'bulk_post_copy':
                    $this->bulkPostCopyTitle();
                    break;
                case 'bulk_image_copy':
                    $this->bulkImageCopy();
                    break;
                case 'ajax_check_exist':
                    $this->checkExist();
                    break;
                case 'reload_analysis':
                    $this->reloadAnalysis();
                    break;
                case 'validate_analysis':
                    $this->validateAnalysis();
                    break;
                case 'update_link':
                    $this->updateLink();
                    break;
                case 'remove_link':
                    $this->removeLink();
                    break;
                case 'save_settings404':
                    $this->save404Settings();
                    break;
                case 'save_settings_breadcrumb':
                    $this->saveBreadcrumbSettings();
                    break;
                case 'update_link_redirect':
                    MetaSeoBrokenLinkTable::updateLinkRedirect();
                    break;
                case 'add_custom_redirect':
                    MetaSeoBrokenLinkTable::addCustomRedirect();
                    break;
                case 'unlink':
                    MetaSeoBrokenLinkTable::unlink();
                    break;
                case 'recheck_link':
                    MetaSeoBrokenLinkTable::reCheckLink();
                    break;
                case 'scan_link':
                    MetaSeoBrokenLinkTable::scanLink();
                    break;
                case 'flush_link':
                    MetaSeoBrokenLinkTable::flushLink();
                    break;
                case 'update_follow':
                    $this->updateFollow();
                    break;
                case 'update_multiplefollow':
                    $this->updateMultipleFollow();
                    break;
                case 'update_pagefollow':
                    $this->updatePageFollow();
                    break;
                case 'update_pageindex':
                    $this->updatePageIndex();
                    break;
                case 'backend_item_reports':
                    MetaSeoGoogleAnalytics::itemsReport();
                    break;
                case 'ga_clearauthor':
                    MetaSeoGoogleAnalytics::clearAuthor();
                    break;
                case 'ga_update_option':
                    MetaSeoGoogleAnalytics::updateOption();
                    break;
                case 'dash_permalink':
                    MetaSeoDashboard::dashboardPermalink();
                    break;
                case 'dash_newcontent':
                    MetaSeoDashboard::dashboardNewContent();
                    break;
                case 'dash_linkmeta':
                    MetaSeoDashboard::dashboardLinkMeta();
                    break;
                case 'dash_metatitle':
                    MetaSeoDashboard::dashboardMetaTitle();
                    break;
                case 'dash_metadesc':
                    MetaSeoDashboard::dashboardMetaDesc();
                    break;
                case 'dash_imgsmeta':
                    MetaSeoDashboard::dashImgsMeta();
                    break;
                case 'setcookie_notification':
                    $this->setcookieNotification();
                    break;
                case 'image_scan_meta':
                    MetaSeoImageListTable::imageScanMeta();
                    break;
            }
        }
    }
}
