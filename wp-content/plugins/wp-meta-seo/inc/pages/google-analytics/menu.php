<?php if (!empty($this->google_alanytics)): ?>
    <ul class="wpmstabs wpms-nav-tab-wrapper">

        <li class="tab wpmstab col" style="min-width: 240px">
            <a href="<?php echo admin_url('admin.php?page=metaseo_google_analytics') ?>">
                <?php _e('Google Analytics Report', 'wp-meta-seo') ?>
            </a>
            <?php
            if (empty($_GET['view'])) {
                echo '<div class="indicator" style="bottom: 0; left: 0;width:100%"></div>';
            }
            ?>
        </li>

        <li class="tab wpmstab col">
            <a href="<?php echo admin_url('admin.php?page=metaseo_google_analytics&view=wpmsga_trackcode') ?>">
                <?php _e('Tracking code', 'wp-meta-seo') ?>
            </a>
            <?php
            if (isset($_GET['view']) && $_GET['view'] == 'wpmsga_trackcode') {
                echo '<div class="indicator" style="bottom: 0; left: 0;width:100%"></div>';
            }
            ?>
        </li>
    </ul>
<?php endif; ?>