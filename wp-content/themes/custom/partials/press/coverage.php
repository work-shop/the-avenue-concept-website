<?php if( have_rows('coverage_years') ): ?>
	<section class="block pt3 pb2 bg-white" id="press-coverage">
		<div class="container-fluid container-fluid-stretch">
			<div class="row section-header-row">
				<div class="col-xl-8">
					<h3 class="mb2">
						<?php the_field('coverage_heading'); ?>
					</h3>
				</div>
			</div>
			<div class="row">
				<?php  while ( have_rows('coverage_years') ) : the_row(); ?>
					<div class="col-md-4 col-lg-3 year mb3">
					<?php if( get_sub_field('year_title') ): ?>
						<h4 class="year-title">
							<?php the_sub_field('year_title'); ?>
						</h4>
					<?php endif; ?>
					<?php if( get_sub_field('year_articles') ): ?>
						<div class="year-content wysiwyg">
							<?php the_sub_field('year_articles'); ?>
						</div>
					<?php endif; ?>
					</div>					
				<?php endwhile; ?>
			</div>
		</section>
	<?php endif; ?>
