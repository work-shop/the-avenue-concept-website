<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-meta.php');
require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-snippet-preview.php');

/**
 * Class WPMSEOMetabox
 * This class generates the metabox on the edit post / page as well as contains all page analysis functionality.
 */
class WPMSEOMetabox extends WPMSEOMeta
{
    /**
     * @var int
     */
    public $perc_score = 0;
    /**
     * @var
     */
    public $settings;
    /**
     * Google client
     * @var
     */
    public $client;

    /**
     * WPMSEOMetabox constructor.
     */
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'addMetaBox'));
        add_action('wp_insert_post', array($this, 'savePostData'));
        add_action('edit_attachment', array($this, 'savePostData'));
        add_action('add_attachment', array($this, 'savePostData'));
        add_action('admin_init', array($this, 'translateMetaBoxes'));
    }

    /**
     * @param $post_id
     * @return bool
     */
    public function savePostData($post_id)
    {
        if ($post_id === null) {
            return false;
        }

        if (wp_is_post_revision($post_id)) {
            $post_id = wp_is_post_revision($post_id);
        }

        clean_post_cache($post_id);
        $post = get_post($post_id);

        if (!is_object($post)) {
            return false;
        }

        do_action('wpmseo_save_compare_data', $post);

        $meta_boxes = apply_filters('wpmseo_save_metaboxes', array());
        $meta_boxes = array_merge(
            $meta_boxes,
            $this->getMetaFieldDefs(
                'general',
                $post->post_type
            ),
            $this->getMetaFieldDefs('advanced'),
            $this->getMetaFieldDefs('social')
        );
        foreach ($meta_boxes as $key => $meta_box) {
            $data = null;
            if ('checkbox' === $meta_box['type']) {
                $data = isset($_POST[self::$form_prefix . $key]) ? 'on' : 'off';
            } else {
                if (isset($_POST[self::$form_prefix . $key])) {
                    $data = $_POST[self::$form_prefix . $key];
                }
            }
            if (isset($data)) {
                self::setValue($key, $data, $post_id);
            }
        }
        do_action('wpmseo_saved_postdata');
        return true;
    }

    /**
     * Translate text strings for use in the meta box
     */
    public static function translateMetaBoxes()
    {
        self::$meta_fields['general']['snippetpreview']['title'] = __('Results preview', 'wp-meta-seo');
        self::$meta_fields['general']['snippetpreview']['help'] = sprintf(
            __('This is a preview of what your content will looks like
             in search engine results: title, description and URL', 'wp-meta-seo'),
            '<a target="_blank" href="https://www.joomunited.com/wordpress-products/wpms">',
            '</a>'
        );

        self::$meta_fields['general']['title']['title'] = __('Search engine title', 'wp-meta-seo');
        self::$meta_fields['general']['title']['description'] = sprintf(
            '<span id="metaseo_wpmseo_title-length">%s</span>',
            self::$meta_length_reason
        );
        self::$meta_fields['general']['title']['help'] = __('This is the title of your content that may be displayed
         in search engine results (meta title). By default it’s the content title (page title, post title…).
          69 characters max allowed.', 'wp-meta-seo');

        $settings = get_option('_metaseo_settings');
        if (isset($settings['metaseo_showkeywords']) && $settings['metaseo_showkeywords'] == 1) {
            self::$meta_fields['general']['keywords']['title'] = __('Search engine keywords', 'wp-meta-seo');
            self::$meta_fields['general']['keywords']['description']
             = '<span id="metaseo_wpmseo_keywords-length"></span>';
            self::$meta_fields['general']['keywords']['help'] = __('This is the keywords of your content that may be
             displayed in search engine results (meta keywords).', 'wp-meta-seo');
        } else {
            unset(self::$meta_fields['general']['keywords']);
        }


        self::$meta_fields['general']['desc']['title'] = __('Search engine description', 'wp-meta-seo');
        self::$meta_fields['general']['desc']['description'] = sprintf(
            '<span id="metaseo_wpmseo_desc-length">%s</span>',
            self::$meta_length_reason
        );
        self::$meta_fields['general']['desc']['help'] = __('The description of your content that may be displayed
         in search engine results aka meta description.
          By default search engine take an excerpt from your content (depending on the search query).
          320 characters max allowed.', 'wp-meta-seo');

        self::$meta_fields['social']['opengraph-title']['title'] = __('Facebook Title', 'wp-meta-seo');
        self::$meta_fields['social']['opengraph-title']['description'] = __('Custom title to display when
         sharing this content on facebook, content title override', 'wp-meta-seo');

        self::$meta_fields['social']['opengraph-desc']['title'] = __('Facebook Description', 'wp-meta-seo');
        self::$meta_fields['social']['opengraph-desc']['description'] = __('Custom description to display when sharing
         this content on facebook, content description override', 'wp-meta-seo');

        self::$meta_fields['social']['opengraph-image']['title'] = __('Facebook Image', 'wp-meta-seo');
        self::$meta_fields['social']['opengraph-image']['description'] = __('Custom image to display when sharing
         this content on facebook, content description override, recommended size is 1200px x 630px', 'wp-meta-seo');

        self::$meta_fields['social']['twitter-title']['title'] = __('Twitter Title', 'wp-meta-seo');
        self::$meta_fields['social']['twitter-title']['description'] = __('Custom title to display when sharing this
         content on twitter, content title override', 'wp-meta-seo');

        self::$meta_fields['social']['twitter-desc']['title'] = __('Twitter Description', 'wp-meta-seo');
        self::$meta_fields['social']['twitter-desc']['description'] = __('Custom description to display when sharing
         this content on twitter, content description override', 'wp-meta-seo');

        self::$meta_fields['social']['twitter-image']['title'] = __('Twitter Image', 'wp-meta-seo');
        self::$meta_fields['social']['twitter-image']['description'] = __('Custom image to display when sharing
         this content on facebook, content description override, recommended min size 440px X 220px', 'wp-meta-seo');

        do_action('wpmseo_tab_translate');
    }

    /**
     * load script and style
     */
    public function metaseoEnqueue()
    {
        global $pagenow;
        if ((!in_array($pagenow, array(
                'post-new.php',
                'post.php',
                'edit.php',
            ), true)
            && apply_filters('wpmseo_always_register_metaboxes_on_admin', false) === false)
        ) {
            return;
        }

        if ($pagenow != 'edit.php') {
            if (0 != get_queried_object_id()) {
                // Enqueue files needed for upload functionality.
                wp_enqueue_media(
                    array(
                        'post' => get_queried_object_id()
                    )
                );
            }
            wp_enqueue_style(
                'm-metabox-tabs',
                plugins_url('css/metabox-tabs.css', WPMSEO_FILE),
                array(),
                WPMSEO_VERSION
            );
            wp_enqueue_style(
                'm-style-qtip',
                plugins_url('css/jquery.qtip.css', WPMSEO_FILE),
                array(),
                WPMSEO_VERSION
            );
            wp_enqueue_script(
                'jquery-qtip',
                plugins_url('js/jquery.qtip.min.js', WPMSEO_FILE),
                array('jquery'),
                '2.2.1',
                true
            );
            wp_enqueue_script(
                'm-wp-seo-metabox',
                plugins_url('js/wp-metaseo-metabox.js', WPMSEO_FILE),
                array('jquery', 'jquery-ui-core'),
                WPMSEO_VERSION,
                true
            );
            wp_enqueue_script(
                'mwpseo-admin-media',
                plugins_url('js/wp-metaseo-admin-media.js', WPMSEO_FILE),
                array('jquery', 'jquery-ui-core'),
                WPMSEO_VERSION,
                true
            );
            wp_enqueue_script(
                'metaseo-cliffpyles',
                plugins_url('js/cliffpyles.js', WPMSEO_FILE),
                array('jquery'),
                WPMSEO_VERSION,
                true
            );
            wp_localize_script('m-wp-seo-metabox', 'wpmseoMetaboxL10n', $this->localizeScript());
            $localize = $this->localizeSettingsScript();
            wp_localize_script('mwpseo-admin-media', 'wpmseoMediaL10n', $localize);
            wp_localize_script('metaseo-cliffpyles', 'wpmscliffpyles', $localize);
        }
    }

    /**
     * Localize a script
     * @return array
     */
    public function localizeSettingsScript()
    {
        $this->settings = array(
            "metaseo_title_home" => "",
            "metaseo_desc_home" => "",
            "metaseo_showfacebook" => "",
            "metaseo_showtwitter" => "",
            "metaseo_twitter_card" => "summary",
            "metaseo_showkeywords" => 0,
            "metaseo_showtmetablock" => 1,
            "metaseo_showsocial" => 1,
            "metaseo_seovalidate" => 0
        );
        $settings = get_option('_metaseo_settings');

        if (is_array($settings)) {
            $this->settings = array_merge($this->settings, $settings);
        }

        return array(
            'choose_image' => __('Use Image', 'wp-meta-seo'),
            'use_validate' => $this->settings['metaseo_seovalidate']
        );
    }

    /**
     * Localize a script
     * @return array
     */
    public function localizeScript()
    {
        $post = $this->getMetaboxPost();

        if ((!is_object($post) || !isset($post->post_type))) {
            return array();
        }

        self::$meta_length_reason = apply_filters('wpmseo_desc_length_reason', self::$meta_length_reason, $post);
        self::$meta_length = apply_filters('wpmseo_desc_length', self::$meta_length, $post);
        $title_template = '%%title%% - %%sitename%%';

        $desc_template = '';
        $sample_permalink = get_sample_permalink($post->ID);
        $sample_permalink = str_replace('%page', '%post', $sample_permalink[0]);

        $cached_replacement_vars = array();

        $vars_to_cache = array(
            'sitedesc',
            'sep',
            'page',
        );

        foreach ($vars_to_cache as $var) {
            $cached_replacement_vars[$var] = $var;
        }

        $cached_replacement_vars['sitename'] = get_option('blogname');
        $plugin_active = json_encode(get_option('active_plugins'));
        $array_keyword = array(
            'plugin_active' => $plugin_active,
            'field_prefix' => self::$form_prefix,
            'choose_image' => __('Use Image', 'wp-meta-seo'),
            'wpmseo_meta_desc_length' => self::$meta_length,
            'wpmseo_meta_title_length' => self::$meta_title_length,
            'wpmseo_meta_keywords_length' => self::$meta_keywords_length,
            'wpmseo_title_template' => $title_template,
            'wpmseo_desc_template' => $desc_template,
            'wpmseo_permalink_template' => $sample_permalink,
            'wpmseo_keyword_suggest_nonce' => wp_create_nonce('wpmseo-get-suggest'),
            'wpmseo_replace_vars_nonce' => wp_create_nonce('wpmseo-replace-vars'),
            'no_parent_text' => __('(no parent)', 'wp-meta-seo'),
            'show_keywords' => 0
        );
        $settings = get_option('_metaseo_settings');
        if (isset($settings['metaseo_showkeywords']) && $settings['metaseo_showkeywords'] == 1) {
            $array_keyword['show_keywords'] = 1;
        }

        return array_merge($cached_replacement_vars, $array_keyword);
    }

    /**
     * Adds the Meta SEO meta box to the edit boxes in the edit post / page  / cpt pages.
     */
    public function addMetaBox()
    {
        $post_types = get_post_types(array('public' => true));
        if (is_array($post_types) && $post_types !== array()) {
            foreach ($post_types as $post_type) {
                add_meta_box('wpmseo_meta', __('WP Meta SEO - Page optimization', 'wp-meta-seo'), array(
                    $this,
                    'metaBox',
                ), $post_type, 'normal', apply_filters('wpmseo_metabox_prio', 'core'));
            }
        }
    }

    /**
     * Output the meta box
     */
    public function metaBox()
    {
        $this->metaseoEnqueue();
        $post = $this->getMetaboxPost();
        $default_settings = array(
            "metaseo_title_home" => "",
            "metaseo_desc_home" => "",
            "metaseo_showfacebook" => "",
            "metaseo_showtwitter" => "",
            "metaseo_twitter_card" => "summary",
            "metaseo_showtmetablock" => 1,
            "metaseo_showsocial" => 1
        );
        $settings = get_option('_metaseo_settings');
        if (is_array($settings)) {
            $default_settings = array_merge($default_settings, $settings);
        }
        $check_connected = false;
        $service = false;
        ?>
        <div class="wpmseo-metabox-tabs-div">
        <ul class="wpmseo-metabox-tabs" id="wpmseo-metabox-tabs">
            <li class="general">
                <a class="wpmseo_tablink"
                   data-link="wpmseo_general"><?php _e('SEO Page optimization', 'wp-meta-seo'); ?></a>
            </li>
            <?php
            if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
                $service = $this->serverWebmaster();
                if (!empty($service)) {
                    // get domain
                    $option = get_option(WPMS_GSC, array('profile' => ''));
                    if (!empty($option['profile'])) {
                        $check_connected = true;
                    }
                }
                if ($check_connected) {
                    echo '<li class="gsckeywords">';
                    echo '<a class="wpmseo_tablink" data-link="wpmseo_gsc_keywords">';
                    echo __('Search console keywords', 'wp-meta-seo');
                    echo '</a>';
                    echo '</li>';
                }
            }
            ?>

            <?php if ((isset($default_settings['metaseo_showsocial'])
                && $default_settings['metaseo_showsocial'] == 1)) : ?>
                <li class="social">
                    <a class="wpmseo_tablink"
                       data-link="wpmseo_social"><?php _e('Social for search engine', 'wp-meta-seo') ?></a>
                </li>
            <?php endif; ?>
            <?php do_action('wpmseo_tab_header'); ?>
        </ul>
        <?php
        $content = '';
        if (is_object($post) && isset($post->post_type)) {
            foreach ($this->getMetaFieldDefs('general', $post->post_type) as $key => $meta_field) {
                $content .= $this->doMetaBox($meta_field, $key);
            }
            unset($key, $meta_field);
        }
        $this->doTab('general', $content);
        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            if ($check_connected) {
                echo apply_filters('wpmsaddon_google_search_keywords', '', $post->post_content, $service);
            }
        }


        $content = '';
        foreach ($this->getMetaFieldDefs('social') as $meta_key => $meta_field) {
            $content .= $this->doMetaBox($meta_field, $meta_key);
        }

        if ((isset($default_settings['metaseo_showsocial']) && $default_settings['metaseo_showsocial'] == 1)) {
            $this->doTab('social', $content);
        }

        do_action('wpmseo_tab_content');

        echo '</div>';
    }

    /**
     * @return bool|Wpms_Google_Service_Webmasters
     */
    public function serverWebmaster()
    {
        try {
            if (!class_exists('Wpms_Api_Google', false)) {
                require_once WPMETASEO_ADDON_PLUGIN_DIR . '/inc/google-api/' . 'class-api-google.php';
                new Wpms_Api_Google();
            }
        } catch (Exception $exception) {
        }

        $conn = get_option('wpms_gsc_connect');
        if (!empty($conn['googleClientId']) && !empty($conn['googleClientSecret'])) {
            $client_id = $conn['googleClientId'];
            $client_secret = $conn['googleClientSecret'];
        } else {
            $client_id = WPMSEO_ADDON_CLIENT_ID;
            $client_secret = WPMSEO_ADDON_CLIENT_SECRET;
        }

        $gsc = array(
            'application_name' => 'WP Meta SEO',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob',
            'scopes' => array('https://www.googleapis.com/auth/webmasters'),
        );

        $this->client = new Wpms_Api_Google_Client($gsc, 'wpms-gsc', 'https://www.googleapis.com/webmasters/v3/');
        if ($this->client->getAccessToken() != null) {
            $service = new Wpms_Google_Service_Webmasters($this->client);
            return $service;
        }
        return false;
    }

    /**
     * Generate html field settings
     * @param $data_title
     * @param $alt
     * @param $dashicon
     * @param $label
     * @param $value_hidden
     * @return string
     */
    public function createFieldAnalysis($data_title, $alt, $dashicon, $label, $value_hidden)
    {
        $output = '<div class="metaseo_analysis metaseo_tool" data-title="' . $data_title . '" data-alt="' . $alt . '">';
        if ($dashicon == 'done') {
            $output .= '<i class="metaseo-dashicons material-icons dashicons-before icons-mboxdone">done</i>' . $label . '</div>';
        } else {
            $output .= '<i class="metaseo-dashicons material-icons dashicons-before icons-mboxwarning">warning</i>' . $label . '</div>';
        }

        $output .= '<input type="hidden" class="wpms_analysis_hidden" name="wpms[' . $data_title . ']"
         value="' . $value_hidden . '">';
        return $output;
    }

    /**
     * load heading title status in page optimize
     * @param $tooltip_page
     * @return string
     */
    public function headingTitle($tooltip_page)
    {
        $output = $this->createFieldAnalysis(
                'heading_title',
                $tooltip_page['title_in_heading'],
                'warning',
                __('Page title word in content heading', 'wp-meta-seo'),
                0
            );
            return $output;
    }

    /**
     * load meta title in content status in page optimize
     * @param $tooltip_page
     * @return string
     */
    public function titleContent($tooltip_page)
    {
        $output = $this->createFieldAnalysis(
                'content_title',
                $tooltip_page['title_in_content'],
                'warning',
                __('Page title word in content', 'wp-meta-seo'),
                0
            );
        return $output;
    }

    /**
     * load page url status in page optimize
     * @param $tooltip_page
     * @return string
     */
    public function pageUrl($tooltip_page)
    {
        $output = $this->createFieldAnalysis(
                    'pageurl',
                    $tooltip_page['page_url'],
                    'warning',
                    __('Page url matches with page title', 'wp-meta-seo'),
                    0
                );

        return $output;
    }

    /**
     * load meta title status in page optimize
     * @param $tooltip_page
     * @return string
     */
    public function metaTitle($tooltip_page)
    {
        $output = $this->createFieldAnalysis(
                'metatitle',
                $tooltip_page['meta_title'],
                'warning',
                __('Meta title filled', 'wp-meta-seo'),
                0
            );

        return $output;
    }

    /**
     * load meta description status in page optimize
     * @param $tooltip_page
     * @return string
     */
    public function metaDesc($tooltip_page)
    {
        $output = $this->createFieldAnalysis(
                'metadesc',
                $tooltip_page['meta_desc'],
                'warning',
                __('Meta description filled', 'wp-meta-seo'),
                0
            );

        return $output;
    }

    /**
     * load image resize status in page optimize
     * @param $tooltip_page
     * @return string
     */
    public function imageResize($tooltip_page)
    {
        $output = $this->createFieldAnalysis(
                'imgresize',
                $tooltip_page['image_resize'],
                'warning',
                __('Wrong image resize', 'wp-meta-seo'),
                0
            );

        return $output;
    }

    /**
     * load image alt status in page optimize
     * @param $tooltip_page
     * @return string
     */
    public function imageAlt($tooltip_page)
    {
        $output = $this->createFieldAnalysis(
                'imgalt',
                $tooltip_page['image_alt'],
                'warning',
                __('Image have meta title or alt', 'wp-meta-seo'),
                0
            );

        return $output;
    }

    /**
     * Load page analysis
     * @param $post
     * @return string
     */
    public function pageAnalysis($post)
    {

        $tooltip_page = array();
        $tooltip_page['title_in_heading'] = __('Check if a word of this
         content title is also in a title heading (h1, h2...)', 'wp-meta-seo');
        $tooltip_page['title_in_content'] = __('Check if a word of this content
         title is also in the text', 'wp-meta-seo');
        $tooltip_page['page_url'] = __('Does the page title match with the permalink (URL structure)', 'wp-meta-seo');
        $tooltip_page['meta_title'] = __('Is the meta title of this page filled?', 'wp-meta-seo');
        $tooltip_page['meta_desc'] = __('Is the meta description of this page filled?', 'wp-meta-seo');
        $tooltip_page['image_resize'] = __('Check for image HTML resizing
         in content (usually image resized using handles)', 'wp-meta-seo');
        $tooltip_page['image_alt'] = __('Check for image Alt text and title', 'wp-meta-seo');

        $headingtitle = $this->headingTitle($tooltip_page);
        $contenttitle = $this->titleContent($tooltip_page);
        $pageurl = $this->pageUrl($tooltip_page);
        $metatitle = $this->metaTitle($tooltip_page);
        $metadesc = $this->metaDesc($tooltip_page);
        $imagewrong = $this->imageResize($tooltip_page);
        $imagealt = $this->imageAlt($tooltip_page);

        $output = '';

        $circliful = 0;
        $output .= '<div style="width:100%;float:left;">';
        $output .= '<div class="metaseo_left">
            <div class="metaseo-progress-bar" data-post_id="' . $post->ID . '">
                  <canvas id="inactiveProgress" class="metaseo-progress-inactive" height="275px" width="275px"></canvas>
              <canvas id="activeProgress" class="metaseo-progress-active"  height="275px" width="275px"></canvas>
              <p>0%</p>
            </div>
            <input type="hidden" id="progressController" value="' . $circliful . '" />
                <input type="hidden" id="metaseo_alanysis_ok" value="' . $this->perc_score . '" />
          </div>';

        $output .= '<div class="metaseo_right">';
        $output .= $headingtitle . $contenttitle . $pageurl . $metatitle . $metadesc . $imagewrong . $imagealt;
        $output .= '</div>';
        $output .= '</div>';
        return $output;
    }

     /**
    * Display html content for current tab
    * @param $id
    * @param $content
    */
    public function doTab($id, $content)
    {
        global $post;
        ?>
        <div class="wpmseotab <?php echo esc_attr($id) ?>">
            <?php if ($id == 'general') : ?>
                <p class="reload_analysis">
                <span class="spinner" style="float: left;"></span>
                <input type="button" name="reload_analysis" id="reload_analysis" class="button button-primary"
                 value="<?php _e('Reload analysis', 'wp-meta-seo'); ?>">
                </p>
            <?php endif; ?>
            <table class="form-table">
                <?php echo $content; ?>
            </table>
            <?php
            if ($id == 'general') {
                echo $this->pageAnalysis($post);
                $settings = get_option('_metaseo_settings');
                if (!empty($settings['metaseo_follow'])) {
                    $page_follow = get_post_meta($post->ID, '_metaseo_metafollow', true);
                    $slf = '<select class="metaseo_metabox_follow" data-post_id="' . $post->ID . '">';
                    if ($page_follow == 'nofollow') {
                        $slf .= '<option value="follow">' . __('Follow', 'wp-meta-seo') . '</option>';
                        $slf .= '<option selected value="nofollow">' . __('Nofollow', 'wp-meta-seo') . '</option>';
                    } else {
                        $slf .= '<option selected value="follow">' . __('Follow', 'wp-meta-seo') . '</option>';
                        $slf .= '<option value="nofollow">' . __('Nofollow', 'wp-meta-seo') . '</option>';
                    }
                    $slf .= '</select>';
                    echo '<p><span class="wpmslabel">' . __('Follow', 'wp-meta-seo') . '
                    <i class="material-icons alignright metaseo_help" id="deschelp"
                     alt="' . __('Nofollow provides a way for webmasters to tell search engines:
                      don\'t follow this link. So it may influence the link target’s ranking', 'wp-meta-seo') . '"
                      style="color:#32373C" data-hasqtip="2">chat_bubble</i></span>' . $slf . '</p>';
                }

                if (!empty($settings['metaseo_index'])) {
                    $page_index = get_post_meta($post->ID, '_metaseo_metaindex', true);
                    $sli = '<select class="metaseo_metabox_index" data-post_id="' . $post->ID . '">';
                    if ($page_index == 'noindex') {
                        $sli .= '<option value="index">' . __('Index', 'wp-meta-seo') . '</option>';
                        $sli .= '<option selected value="noindex">' . __('Noindex', 'wp-meta-seo') . '</option>';
                    } else {
                        $sli .= '<option selected value="index">' . __('Index', 'wp-meta-seo') . '</option>';
                        $sli .= '<option value="noindex">' . __('Noindex', 'wp-meta-seo') . '</option>';
                    }

                    $sli .= '</select>';
                    echo '<p><span class="wpmslabel">' . __('Index', 'wp-meta-seo') . '
                    <i class="material-icons alignright metaseo_help" id="deschelp"
                     alt="' . __('Allow search engines robots to index this content,
                      as default your content is indexed', 'wp-meta-seo') . '"
                      style="color:#32373C" data-hasqtip="2">chat_bubble</i></span>' . $sli . '</p>';
                }
            }
            ?>
        </div>
        <?php
    }

    /**
     * Display html content for current tab
     * @param $meta_field_def
     * @param string $key
     * @return string
     */
    public function doMetaBox($meta_field_def, $key = '')
    {
        wp_enqueue_style('metaseo-google-icon');
        $content = '';
        $esc_form_key = esc_attr(self::$form_prefix . $key);
        $post = $this->getMetaboxPost();
        $meta_value = self::getValue($key, $post->ID);

        $class = '';
        if (isset($meta_field_def['class']) && $meta_field_def['class'] !== '') {
            $class = ' ' . $meta_field_def['class'];
        }

        $placeholder = '';
        if (isset($meta_field_def['placeholder']) && $meta_field_def['placeholder'] !== '') {
            $placeholder = $meta_field_def['placeholder'];
        }

        switch ($meta_field_def['type']) {
            case 'snippetpreview':
                $content .= $this->snippet();
                break;

            case 'text':
                $ac = '';
                if (isset($meta_field_def['autocomplete']) && $meta_field_def['autocomplete'] === false) {
                    $ac = 'autocomplete="off" ';
                }
                if ($placeholder !== '') {
                    $placeholder = ' placeholder="' . esc_attr($placeholder) . '"';
                }
                $content .= '<input type="text"' . $placeholder . ' id="' . $esc_form_key . '" ' . $ac . '
                name="' . $esc_form_key . '" value="' . esc_attr($meta_value) . '"
                 class="large-text' . $class . '"/><br />';
                break;

            case 'textarea':
                $rows = 3;
                if (isset($meta_field_def['rows']) && $meta_field_def['rows'] > 0) {
                    $rows = $meta_field_def['rows'];
                }
                $content .= '<textarea class="large-text' . $class . '"
                 rows="' . esc_attr($rows) . '" id="' . $esc_form_key . '"
                  name="' . $esc_form_key . '">' . esc_textarea($meta_value) . '</textarea>';
                break;

            case 'select':
                if (isset($meta_field_def['options']) && is_array($meta_field_def['options'])
                 && $meta_field_def['options'] !== array()) {
                    $content .= '<select name="' . $esc_form_key . '" id="' . $esc_form_key . '"
                     class="metaseo' . $class . '">';
                    foreach ($meta_field_def['options'] as $val => $option) {
                        $selected = selected($meta_value, $val, false);
                        $content .= '<option ' . $selected . '
                         value="' . esc_attr($val) . '">' . esc_html($option) . '</option>';
                    }
                    unset($val, $option, $selected);
                    $content .= '</select>';
                }
                break;

            case 'multiselect':
                if (isset($meta_field_def['options']) && is_array($meta_field_def['options'])
                 && $meta_field_def['options'] !== array()) {
                    $selected_arr = $meta_value;
                    if ('meta-robots-adv' == $key) {
                        $selected_arr = explode(',', $meta_value);
                    }

                    if (!is_array($selected_arr)) {
                        $selected_arr = (array)$selected_arr;
                    }

                    $options_count = count($meta_field_def['options']);

                    $content .= '<select multiple="multiple" size="' . esc_attr($options_count) . '"
                     style="height: ' . esc_attr(($options_count * 20) + 4) . 'px;"
                      name="' . $esc_form_key . '[]" id="' . $esc_form_key . '" class="metaseo' . $class . '">';
                    foreach ($meta_field_def['options'] as $val => $option) {
                        $selected = '';
                        if (in_array($val, $selected_arr)) {
                            $selected = ' selected="selected"';
                        }
                        $content .= '<option ' . $selected . '
                         value="' . esc_attr($val) . '">' . esc_html($option) . '</option>';
                    }
                    $content .= '</select>';
                    unset($val, $option, $selected, $selected_arr, $options_count);
                }
                break;

            case 'checkbox':
                $checked = checked($meta_value, 'on', false);
                $expl = (isset($meta_field_def['expl'])) ? esc_html($meta_field_def['expl']) : '';
                $content .= '<label for="' . $esc_form_key . '"><input type="checkbox"
                 id="' . $esc_form_key . '" name="' . $esc_form_key . '" ' . $checked . '
                  value="on" class="metaseo' . $class . '"/> ' . $expl . '</label><br />';
                unset($checked, $expl);
                break;

            case 'radio':
                if (isset($meta_field_def['options']) && is_array($meta_field_def['options'])
                 && $meta_field_def['options'] !== array()) {
                    foreach ($meta_field_def['options'] as $val => $option) {
                        $checked = checked($meta_value, $val, false);
                        $content .= '<input type="radio" ' . $checked . '
                         id="' . $esc_form_key . '_' . esc_attr($val) . '" name="' . $esc_form_key . '"
                          value="' . esc_attr($val) . '"/> <label for="' . $esc_form_key . '_' . esc_attr($val) . '">
                          ' . esc_html($option) . '</label> ';
                    }
                    unset($val, $option, $checked);
                }
                break;

            case 'upload':
                $content .= '<input id="' . $esc_form_key . '" type="text" size="36" class="' . $class . '"
                 name="' . $esc_form_key . '" value="' . esc_attr($meta_value) . '" />';
                $content .= '<input id="' . $esc_form_key . '_button" class="wpmseo_image_upload_button button"
                 type="button" value="' . __('Upload Image', 'wp-meta-seo') . '" />';
                break;
        }


        $html = '';
        if ($content === '') {
            $content = apply_filters(
                'wpmseo_do_meta_box_field_' . $key,
                $content,
                $meta_value,
                $esc_form_key,
                $meta_field_def,
                $key
            );
        }

        if ($content !== '') {
            $label = esc_html($meta_field_def['title']);
            if (in_array($meta_field_def['type'], array(
                    'snippetpreview',
                    'radio',
                    'checkbox',
                ), true) === false
            ) {
                $label = '<label for="' . $esc_form_key . '">' . $label . ':</label>';
            }

            $help = '';
            if (isset($meta_field_def['help']) && $meta_field_def['help'] !== '') {
                $help = '<i class="material-icons alignright metaseo_help"
                 id="' . esc_attr($key . 'help') . '" alt="' . esc_attr($meta_field_def['help']) . '"
                  style="color:#32373C">chat_bubble</i>';
            }

            $html = '
                            <tr>
                                    <th scope="row">' . $label . $help . '</th>
                                    <td>';

            $html .= $content;

            if (isset($meta_field_def['description'])) {
                $html .= '<div>' . $meta_field_def['description'] . '</div>';
            }

            $html .= '
                                    </td>
                            </tr>';
        }
        return $html;
    }

    /**
     * @return array|mixed|null|WP_Post
     */
    private function getMetaboxPost()
    {
        if (isset($_GET['post'])) {
            $post_id = (int)$_GET['post'];
            $post = get_post($post_id);
        } else {
            $post = $GLOBALS['post'];
        }

        return $post;
    }

    /**
     * @return string
     */
    public function snippet()
    {
        $post = $this->getMetaboxPost();
        $title = self::getValue('title', $post->ID);
        $description = self::getValue('desc', $post->ID);

        $snippet_preview = new WPMSEOSnippetPreview($post, $title, $description);

        return $snippet_preview->getContent();
    }
}
