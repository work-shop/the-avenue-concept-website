<div class="panel panel-updates">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-7 col-lg-8">
                <h4 class="panel-title text-danger"><?php _e('New or updated content', 'wp-meta-seo') ?></h4>
                <h3><?php echo $results[0] . '%' ?></h3>
                <div class="progress">
                    <div style="width: <?php echo $results[0] . '%' ?>" aria-valuemax="100" aria-valuemin="0"
                         aria-valuenow="<?php echo $results[0] ?>" role="progressbar"
                         class="progress-bar progress-bar-danger">
                        <span class="sr-only"><?php echo $results[0] . '%' ?> Complete (success)</span>
                    </div>
                </div>
                <p><?php _e('Latest month new or updated content', 'wp-meta-seo') ?>: <?php echo $results[1][0] ?></p>
            </div>
            <div class="col-xs-5 col-lg-4 text-right">
                <label>
                    <input type="text" value="<?php echo $results[0] ?>" class="dial-danger">
                </label>
            </div>
        </div>
    </div>
</div>