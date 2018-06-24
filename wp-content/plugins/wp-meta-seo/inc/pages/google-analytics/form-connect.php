<form name="ga_dash_form" method="post" action="">
    <?php wp_nonce_field('gadash_form', 'gadash_security'); ?>
    <table class="gadwp-settings-options">
        <tbody>
        <tr>
            <td colspan="2">
                <h2><?php _e('Plugin Authorization', 'wp-meta-seo') ?></h2>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="pure-checkbox">
                    <input id="wpmsga_dash_userapi" type="checkbox" name="wpmsga_dash_userapi"
                           value="1" <?php checked($this->google_alanytics['wpmsga_dash_userapi'], 1) ?>>
                    <label class="metaseo_tool" for="wpmsga_dash_userapi"
                           alt="<?php _e('You have the option to create your own Google developer
                            project and use your own API key for tracking (optional)', 'wp-meta-seo') ?>">
                        <?php _e(' Use your own API Project credentials', 'wp-meta-seo') ?>
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <td class="gadwp-settings-title"><label><?php _e('Client ID:', 'wp-meta-seo') ?></label></td>
            <td>
                <label>
                    <?php
                    if ((!empty($this->google_alanytics['wpmsga_dash_clientid']))) {
                        $value = $this->google_alanytics['wpmsga_dash_clientid'];
                    } else {
                        $value = '';
                    }
                    ?>
                    <input type="text" name="wpmsga_dash_clientid"
                           value="<?php echo $value ?>"
                           size="40" required="required">
                </label>
            </td>
        </tr>
        <tr>
            <td class="gadwp-settings-title"><label><?php _e('Client Secret:', 'wp-meta-seo') ?></label></td>
            <td>
                <label>
                    <?php
                    if ((!empty($this->google_alanytics['wpmsga_dash_clientsecret']))) {
                        $value = $this->google_alanytics['wpmsga_dash_clientsecret'];
                    } else {
                        $value = '';
                    }
                    ?>
                    <input type="text" name="wpmsga_dash_clientsecret"
                           value="<?php echo $value ?>"
                           size="40" required="required">
                </label>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <hr>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="submit" name="Authorize" class="wpmsbtn" id="authorize"
                       value="<?php _e('Save Changes', 'wp-meta-seo') ?>">
            </td>
        </tr>
        </tbody>
    </table>
</form>

<?php
if (!empty($this->google_alanytics['wpmsga_dash_clientid'])
    && !empty($this->google_alanytics['wpmsga_dash_clientsecret'])) :
    ?>
    <form name="input" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
        <?php wp_nonce_field('gadash_form', 'gadash_security'); ?>
        <table class="wpms-settings-options">
            <tr>
                <td colspan="2" class="wpms-settings-info">
                    <?php echo __("Use this link to get your access code:", 'wp-meta-seo') . '
                     <a href="' . $authUrl . '" id="gapi-access-code"
                      target="_blank">' . __("Get Access Code", 'wp-meta-seo') . '</a>.'; ?>
                </td>
            </tr>
            <tr>
                <td class="wpms-settings-title">
                    <label for="wpms_ga_code"
                           title="<?php _e("Use the red link to get your access code!", 'wp-meta-seo') ?>">
                        <?php _e("Access Code:", 'wp-meta-seo'); ?></label>
                </td>
                <td><input type="text" id="ga_dash_code" name="wpms_ga_code" value="" size="61" required="required"
                           title="<?php _e("Use the red link to get your access code!", 'wp-meta-seo') ?>"></td>
            </tr>

            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" class="button button-secondary wpmsga_authorize" name="ga_dash_authorize"
                           value="<?php _e("Save Access Code", 'wp-meta-seo'); ?>"/>
                </td>
            </tr>
        </table>
    </form>
    <?php
endif;
?>