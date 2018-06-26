<section class="block section-padded spy-target bg-tan spy-first vh100" id="home-events">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-lg-12 col-xl-8">
				<h2 class="section-header brand">
					Upcoming Events
				</h2>
			</div>
		</div>
		<div class="row section-content-row">
			<div class="col">
				<?php if( have_rows('field') ): ?>
					<ul>
						<?php  while ( have_rows('field') ) : the_row(); ?>
							<li>
								<?php the_sub_field('field'); ?>
							</li>
						<?php endwhile; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>