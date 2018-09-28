<?php  while ( have_rows('events','185') ) : the_row(); ?>
	<div class="col-6 mb5">
		<div class="event card card-event">
			<?php 
			$link = get_sub_field('event_link'); 
			$event_image = get_sub_field('event_image'); ?>
			<?php if( $event_image ): ?>
				<?php if( $link ): ?>
					<a href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>">
					<?php endif; ?>
					<div class="card-image">
						<img src="<?php echo $event_image['sizes']['page_hero']; ?>" />
					</div>
					<?php if( $link ): ?>
					</a>
				<?php endif; ?>
			<?php endif; ?>
			<div class="card-text">
				<?php if( $link ): ?>
					<a href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>">
					<?php endif; ?>
					<h3 class="event-title font-main mt1">
						<?php the_sub_field('event_title'); ?>
					</h3>
					<h4 class="font-main m0">
						<?php the_sub_field('event_date'); ?>, <?php the_sub_field('event_start_time'); ?> <?php if( get_sub_field('event_end_time') ): ?> - <?php the_sub_field('event_end_time'); ?> <?php endif; ?>
					</h4>
					<h4 class="font-main mt0 mb1">
						<?php the_sub_field('event_location'); ?>
					</h4>
					<?php if( $link ): ?>
						<h4 class="mb0 font-main card-event-link"><?php echo $link['title']; ?></h4>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endwhile; ?>