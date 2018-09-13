<h1><?php esc_html_e('Sitemap', 'wp-meta-seo') ?></h1>
<div class="wpmsrow">
    <div class="col s12">
        <ul class="tabs wpmstabs">
            <li class="tab wpmstab col active">
                <a href="#menu_sitemaps"><?php esc_html_e('Sitemaps', 'wp-meta-seo') ?></a>
            </li>
            <li class="tab wpmstab col">
                <a href="#menu_source_menus"><?php esc_html_e('Source: menu', 'wp-meta-seo') ?></a>
            </li>
            <li class="tab wpmstab col">
                <a href="#menu_source_posts"><?php esc_html_e('Source: posts', 'wp-meta-seo') ?></a>
            </li>
            <li class="tab wpmstab col">
                <a href="#menu_source_pages"><?php esc_html_e('Source: pages', 'wp-meta-seo') ?></a>
            </li>
            <?php if (is_plugin_active(WPMSEO_ADDON_FILENAME)) : ?>
                <li class="tab wpmstab col" data-tab="custom_url"><a
                            href="#menu_custom_url"><?php esc_html_e('Custom URL', 'wp-meta-seo') ?></a></li>
            <?php endif; ?>
            <?php
            if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
                if (!empty($custom_post_types)) {
                    foreach ($custom_post_types as $post_type => $label) {
                        echo '<li class="tab wpmstab col">';
                        echo '<a href="' . esc_attr('#menu_source_' . $post_type) . '">' . esc_html(ucfirst($label)) . '</a>';
                        echo '</li>';
                    }
                }
            }
            ?>
        </ul>
    </div>
</div>