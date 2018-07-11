<?php if( have_rows('upcoming_events') ): ?>
	<section class="block section-padded spy-target bg-white spy-first" id="home-events">
		<div class="container-fluid container-fluid-stretch">
			<div class="row section-header-row">
				<div class="col-lg-12 col-xl-8">
					<h2 class="section-header">
						Upcoming Events
					</h2>
				</div>
			</div>
			<div class="row section-content-row">
				<?php  while ( have_rows('upcoming_events') ) : the_row(); ?>
					<div class="col-6 mb3">
						<div class="home-event card-event bg-tan">
							<?php 
							$link = get_sub_field('event_link');
							if( $link ): ?>
								<a href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>">
								<?php endif; ?>
								<h3 class="home-event-title mt1">
									<?php the_sub_field('event_title'); ?>
								</h3>
								<h4 class="font-primary">
									<?php the_sub_field('event_date'); ?>, <?php the_sub_field('event_start_time'); ?> <?php if( get_sub_field('event_end_time') ): ?> - <?php the_sub_field('event_end_time'); ?> <?php endif; ?>
								</h4>
								<h4 class="font-primary mb2">
									<?php the_sub_field('event_location'); ?>
								</h4>
								<?php if( $link ): ?>
									<h4 class="mb0"><?php echo $link['title']; ?></h4>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
