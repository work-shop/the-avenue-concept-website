<section class="block pt6 pb6 spy-target" id="home-map">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-content-row">
			<div class="col-md-4">
				<h3 class="section-header">
					<?php the_field('map_heading'); ?>
				</h3>
				<p class="mb2">
					<?php the_field('map_subheading'); ?>
				</p>
				<a href="/artworks" class="button">
					<?php the_field('map_link_text'); ?>
				</a>
			</div>
			<div id="home-map-container" class="col-md-8">
			</div>
		</div>
	</div>
</section>
