<?php if( have_rows('impact') ): ?>
	<section class="block padded bg-brand" id="about-impact">
		<div class="container-fluid container-fluid-stretch">
			<div class="row">
				<div class="col-xl-8">
					<h3 class="mb3 values-heading">
						<?php the_field('impact_heading'); ?>
					</h3>
				</div>
			</div>
			<?php $count = 1; ?>
			<div class="values-list row justify-content-center">
				<?php  while ( have_rows('impact') ) : the_row(); ?>
					<div class="col-6 col-md-4 impact-col">
						<h1 class="impact-title"><?php the_sub_field('title'); ?></h1>
						<h3 class="impact-label"><?php the_sub_field('label'); ?></h3>
					</div>
					<?php $count++; ?>
				<?php endwhile; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
