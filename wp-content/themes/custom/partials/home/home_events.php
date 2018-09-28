<?php if( have_rows('events','185') ): ?>
	<section class="block padded spy-target bg-tan spy-first" id="home-events">
		<div class="container-fluid container-fluid-stretch">
			<div class="row">
				<div class="col-lg-12 col-xl-8">
					<h3 class="mb2">
						Upcoming Events
					</h3>
				</div>
			</div>
			<div class="row">
				<?php get_template_part('/partials/events/events_loop'); ?>
			</div>
		</div>
	</section>
<?php endif; ?>
