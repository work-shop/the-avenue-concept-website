<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div id="menu_sitemaps" class="wpms_source wpms_source_sitemaps content-box">
    <h1 class="wpms-top-h1"><?php esc_html_e('Sitemap', 'wp-meta-seo') ?></h1>
    <div class="ju-settings-option min-height-140">
        <div class="wpms_row_full">
            <label class="ju-setting-label wpms_width_100 wpms_left" data-alt="<?php esc_attr_e('Link to the xml file generated. It’s highly recommended
                 to add this sitemap link to your Google search console', 'wp-meta-seo'); ?>">
                <?php esc_html_e('XML sitemap link	', 'wp-meta-seo') ?>
            </label>
            <p class="p-lr-20">
                <label>
                    <?php
                    $sitemap->sitemapLink();
                    ?>
                </label>
            </p>
        </div>
    </div>

    <div class="ju-settings-option min-height-140">
        <div class="wpms_row_full">
            <label class="ju-setting-label wpms_width_100 wpms_left" data-alt="<?php esc_attr_e('A page is automatically generated to display your HTML sitemap.
                 You can also use any of the existing pages', 'wp-meta-seo'); ?>">
                <?php esc_html_e('HTML Sitemap page', 'wp-meta-seo') ?>
            </label>
            <p class="p-lr-20">
                <label>
                    <?php
                    $sitemap->sitemapPage();
                    ?>
                </label>
            </p>
        </div>
    </div>

    <div class="ju-settings-option min-height-140">
        <div class="wpms_row_full">
            <label class="ju-setting-label wpms_width_100 wpms_left" data-alt="<?php esc_attr_e('The additional WordPress taxonomies that you want
                 to load in your sitemaps', 'wp-meta-seo'); ?>">
                <?php esc_html_e('Additional content', 'wp-meta-seo') ?>
            </label>
            <div class="p-lr-20">
                <?php
                $sitemap->sitemapTaxonomies();
                ?>
            </div>
        </div>
    </div>

    <div class="ju-settings-option min-height-140">
        <div class="wpms_row_full">
            <label class="ju-setting-label"
                   data-alt="<?php esc_attr_e('You can include a list of posts by author in your sitemaps', 'wp-meta-seo'); ?>">
                <?php esc_html_e('Display author posts', 'wp-meta-seo') ?>
            </label>
            <div class="ju-switch-button">
                <label class="switch">
                    <?php
                    $sitemap->sitemapAuthor();
                    ?>
                    <span class="slider round"></span>
                </label>
            </div>
            <p class="description text_left p-lr-20"><?php esc_html_e('You can include a list of posts by author in your sitemaps', 'wp-meta-seo'); ?></p>
        </div>
    </div>

    <div class="ju-settings-option min-height-140">
        <div class="wpms_row_full">
            <label class="ju-setting-label wpms_width_100 wpms_left" data-alt="<?php esc_attr_e('Number of columns of the HTML sitemap.
                 You can also setup where your content will be displayed using the tabs above', 'wp-meta-seo'); ?>">
                <?php esc_html_e('HTML Sitemap display', 'wp-meta-seo') ?>
            </label>
            <p class="p-lr-20">
                <label>
                    <?php
                    $sitemap->sitemapColumn();
                    ?>
                </label>
            </p>
        </div>
    </div>

    <?php if (is_plugin_active(WPMSEO_ADDON_FILENAME)) : ?>
        <div class="ju-settings-option min-height-140">
            <div class="wpms_row_full">
                <label class="ju-setting-label wpms_width_100 wpms_left" data-alt="<?php esc_attr_e('Define a display theme for the HTML sitemap on frontend, mainly to define how the multiple level menus would be opened', 'wp-meta-seo'); ?>">
                    <?php esc_html_e('HTML Sitemap theme', 'wp-meta-seo') ?>
                </label>
                <p class="p-lr-20">
                    <label>
                        <?php
                        $sitemap->sitemapTheme();
                        ?>
                    </label>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <div class="ju-settings-option min-height-140">
        <div class="wpms_row_full">
            <label class="ju-setting-label wpms_width_100 wpms_left" data-alt="<?php esc_attr_e('Once you’ve selected a HTML Sitemap page, if this page has already content in it, setup where you want to display your sitemap', 'wp-meta-seo'); ?>">
                <?php esc_html_e('HTML Sitemap Position', 'wp-meta-seo') ?>
            </label>
            <p class="p-lr-20">
                <label>
                    <?php
                    $sitemap->sitemapPosition();
                    ?>
                </label>
            </p>
        </div>
    </div>

    <div class="ju-settings-option min-height-140">
        <div class="wpms_row_full">
            <label class="ju-setting-label" data-alt="<?php esc_attr_e('You can include a link to your xml sitemap in the robot.txt.
                 It helps some search engines to find it', 'wp-meta-seo'); ?>">
                <?php esc_html_e('Sitemap and robot.txt', 'wp-meta-seo') ?>
            </label>
            <div class="ju-switch-button">
                <label class="switch">
                    <?php
                    if (is_multisite()) { ?>
                        <input id="wpms_sitemap_add" disabled="disabled" type="checkbox"
                               name="_metaseo_settings_sitemap[wpms_sitemap_add]"
                               value="1" <?php checked(1, $sitemap->settings_sitemap['wpms_sitemap_add']); ?>>
                    <?php } else { ?>
                        <!-- for robots.txt we need to use site_url instead home_url ! -->
                        <input id="wpms_sitemap_add" type="checkbox" name="_metaseo_settings_sitemap[wpms_sitemap_add]"
                               value="1" <?php checked(1, $sitemap->settings_sitemap['wpms_sitemap_add']); ?>>
                    <?php } ?>
                    <span class="slider round"></span>
                </label>
            </div>
            <?php
            if (is_multisite()) { ?>
                <p class="description text_left p-lr-20"><?php esc_html_e('add sitemap file path in robots.txt', 'wp-meta-seo'); ?></p>
                <p class="description text_left p-lr-20">
                    <?php esc_html_e('Since you are using multisite,
             the plugin does not allow to add a sitemap to robots.txt', 'wp-meta-seo'); ?>
                </p>
            <?php } else { ?>
                <!-- for robots.txt we need to use site_url instead home_url ! -->
                <p class="description text_left p-lr-20"><?php esc_html_e('add sitemap link in the', 'wp-meta-seo'); ?>
                    <a
                            href="<?php echo esc_url(site_url('/')); ?>robots.txt" target="_new" style="color: #ff8726">robots.txt</a>
                </p>
            <?php } ?>
        </div>
    </div>

    <div class="ju-settings-option min-height-140">
        <div class="wpms_row_full">
            <label class="ju-setting-label" data-alt="<?php esc_attr_e('Add a copy of the lastest version of your .xml sitemap at the root
                 of your WordPress install named sitemap.xml. Some SEO tools and search engines bots
                  are searching for it.', 'wp-meta-seo'); ?>">
                <?php esc_html_e('Sitemap root', 'wp-meta-seo') ?>
            </label>
            <div class="ju-switch-button">
                <label class="switch">
                    <input id="wpms_sitemap_root" type="checkbox" name="_metaseo_settings_sitemap[wpms_sitemap_root]"
                           value="1" <?php checked(1, $sitemap->settings_sitemap['wpms_sitemap_root']); ?>>
                    <span class="slider round"></span>
                </label>
            </div>
            <p class="description text_left p-lr-20"><?php esc_html_e('Add a sitemap.xml copy @ the site root', 'wp-meta-seo'); ?></p>
        </div>
    </div>

    <?php if (is_plugin_active(WPMSEO_ADDON_FILENAME)) : ?>
        <div class="ju-settings-option">
            <div class="wpms_row_full">
                <label class="ju-setting-label wpms_left" data-alt="<?php esc_attr_e('A page is automatically generated to display your
                     HTML sitemap. You can also use any of the existing pages.', 'wp-meta-seo'); ?>">
                    <?php esc_html_e('Sitemap link check', 'wp-meta-seo') ?>
                </label>
                <label class="wpms_right">
                    <?php
                    $sitemap->checkLink();
                    ?>
                </label>
            </div>
        </div>
    <?php endif; ?>

    <?php if (is_plugin_active(WPMSEO_ADDON_FILENAME) && (is_plugin_active('sitepress-multilingual-cms/sitepress.php') || is_plugin_active('polylang/polylang.php'))) : ?>
    <div class="ju-settings-option min-height-140" style="clear: both">
        <div class="wpms_row_full">
            <?php
            if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
                if (is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
                    ?>
                    <label class="ju-setting-label wpms_width_100 wpms_left" data-alt="<?php esc_attr_e('Select a language to include in your sitemap,
                         it will add the relative menu, post, page… content automatically', 'wp-meta-seo'); ?>">
                        <?php esc_html_e('WPML language', 'wp-meta-seo') ?>
                    </label>
                    <p class="p-lr-20">
                        <label>
                            <?php
                            $sitemap->sitemapIncludeLanguages();
                            ?>
                        </label>
                    </p>
                    <?php
                } elseif (is_plugin_active('polylang/polylang.php')) { ?>
                    <label class="ju-setting-label wpms_width_100 wpms_left" data-alt="<?php esc_attr_e('Select a language to include in your sitemap,
                         it will add the relative menu, post, page… content automatically', 'wp-meta-seo'); ?>">
                        <?php esc_html_e('Polylang language', 'wp-meta-seo') ?>
                    </label>
                    <p class="p-lr-20">
                        <label>
                            <?php
                            $sitemap->sitemapIncludeLanguages();
                            ?>
                        </label>
                    </p>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    <?php endif; ?>
</div>
