<div id="menu_source_menus" class="wpms_source wpms_source_menu">
    <?php
    $terms = get_terms(array('taxonomy' => 'nav_menu', 'hide_empty' => true, 'orderby' => 'term_id', 'order' => 'ASC'));
    if (!empty($terms)) {
        echo '<div class="div_sitemap_check_all">';
        echo '<div class="pure-checkbox">';
        echo '<input class="sitemap_check_all" data-type="menu" id="wpms_check_all_menus" type="checkbox">';
        echo '<label for="wpms_check_all_menus">' . __("Check all menus", 'wp-meta-seo') . '</label>';
        echo '</div>';
        echo '</div>';
        ?>
        <div class="div_sitemap_check_all wpms_xmp_order" style="font-weight: bold;">
            <label><?php _e('Order', 'wp-meta-seo'); ?></label>
            <label>
                <select class="wpms_display_order_menus">
                    <?php
                    for ($i = 1; $i <= 4; $i++) {
                        if ($i == $sitemap->settings_sitemap['wpms_display_order_menus']) {
                            echo '<option selected value="' . $i . '">' . $i . '</option>';
                        } else {
                            echo '<option value="' . $i . '">' . $i . '</option>';
                        }
                    }
                    ?>
                </select>
            </label>
        </div>
        <?php
        foreach ($terms as $term) {
            $viewmenu = $sitemap->viewMenus($term);
        }
        echo '<div class="wrap_sitemap_option">';
        echo '<input name="_metaseo_settings_sitemap[wpms_check_firstsave]" type="hidden" value="1">';
        echo $viewmenu;
        echo '</div>';
    }
    ?>
</div>