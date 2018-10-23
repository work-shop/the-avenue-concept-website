
<?php
$show_sitewide_alert = get_field('show_sitewide_alert', 'option');
$sitewide_alert_message = get_field('sitewide_alert_message', 'option');
$sitewide_alert_link = get_field('sitewide_alert_link', 'option');
?>
<?php if( $show_sitewide_alert ): ?>
	<div id="sitewide-alert" class="">
		<div class="container-fluid container-fluid-stretch">
			<div class="row">
				<div class="col-11 col-sm-12">
					<?php if( $sitewide_alert_link ): ?>
						<a href="<?php echo $sitewide_alert_link['url']; ?>">
						<?php endif; ?>
						<span class="sitewide-alert-message">
							<?php echo $sitewide_alert_message; ?>
						</span>
						<?php if( $sitewide_alert_link ): ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<a href="#" id="sitewide-alert-close"><span class="icon" data-icon="’"></span></a>
	</div>
<?php endif; ?>
