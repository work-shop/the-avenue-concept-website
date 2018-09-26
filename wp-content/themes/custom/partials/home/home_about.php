<section class="block section-padded spy-target spy-first" id="home-about">
	<div class="block-background mask-dark" style="background-image: url('<?php $image = get_field('about_background_image'); echo $image['sizes']['page_hero']; ?>');">
	</div>
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h2 class="section-header white">
					<?php the_field('about_heading'); ?>
				</h2>
			</div>
		</div>
		<?php if( have_rows('mission','159') ): ?>
			<?php $count = 1; ?>
			<div class="row section-content-row mb4">
				<?php  while ( have_rows('mission','159') ) : the_row(); ?>
					<div class="col-6 mb3 mission-element">
						<div class="row">
							<div class="col-sm-1">
								<h2 class="white">
									<?php echo $count; ?>
								</h2>
							</div>
							<div class="col-sm-11 col-md-9">
								<h3 class="white">
									<?php the_sub_field('mission_element'); ?>
								</h3>
							</div>
						</div>
					</div>
					<?php $count++; ?>
				<?php endwhile; ?>
			</div>
		<?php endif; ?>
	</div>
</section>