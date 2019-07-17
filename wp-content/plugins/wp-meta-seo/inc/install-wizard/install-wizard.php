<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once(WPMETASEO_PLUGIN_DIR . '/inc/install-wizard/handler-wizard.php');
/**
 * Class WpmsInstallWizard
 */
class WpmsInstallWizard
{
    /**
     * Init step params
     *
     * @var array
     */
    protected $steps = array(
            'environment' => array(
                    'name' => 'Environment Check',
                    'view' => 'viewEvironment',
                    'action' => 'saveEvironment'
            ),
            'meta_information' => array(
                    'name' => 'Meta information',
                    'view' => 'viewMetaInfos',
                    'action' => 'saveMetaInfos'
            ),
            'google_analytics' => array(
                    'name' => 'Google Analytics',
                    'view' => 'viewGoogleAnalytics',
                    'action' => 'saveGoogleAnalytics',
            ),
            'social_meta' => array(
                'name' => 'Social',
                'view' => 'viewSocial',
                'action' => 'saveSocial',
            )
    );
    /**
     * Init current step params
     *
     * @var array
     */
    protected $current_step = array();
    /**
     * WpmsInstallWizard constructor.
     */
    public function __construct()
    {
        if (current_user_can('manage_options')) {
            add_action('admin_menu', array($this, 'adminMenus'));
            add_action('admin_init', array($this, 'runWizard'));
        }
    }
    /**
     * Add admin menus/screens.
     *
     * @return void
     */
    public function adminMenus()
    {
        add_dashboard_page('', '', 'manage_options', 'wpms-setup', '');
    }

    /**
     * Execute wizard
     *
     * @return void
     */
    public function runWizard()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- View request, no action
        wp_enqueue_style(
            'wpms-material-icons',
            'https://fonts.googleapis.com/icon?family=Material+Icons'
        );

        wp_enqueue_style(
            'wpms-install-style',
            WPMETASEO_PLUGIN_URL  . 'inc/install-wizard/install-wizard.css',
            array(),
            WPMSEO_VERSION
        );

        wp_enqueue_style(
            'wpms-main-style',
            WPMETASEO_PLUGIN_URL  . 'assets/css/main.css',
            array(),
            WPMSEO_VERSION
        );

        // Get step
        $this->steps = apply_filters('wpms_setup_wizard_steps', $this->steps);
        $this->current_step  = isset($_GET['step']) ? sanitize_key($_GET['step']) : current(array_keys($this->steps));

        // Save action
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- View request, no action
        if (!empty($_POST['wpms_save_step']) && isset($this->steps[$this->current_step]['action'])) {
            call_user_func(array('WpmsHandlerWizard', $this->steps[$this->current_step]['action']), $this->current_step);
        }

        // Render
        $this->setHeader();
        if (!isset($_GET['step'])) {
            require_once(WPMETASEO_PLUGIN_DIR . '/inc/install-wizard/content/viewWizard.php');
        } elseif (isset($_GET['step']) && $_GET['step'] === 'wizard_done') {
            require_once(WPMETASEO_PLUGIN_DIR . '/inc/install-wizard/content/viewDone.php');
        } else {
            $this->setMenu();
            $this->setContent();
        }
        $this->setFooter();
        // phpcs:enable
        exit();
    }


    /**
     * Get next link step
     *
     * @param string $step Current step
     *
     * @return string
     */
    public function getNextLink($step = '')
    {
        if (!$step) {
            $step = $this->current_step;
        }

        $keys = array_keys($this->steps);

        if (end($keys) === $step) {
            return add_query_arg('step', 'wizard_done', remove_query_arg('activate_error'));
        }

        $step_index = array_search($step, $keys, true);
        if (false === $step_index) {
            return '';
        }

        return add_query_arg('step', $keys[$step_index + 1], remove_query_arg('activate_error'));
    }

    /**
     * Output the menu for the current step.
     *
     * @return void
     */
    public function setMenu()
    {
        $output_steps = $this->steps;
        ?>
        <div class="wpms-wizard-steps">
            <ul class="wizard-steps">
                <?php
                $i = 0;
                foreach ($output_steps as $key => $step) {
                    $position_current_step = array_search($this->current_step, array_keys($this->steps), true);
                    $position_step = array_search($key, array_keys($this->steps), true);
                    $is_visited = $position_current_step > $position_step;
                    $i ++;
                    if ($key === $this->current_step) {
                        ?>
                        <li class="actived"><div class="layer"><?php echo esc_html($i) ?></div></li>
                        <?php
                    } elseif ($is_visited) {
                        ?>
                        <li class="visited">
                            <a href="<?php echo esc_url(add_query_arg('step', $key, remove_query_arg('activate_error'))); ?>">
                                <div class="layer"><?php echo esc_html($i) ?></div></a>
                        </li>
                        <?php
                    } else {
                        ?>
                        <li><div class="layer"><?php echo esc_html($i) ?></div></li>
                        <?php
                    }
                }
                ?>
            </ul>
        </div>
        <?php
    }


    /**
     * Output the content for the current step.
     *
     * @return void
     */
    public function setContent()
    {
        echo '<div class="">';
        if (!empty($this->steps[$this->current_step]['view'])) {
            require_once(WPMETASEO_PLUGIN_DIR . '/inc/install-wizard/content/' . $this->steps[$this->current_step]['view'] . '.php');
        }
        echo '</div>';
    }

    /**
     * Setup Wizard Header.
     *
     * @return void
     */
    public function setHeader()
    {
        set_current_screen();
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php esc_html_e('WP Meta SEO &rsaquo; Setup Wizard', 'wp-meta-seo'); ?></title>
            <?php do_action('admin_print_styles'); ?>
            <?php do_action('admin_head'); ?>
        </head>
        <body class="wpms-wizard-setup wp-core-ui">
        <div class="wpms-wizard-content p-d-20">
        <?php
    }

    /**
     * Setup Wizard Footer.
     *
     * @return void
     */
    public function setFooter()
    {
        ?>
        </div>
        </body>
        <?php wp_print_footer_scripts(); ?>
        </html>
        <?php
    }
}

new WpmsInstallWizard();
