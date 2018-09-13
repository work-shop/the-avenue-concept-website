<div class="wrap">
    <?php
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/google-analytics/menu.php');
    ?>
    <h2 class="wpms_uppercase"><?php esc_html_e('Google Analytics tracking & report', 'wp-meta-seo') ?></h2>
    <p><?php esc_html_e('Enable Google Analytics tracking and reports using a Google Analytics
     direct connection. Require a Google Analytics login', 'wp-meta-seo') ?></p>
    <p>
        <a class="wpmsbtn wpmsbtn_small wpmsbtn_secondary" href="<?php echo esc_url($authUrl) ?>"
           target="_blank"><?php esc_html_e('Generate Access Code', 'wp-meta-seo') ?></a>
    </p>
    <form name="input" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
        <?php wp_nonce_field('gadash_form', 'gadash_security'); ?>
        <table class="wpms-settings-options">
            <tr>
                <td class="wpms-settings-title">
                    <label for="wpms_ga_code"
                                       title="<?php esc_attr_e('Use the red link to get your access code!', 'wp-meta-seo') ?>">
                        <?php esc_html_e('Access Code:', 'wp-meta-seo'); ?></label>
                </td>
                <td>
                    <input type="text" id="ga_dash_code" name="wpms_ga_code" value="" size="61"
                           title="<?php esc_attr_e('Use the red link to get your access code!', 'wp-meta-seo') ?>">
                    <input type="hidden" name="wpms_nonce" value="<?php echo esc_attr(wp_create_nonce('wpms_nonce')) ?>">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="pure-checkbox">
                        <input id="wpmsga_dash_userapi" type="checkbox" name="wpmsga_dash_userapi"
                               value="1" <?php checked($this->google_alanytics['wpmsga_dash_userapi'], 1) ?>>
                        <label class="metaseo_tool" for="wpmsga_dash_userapi"
                               alt="<?php esc_attr_e('You have the option to create your own Google developer
                                project and use your own API key for tracking (optional)', 'wp-meta-seo') ?>">
                            <?php esc_html_e(' Use your own API Project credentials', 'wp-meta-seo') ?></label>
                    </div>
                </td>
            </tr>
        </table>
        <div class="wpms_wrap_hr">
            <hr class="wpms_hr">
        </div>
        <h2 class="wpms_uppercase"><?php esc_html_e('Google analytics tracking only', 'wp-meta-seo') ?></h2>
        <p><?php esc_html_e('Enable Google Analytics tracking only. You won\'t be
         able to display statistics in your', 'wp-meta-seo') ?></p>
        <p><?php esc_html_e('Wordpress admin, only on Google Analytics website', 'wp-meta-seo') ?></p>

        <table class="wpms-settings-options">
            <tr>
                <td class="wpms-settings-title"><label for="wpms_ga_uax_reference"
                                                       title="<?php esc_attr_e('Analytics UA-X reference', 'wp-meta-seo') ?>">
                        <?php esc_html_e('Analytics UA-X reference:', 'wp-meta-seo'); ?></label>
                </td>
                <td>
                    <input type="text" id="wpms_ga_uax_reference" name="_metaseo_ga_disconnect[wpms_ga_uax_reference]"
                           value="<?php echo esc_attr($this->gaDisconnect['wpms_ga_uax_reference']) ?>" size="61">
                    <input type="hidden" name="wpms_nonce" value="<?php echo esc_attr(wp_create_nonce('wpms_nonce')) ?>">
                </td>
            </tr>
            <tr>
                <td class="wpms-settings-title"><label for="wpms_ga_uax_reference"
                                                       title="<?php esc_attr_e('Analytics tracking type', 'wp-meta-seo') ?>">
                        <?php esc_html_e('Analytics tracking type', 'wp-meta-seo'); ?></label>
                </td>
                <td>
                    <label>
                        <select id="wpmsga_dash_tracking_type" name="_metaseo_ga_disconnect[wpmsga_dash_tracking_type]">
                            <option value="classic"
                                <?php selected($this->gaDisconnect['wpmsga_dash_tracking_type'], 'classic') ?>>
                                <?php esc_html_e('Classic Analytics', 'wp-meta-seo') ?>
                            </option>
                            <option value="universal"
                                <?php selected($this->gaDisconnect['wpmsga_dash_tracking_type'], 'universal') ?>>
                                <?php esc_html_e('Universal Analytics', 'wp-meta-seo') ?>
                            </option>
                        </select>
                    </label>
                </td>
            </tr>
        </table>
        <p class="description">
            <?php esc_html_e('If you are using Universal Analytics make sure
             you have changed your account to a Universal Analytics', 'wp-meta-seo') ?>
        </p>
        <p class="description">
            <?php esc_html_e('property in Google Analytics Read more about Universal Analytics ', 'wp-meta-seo') ?>
            <a target="_blank" href="https://developers.google.com/analytics/devguides/collection/upgrade/">here</a>
        </p>
        <p><?php esc_html_e('OR use Analytics JS code', 'wp-meta-seo') ?></p>
        <label>
            <textarea name="_metaseo_ga_disconnect[wpmsga_code_tracking]"
                      class="wpmsga_code_tracking">
            <?php echo esc_textarea($this->gaDisconnect['wpmsga_code_tracking']); ?>
        </textarea>
        </label>
        <p>
            <input type="submit" class="wpmsbtn wpmsga_authorize" name="ga_dash_authorize"
                   value="<?php esc_attr_e('Save Changes', 'wp-meta-seo'); ?>"/>
        </p>
    </form>
</div>