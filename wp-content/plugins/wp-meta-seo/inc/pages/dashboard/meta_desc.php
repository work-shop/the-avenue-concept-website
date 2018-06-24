<div class="panel panel-success-full panel-updates">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-7 col-lg-8">
                <h4 class="panel-title text-success"><?php _e('Meta Description', 'wp-meta-seo') ?></h4>
                <h3><?php echo $results[0] . '%' ?></h3>
                <div class="progress">
                    <div style="width: <?php echo $results[0] . '%' ?>" aria-valuemax="100" aria-valuemin="0"
                         aria-valuenow="<?php echo $results[0] ?>" role="progressbar"
                         class="progress-bar progress-bar-info">
                        <span class="sr-only"><?php echo $results[0] . '%' ?> Complete (success)</span>
                    </div>
                </div>
                <p><?php _e('Meta description filled', 'wp-meta-seo') ?>
                    : <?php echo $results[1][0] . '/' . $results[1][1] ?></p>
            </div>
            <div class="col-xs-5 col-lg-4 text-right">
                <label>
                    <input type="text" value="<?php echo $results[0] ?>" class="dial-info">
                </label>
            </div>
        </div>
    </div>
</div>
