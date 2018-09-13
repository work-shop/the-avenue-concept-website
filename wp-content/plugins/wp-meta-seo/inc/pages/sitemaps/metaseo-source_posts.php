<div id="menu_source_posts" class="wpms_source wpms_source_posts">
    <div class="div_sitemap_check_all">
        <div class="pure-checkbox">
            <input class="sitemap_check_all" data-type="posts" id="wpms_check_all_posts" type="checkbox">
            <label for="wpms_check_all_posts"><?php esc_html_e('Check all posts', 'wp-meta-seo'); ?></label>
        </div>
    </div>

    <div class="div_sitemap_check_all">
        <div class="pure-checkbox">
            <input class="sitemap_check_all_posts_in_page" data-type="posts" id="wpms_check_all_posts_in_page"
                   type="checkbox">
            <label for="wpms_check_all_posts_in_page">
                <?php esc_html_e('Check all posts in current page', 'wp-meta-seo'); ?>
            </label>
        </div>
    </div>

    <div class="div_sitemap_check_all" style="font-weight: bold;">
        <label><?php esc_html_e('Public name', 'wp-meta-seo'); ?></label>
        <label>
            <input type="text" class="public_name_posts"
                   value="<?php echo esc_attr($sitemap->settings_sitemap['wpms_public_name_posts']) ?>">
        </label>
    </div>

    <div class="div_sitemap_check_all wpms_xmp_custom_column" style="font-weight: bold;">
        <label><?php esc_html_e('Display in column', 'wp-meta-seo'); ?></label>
        <label>
            <select class="wpms_display_column wpms_display_column_posts">
                <?php
                for ($i = 1; $i <= $sitemap->settings_sitemap['wpms_html_sitemap_column']; $i ++) {
                    if ((int) $sitemap->settings_sitemap['wpms_display_column_posts'] === (int) $i) {
                        echo '<option selected value="' . esc_attr($i) . '">' . esc_html($sitemap->columns[$i]) . '</option>';
                    } else {
                        echo '<option value="' . esc_attr($i) . '">' . esc_html($sitemap->columns[$i]) . '</option>';
                    }
                }
                ?>
            </select>
        </label>
    </div>

    <div class="div_sitemap_check_all wpms_xmp_order" style="font-weight: bold;">
        <label><?php esc_html_e('Order', 'wp-meta-seo'); ?></label>
        <label>
            <select class="wpms_display_order_posts">
                <?php
                for ($i = 1; $i <= 4; $i ++) {
                    if ((int) $sitemap->settings_sitemap['wpms_display_order_posts'] === (int) $i) {
                        echo '<option selected value="' . esc_attr($i) . '">' . esc_html($i) . '</option>';
                    } else {
                        echo '<option value="' . esc_attr($i) . '">' . esc_html($i) . '</option>';
                    }
                }
                ?>
            </select>
        </label>
    </div>

    <div id="wrap_sitemap_option_posts" class="wrap_sitemap_option">
        <?php
        $posts                    = $sitemap->getPosts();
        $check                    = array();
        $desclink_category_add    = esc_html__('Add link to category name', 'wp-meta-seo');
        $desclink_category_remove = esc_html__('Remove link to category name', 'wp-meta-seo');
        foreach ($posts as $post) {
            if (!in_array($post->taxo, $check)) {
                $check[] = $post->taxo;
                echo '<div class="wpms_row"><h1>' . esc_html($post->taxo) . '</h1></div>';
            }

            if (in_array($post->cat_ID, $sitemap->settings_sitemap['wpms_category_link'])) {
                echo '<div class="wpms_row"><h3>';
                echo '<div class="pure-checkbox">';
                echo '<input for="' . esc_attr($desclink_category_remove) . '"
                 class="sitemap_addlink_categories" id="' . esc_attr('sitemap_addlink_categories_' . $post->cat_ID) . '"
                  type="checkbox" value="' . esc_attr($post->cat_ID) . '" checked>';
                echo '<label for="' . esc_attr('sitemap_addlink_categories_' . $post->cat_ID) . '">' . esc_html($post->cat_name) . '</label>';
                echo '</div>';
                echo '</h3></div>';
            } else {
                echo '<div class="wpms_row"><h3>';
                echo '<div class="pure-checkbox">';
                echo '<input for="' . esc_attr($desclink_category_remove) . '"
                 class="sitemap_addlink_categories" id="' . esc_attr('sitemap_addlink_categories_' . $post->cat_ID) . '"
                  type="checkbox" value="' . esc_attr($post->cat_ID) . '">';
                echo '<label for="' . esc_attr('sitemap_addlink_categories_' . $post->cat_ID) . '">' . esc_html($post->cat_name) . '</label>';
                echo '</div>';
                echo '</h3></div>';
            }

            echo '<div class="wpms_row wpms_row_check_all_posts">';
            echo '<div class="pure-checkbox">';
            echo '<input data-category="' . esc_attr($post->taxo . $post->slug) . '"
             class="xm_cb_all" id="xm_cb_all" type="checkbox">';
            echo '<label for="xm_cb_all">' . esc_html__('Select all', 'wp-meta-seo') . '</label>';
            echo '</div>';
            echo '</div>';
            foreach ($post->results as $p) {
                $category = get_the_terms($p, $post->taxo);
                if ((int) $category[0]->term_id === (int) $post->cat_ID) {
                    if (empty($sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['frequency'])) {
                        $postfrequency = 'monthly';
                    } else {
                        $postfrequency = $sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['frequency'];
                    }
                    if (empty($sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['priority'])) {
                        $postpriority = '1.0';
                    } else {
                        $postpriority = $sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['priority'];
                    }
                    $slpr      = $sitemap->viewPriority(
                        'priority_posts_' . $p->ID,
                        '_metaseo_settings_sitemap[wpms_sitemap_posts][' . $p->ID . '][priority]',
                        $postpriority
                    );
                    $slfr      = $sitemap->viewFrequency(
                        'frequency_posts_' . $p->ID,
                        '_metaseo_settings_sitemap[wpms_sitemap_posts][' . $p->ID . '][frequency]',
                        $postfrequency
                    );
                    $permalink = get_permalink($p->ID);
                    echo '<div class="wpms_row wpms_row_record">';
                    echo '<div style="float:left;line-height:30px;min-width: 300px;">';
                    if (strlen($p->post_title) > 30) {
                        $title = substr($p->post_title, 0, 30);
                    } else {
                        $title = $p->post_title;
                    }
                    if (isset($sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['post_id'])
                        && (int) $sitemap->settings_sitemap['wpms_sitemap_posts'][$p->ID]['post_id'] === (int) $p->ID) {
                        echo '<input class="wpms_sitemap_input_link checked"
                         type="hidden" data-type="post" value="' . esc_attr($permalink) . '">';
                        echo '<div class="pure-checkbox">';
                        echo '<input class="' . esc_attr('cb_sitemaps_posts wpms_xmap_posts ' . $post->taxo . $post->slug) . '"
                         id="' . esc_attr('wpms_sitemap_posts_' . $p->ID) . '" type="checkbox"
                          name="_metaseo_settings_sitemap[wpms_sitemap_posts]" value="' . esc_attr($p->ID) . '" checked>';
                        echo '<label for="' . esc_attr('wpms_sitemap_posts_' . $p->ID) . '">' . esc_html($title) . '</label>';
                        echo '</div>';
                    } else {
                        echo '<input class="wpms_sitemap_input_link" type="hidden"
                         data-type="post" value="' . esc_attr($permalink) . '">';
                        echo '<div class="pure-checkbox">';
                        echo '<input class="' . esc_attr('cb_sitemaps_posts wpms_xmap_posts ' . $post->taxo . $post->slug) . '"
                         id="' . esc_attr('wpms_sitemap_posts_' . $p->ID) . '" type="checkbox"
                          name="_metaseo_settings_sitemap[wpms_sitemap_posts]" value="' . esc_attr($p->ID) . '">';
                        echo '<label for="' . esc_attr('wpms_sitemap_posts_' . $p->ID) . '">' . esc_html($title) . '</label>';
                        echo '</div>';
                    }

                    echo '</div>';
                    // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in the method MetaSeoSitemap::viewPriority and MetaSeoSitemap::viewFrequency
                    echo '<div style="margin-left:200px">' . $slpr . $slfr . '</div>';
                    echo '</div>';
                }
            }
        }
        ?>
    </div>
    <div class="holder holder_posts"></div>
</div>