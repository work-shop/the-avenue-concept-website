<section class="block padded bg-tan" id="about-values">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h2 class="section-header values-heading">
					<?php the_field('values_heading'); ?>
				</h2>
			</div>
		</div>
		<?php if( have_rows('values') ): ?>
			<?php $count = 1; ?>
			<ul class="values-list row">
				<?php  while ( have_rows('values') ) : the_row(); ?>
					<li class="col-sm-6 col-md-4 value-col">
						<h3 class="value"><?php the_sub_field('value'); ?></h3>
					</li>
					<?php $count++; ?>
				<?php endwhile; ?>
			<?php endif; ?>
		</ul>
	</div>
</section>