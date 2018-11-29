<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div id="menu_source_posts" class="wpms_source wpms_source_posts content-box">
    <h1 class="h1_top"><?php esc_html_e('Source : Post', 'wp-meta-seo') ?></h1>
    <div class="ju-settings-option">
        <div class="wpms_row_full">
            <label class="ju-setting-label text"
                   data-alt="<?php echo esc_attr('Include all elements in the sitemap', 'wp-meta-seo') ?>">
                <?php esc_html_e('Check all posts', 'wp-meta-seo') ?>
            </label>
            <div class="ju-switch-button">
                <label class="switch">
                    <input type="checkbox" class="sitemap_check_all" data-type="posts" id="wpms_check_all_posts"
                           value="1">
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="ju-settings-option">
        <div class="wpms_row_full">
            <label class="ju-setting-label text">
                <?php esc_html_e('Check all posts in current page', 'wp-meta-seo') ?>
            </label>
            <div class="ju-switch-button">
                <label class="switch">
                    <input type="checkbox" class="sitemap_check_all_posts_in_page" data-type="posts"
                           id="wpms_check_all_posts_in_page"
                           value="1">
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="ju-settings-option">
        <div class="wpms_row_full">
            <label class="ju-setting-label text wpms_width_100 wpms_left">
                <?php esc_html_e('Public name', 'wp-meta-seo') ?>
            </label>
            <p class="p-d-20">
                <label>
                    <input type="text" class="public_name_posts wpms-large-input wpms_width_100"
                           value="<?php echo esc_attr($sitemap->settings_sitemap['wpms_public_name_posts']) ?>">
                </label>
            </p>
        </div>
    </div>

    <div class="ju-settings-option wpms_xmp_custom_column">
        <div class="wpms_row_full">
            <label class="ju-setting-label text wpms_width_100 wpms_left"
                   data-alt="<?php echo esc_attr('Column selection if you’re using the HTML sitemap', 'wp-meta-seo') ?>">
                <?php esc_html_e('HTML Sitemap column', 'wp-meta-seo') ?>
            </label>
            <p class="p-d-20">
                <label>
                    <select class="wpms_display_column wpms_display_column_posts wpms-large-input wpms_width_100">
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
            </p>
        </div>
    </div>

    <div class="ju-settings-option wpms_xmp_order wpms_right m-r-0">
        <div class="wpms_row_full">
            <label class="ju-setting-label text wpms_width_100 wpms_left">
                <?php esc_html_e('Order', 'wp-meta-seo') ?>
            </label>
            <p class="p-d-20">
                <label>
                    <select class="wpms_display_order_posts wpms-large-input wpms_width_100">
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
            </p>
        </div>
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
            }

            if (in_array($post->cat_ID, $sitemap->settings_sitemap['wpms_category_link'])) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            ?>
            <div class="wpms_row_full">
                <div class="ju-settings-option wpms_row">
                    <div class="wpms_row_full">
                        <label class="ju-setting-label text wpms-uppercase">
                            <?php echo esc_html($post->cat_name) ?>
                        </label>
                        <div class="ju-switch-button">
                            <label class="switch">
                                <input class="sitemap_addlink_categories"
                                       id="<?php echo esc_attr('sitemap_addlink_categories_' . $post->cat_ID) ?>"
                                       type="checkbox"
                                       value="<?php echo esc_attr($post->cat_ID) ?>" <?php echo esc_html($checked) ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="ju-settings-option wpms_row">
                    <div class="wpms_row_full">
                        <label class="ju-setting-label text">
                            <?php esc_html_e('Select all', 'wp-meta-seo') ?>
                        </label>
                        <div class="ju-switch-button">
                            <label class="switch">
                                <input data-category="<?php echo esc_attr($post->taxo . $post->slug) ?>"
                                       class="xm_cb_all" id="<?php echo esc_attr($post->taxo . $post->slug) ?>"
                                       type="checkbox">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php

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
                        echo '<label for="' . esc_attr('wpms_sitemap_posts_' . $p->ID) . '" class="wpms-text">' . esc_html($title) . '</label>';
                        echo '</div>';
                    } else {
                        echo '<input class="wpms_sitemap_input_link" type="hidden"
                         data-type="post" value="' . esc_attr($permalink) . '">';
                        echo '<div class="pure-checkbox">';
                        echo '<input class="' . esc_attr('cb_sitemaps_posts wpms_xmap_posts ' . $post->taxo . $post->slug) . '"
                         id="' . esc_attr('wpms_sitemap_posts_' . $p->ID) . '" type="checkbox"
                          name="_metaseo_settings_sitemap[wpms_sitemap_posts]" value="' . esc_attr($p->ID) . '">';
                        echo '<label for="' . esc_attr('wpms_sitemap_posts_' . $p->ID) . '" class="wpms-text">' . esc_html($title) . '</label>';
                        echo '</div>';
                    }

                    echo '</div>';
                    // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in the method MetaSeoSitemap::viewPriority and MetaSeoSitemap::viewFrequency
                    echo '<div class="wpms_right">' . $slpr . $slfr . '</div>';
                    echo '</div>';
                }
            }

            if ($post->count_posts > 10) {
                echo '<a href="#open-popup-posts-list" class="open-popup-posts-list ju-button wpms-small-btn wpms_left m-t-10 see-more-posts" data-slug="' . esc_attr($post->slug) . '" data-category="' . esc_attr($post->cat_ID) . '"><i class="material-icons wpms-middle">arrow_right_alt</i><label>' . esc_html__('See more posts in this category', 'wp-meta-seo') . '</label></a>';
            }
        }
        ?>
    </div>

    <div id="open-popup-posts-list" class="white-popup mfp-hide">
        <div style="width: 100%; float: left; text-align: center">
            <img class="img-links-loader" src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . 'assets/images/ajax-loader.gif') ?>">
        </div>

        <div class="list_posts_sitemap">

        </div>
    </div>
    <div class="holder holder_posts"></div>
</div>