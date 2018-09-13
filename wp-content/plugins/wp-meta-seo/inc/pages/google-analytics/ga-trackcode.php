<div id="post-body-content">
    <div class="settings-wrapper">
        <?php
        $lists_dimension = array(
            'Disabled',
            'dimension 1',
            'dimension 2',
            'dimension 3',
            'dimension 4',
            'dimension 5',
            'dimension 6',
            'dimension 7',
            'dimension 8',
            'dimension 9',
            'dimension 10',
            'dimension 11',
            'dimension 12',
            'dimension 13',
            'dimension 14',
            'dimension 15',
            'dimension 16',
            'dimension 17',
            'dimension 18',
            'dimension 19',
            'dimension 20'
        );

        $trackExclude = $this->ga_tracking['wpmsga_track_exclude'];

        if (empty($this->ga_tracking['wpmsga_dash_tracking'])) {
            echo '<div class="error"><p>' . esc_html__('The tracking component is disabled.
 You should set Tracking Options to Enabled', 'wp-meta-seo') . '.</p></div>';
        }

        if (empty($this->google_alanytics['tableid_jail'])) {
            echo "<div class='error'><p>";
            esc_html_e('You have to select a profile: ', 'wp-meta-seo');
            echo '<a href="' . esc_url(admin_url('admin.php?page=metaseo_google_analytics&view=wpmsga_trackcode')) . '">';
            esc_html_e('authorize the plugin', 'wp-meta-seo');
            echo '</a></p></div>';
        }

        require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/google-analytics/menu.php');
        ?>
        <div class="inside">
            <form method="post" action="">
                <div id="wpmsga-basic">
                    <table class="wpmsga-settings-options">
                        <tbody>
                        <tr>
                            <td colspan="2"><h2><?php esc_html_e('Tracking Settings', 'wp-meta-seo') ?></h2></td>
                        </tr>
                        <tr>
                            <td class="wpmsga-settings-title"><label
                                        for="wpmsga_dash_tracking">
                                    <?php esc_html_e('Tracking Options:', 'wp-meta-seo') ?>
                                </label>
                            </td>
                            <td>
                                <select id="wpmsga_dash_tracking"
                                        name="_metaseo_ggtracking_settings[wpmsga_dash_tracking]"
                                        onchange="this.form.submit()">
                                    <option value="0" <?php selected($this->ga_tracking['wpmsga_dash_tracking'], 0) ?>>
                                        <?php esc_html_e('Disabled', 'wp-meta-seo') ?>
                                    </option>
                                    <option value="1" <?php selected($this->ga_tracking['wpmsga_dash_tracking'], 1) ?>>
                                        <?php esc_html_e('Enabled', 'wp-meta-seo') ?>
                                    </option>
                                </select>
                                <div class="wpmsga_clear_author">
                                    <input type="button" name="wpmsClearauthor"
                                           class="wpmsClearauthor button button-secondary"
                                           value="<?php esc_attr_e('!Remove tracking authorization!', 'wp-meta-seo') ?>">
                                    <span class="spinner"></span>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="wpmsga-settings-title"><label
                                        for="wpmsga_dash_tracking">
                                    <?php esc_html_e('Analytics profile:', 'wp-meta-seo') ?>
                                </label>
                            </td>
                            <td>
                                <label>
                                    <input type="hidden" name="wpms_nonce" value="<?php echo esc_attr(wp_create_nonce('wpms_nonce')) ?>">
                                    <select id="tableid_jail" name="tableid_jail">
                                        <?php
                                        echo '<option value="0">' . esc_html__('Select a profile', 'wp-meta-seo') . '</option>';
                                        if (!empty($this->google_alanytics['profile_list'])) {
                                            foreach ($this->google_alanytics['profile_list'] as $items) {
                                                if ($items[3]) {
                                                    echo '<optgroup
                                                 label="' . esc_attr(WpmsGaTools::stripProtocol($items[3])) . '">';
                                                    if (isset($this->google_alanytics['tableid_jail'])
                                                        && $this->google_alanytics['tableid_jail'] === $items[1]) {
                                                        echo '<option value="' . esc_attr($items[1]) . '" selected';
                                                        echo '>' . esc_html($items[0]) . '</option>';
                                                    } else {
                                                        echo '<option value="' . esc_attr($items[1]) . '" ';
                                                        echo '>' . esc_html($items[0]) . '</option>';
                                                    }

                                                    echo '</optgroup >';
                                                }
                                            }
                                        } else {
                                            echo '<option value="">
' . esc_html__('Property not found', 'wp-meta-seo') . '</option>';
                                        }
                                        ?>
                                    </select>
                                </label>
                            </td>
                        </tr>


                        <tr>
                            <td class="wpmsga-settings-title"></td>
                            <td>
                                <?php
                                if (isset($this->google_alanytics['tableid_jail'])
                                    && isset($this->google_alanytics['profile_list'])) {
                                    $profile_info = WpmsGaTools::getSelectedProfile(
                                        $this->google_alanytics['profile_list'],
                                        $this->google_alanytics['tableid_jail']
                                    );
                                    if (!empty($profile_info[0])
                                        && !empty($this->ga_tracking['wpmsga_dash_tracking'])) {
                                        echo '<pre>View Name:	' . esc_html($profile_info[0]) . '<br>
Tracking ID:	' . esc_html($profile_info[2]) . '<br>
Default URL:	' . esc_html($profile_info[3]) . '<br>
Time Zone:	' . esc_html($profile_info[5]) . '</pre>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <hr>
                                <h2><?php esc_html_e('Basic Tracking', 'wp-meta-seo') ?></h2></td>
                        </tr>
                        <tr>
                            <td class="wpmsga-settings-title"><label
                                        for="wpmsga_dash_tracking_type">
                                    <?php esc_html_e('Tracking Type:', 'wp-meta-seo') ?></label>
                            </td>
                            <td><select id="wpmsga_dash_tracking_type"
                                        name="_metaseo_ggtracking_settings[wpmsga_dash_tracking_type]">
                                    <option value="classic"
                                        <?php selected($this->ga_tracking['wpmsga_dash_tracking_type'], 'classic') ?>>
                                        <?php esc_html_e('Classic Analytics', 'wp-meta-seo') ?></option>
                                    <option value="universal"
                                        <?php selected($this->ga_tracking['wpmsga_dash_tracking_type'], 'universal') ?>>
                                        <?php esc_html_e('Universal Analytics', 'wp-meta-seo') ?></option>
                                </select></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="wpmsga-settings-title">
                                <label class="wpms_label_100">
                                    <?php esc_html_e(' anonymize IPs while tracking', 'wp-meta-seo'); ?>
                                </label>
                                <div class="switch-optimization">
                                    <label class="switch switch-optimization">
                                        <input type="checkbox"
                                               name="_metaseo_ggtracking_settings[wpmsga_dash_anonim]"
                                            <?php checked($this->ga_tracking['wpmsga_dash_anonim'], 1) ?>
                                               value="1" class="wpmsga-settings-switchoo-checkbox"
                                               id="wpmsga_dash_anonim">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="wpmsga-settings-title">
                                <label class="wpms_label_100">
                                    <?php
                                    esc_html_e(' enable remarketing, demographics and interests reports', 'wp-meta-seo');
                                    ?>
                                </label>
                                <div class="switch-optimization">
                                    <label class="switch switch-optimization">
                                        <input type="checkbox"
                                               name="_metaseo_ggtracking_settings[wpmsga_dash_remarketing]"
                                            <?php checked($this->ga_tracking['wpmsga_dash_remarketing'], 1) ?>
                                               value="1" class="wpmsga-settings-switchoo-checkbox"
                                               id="wpmsga_dash_remarketing">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div id="wpmsga-events">
                    <table class="wpmsga-settings-options">
                        <tbody>
                        <tr>
                            <td colspan="2"><h2><?php esc_html_e('Events Tracking', 'wp-meta-seo') ?></h2></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="wpmsga-settings-title">
                                <label class="wpms_label_100">
                                    <?php esc_html_e(' track downloads, mailto and outbound links', 'wp-meta-seo'); ?>
                                </label>
                                <div class="switch-optimization">
                                    <label class="switch switch-optimization">
                                        <input type="checkbox"
                                               name="_metaseo_ggtracking_settings[wpmsga_event_tracking]"
                                            <?php checked($this->ga_tracking['wpmsga_event_tracking'], 1) ?>
                                               value="1" class="wpmsga-settings-switchoo-checkbox"
                                               id="wpmsga_event_tracking">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="wpmsga-settings-title"><label
                                        for="wpmsga_event_downloads">
                                    <?php esc_html_e('Downloads Regex:', 'wp-meta-seo') ?>
                                </label>
                            </td>
                            <td><input type="text" id="wpmsga_event_downloads"
                                       name="_metaseo_ggtracking_settings[wpmsga_event_downloads]"
                                       value="<?php echo esc_attr($this->ga_tracking['wpmsga_event_downloads']) ?>" size="50">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div id="wpmsga-exclude">
                    <table class="wpmsga-settings-options">
                        <tbody>
                        <tr>
                            <td colspan="2"><h2><?php esc_html_e('Exclude Tracking', 'wp-meta-seo') ?></h2></td>
                        </tr>
                        <tr>
                            <td class="roles wpmsga-settings-title"><label
                                        for="wpmsga_track_exclude">
                                    <?php esc_html_e('Exclude tracking for:', 'wp-meta-seo') ?>
                                </label>
                            </td>
                            <td class="wpmsga-settings-roles">
                                <table>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <div class="pure-checkbox">
                                                <input name="_metaseo_ggtracking_settings[wpmsga_track_exclude][]"
                                                    <?php
                                                    echo (in_array('administrator', $trackExclude)) ? 'checked' : ''
                                                    ?>
                                                       value="administrator" id="wpmsga_track_exclude_administrator"
                                                       type="checkbox">
                                                <label for="wpmsga_track_exclude_administrator">
                                                    <?php esc_html_e('Administrator', 'wp-meta-seo'); ?></label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="pure-checkbox">
                                                <input name="_metaseo_ggtracking_settings[wpmsga_track_exclude][]"
                                                    <?php echo (in_array('editor', $trackExclude)) ? 'checked' : '' ?>
                                                       value="editor" id="wpmsga_track_exclude_editor" type="checkbox">
                                                <label for="wpmsga_track_exclude_editor">
                                                    <?php esc_html_e('Editor', 'wp-meta-seo'); ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="pure-checkbox">
                                                <input name="_metaseo_ggtracking_settings[wpmsga_track_exclude][]"
                                                    <?php echo (in_array('author', $trackExclude)) ? 'checked' : '' ?>
                                                       value="author" id="wpmsga_track_exclude_author" type="checkbox">
                                                <label for="wpmsga_track_exclude_author">
                                                    <?php esc_html_e('Author', 'wp-meta-seo'); ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="pure-checkbox">
                                                <input name="_metaseo_ggtracking_settings[wpmsga_track_exclude][]"
                                                    <?php
                                                    echo (in_array('contributor', $trackExclude)) ? 'checked' : ''
                                                    ?>
                                                       value="contributor" id="wpmsga_track_exclude_contributor"
                                                       type="checkbox">
                                                <label for="wpmsga_track_exclude_contributor">
                                                    <?php esc_html_e('Contributor', 'wp-meta-seo'); ?>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="pure-checkbox">
                                                <input name="_metaseo_ggtracking_settings[wpmsga_track_exclude][]"
                                                    <?php echo in_array('subscriber', $trackExclude) ? 'checked' : '' ?>
                                                       value="subscriber" id="wpmsga_track_exclude_subscriber"
                                                       type="checkbox">
                                                <label for="wpmsga_track_exclude_subscriber">
                                                    <?php esc_html_e('Subscriber', 'wp-meta-seo'); ?>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <table class="wpmsga-settings-options">
                    <tbody>
                    <tr>
                        <td colspan="2">
                            <hr>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="submit"><input type="submit" name="Submit" class="wpmsbtn"
                                                              value="Save Changes"></td>
                    </tr>
                    </tbody>
                </table>
                <input type="hidden" name="_metaseo_ggtracking_settings[wpmsga_dash_hidden]" value="Y">
                <?php wp_nonce_field('gadash_form', 'gadash_security'); ?>
            </form>

        </div>
    </div>
</div>