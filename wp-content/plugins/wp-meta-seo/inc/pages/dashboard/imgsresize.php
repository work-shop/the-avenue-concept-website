<div class="panel panel-danger-full panel-updates">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-7 col-lg-8">
                <h4 class="panel-title text-warning"><?php _e('HTML image resizing', 'wp-meta-seo') ?></h4>
                <h3><?php echo $results['imgs_statis'][2] . '%' ?></h3>
                <div class="progress">
                    <div style="width: <?php echo $results['imgs_statis'][2] . '%' ?>" aria-valuemax="100"
                         aria-valuemin="0" aria-valuenow="<?php echo $results['imgs_statis'][2] ?>" role="progressbar"
                         class="progress-bar progress-bar-warning">
                        <span class="sr-only"><?php echo $results['imgs_statis'][2] . '%' ?> Complete (success)</span>
                    </div>
                </div>
                <p><?php _e('Wrong resized images', 'wp-meta-seo') ?>
                    : <?php echo $results['imgs_statis'][0] . '/' . $results['imgs_statis'][1] ?></p>
            </div>
            <div class="col-xs-5 col-lg-4 text-right">
                <label>
                    <input type="text" value="<?php echo $results['imgs_statis'][2] ?>" class="dial-warning">
                </label>
            </div>
        </div>
    </div>
</div>