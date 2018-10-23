
<?php
$show_announcement = get_field('show_announcement','6');
$announcement_message = get_field('announcement_text','6');
$announcement_link = get_field('announcement_link','6');
?>
<?php if( $show_announcement ): ?>
	<div id="home-announcement" class="announcement-bar">
		<div class="container-fluid container-fluid-stretch">
			<div class="row">
				<div class="col-11 col-sm-12">
					<?php if( $announcement_link ): ?>
						<a href="<?php echo $announcement_link; ?>">
						<?php endif; ?>
						<span class="announcement-message">
							<?php echo $announcement_message; ?>
						</span>
						<?php if( $announcement_link ): ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<a href="#" id="announcement-close"><span class="icon" data-icon="’"></span></a>
	</div>
<?php endif; ?>
