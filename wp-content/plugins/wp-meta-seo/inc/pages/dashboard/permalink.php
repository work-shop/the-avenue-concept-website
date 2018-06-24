<div class="panel panel-updates">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-7 col-lg-8">
                <h4 class="panel-title text-success"><?php _e('Permalinks settings', 'wp-meta-seo') ?></h4>
                <h3><?php echo $permalink . '%' ?></h3>
                <div class="progress">
                    <div style="width: <?php echo $permalink . '%' ?>" aria-valuemax="100" aria-valuemin="0"
                         aria-valuenow="<?php echo $permalink ?>" role="progressbar"
                         class="progress-bar progress-bar-success">
                        <span class="sr-only"><?php echo $permalink . '%' ?> Complete (success)</span>
                    </div>
                </div>
                <p><?php _e('Optimized at', 'wp-meta-seo') ?>: <?php echo $permalink . '%' ?></p>
            </div>
            <div class="col-xs-5 col-lg-4 text-right">
                <label>
                    <input type="text" value="<?php echo $permalink ?>" class="dial-success">
                </label>
            </div>
        </div>
    </div>
</div>
