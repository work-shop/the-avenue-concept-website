<section class="block padded bg-white" id="about-mission">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h2 class="section-header ">
					<?php the_field('mission_heading'); ?>
				</h2>
			</div>
		</div>
		<?php if( have_rows('mission') ): ?>
			<?php $count = 1; ?>
			<div class="row section-content-row">
				<?php  while ( have_rows('mission') ) : the_row(); ?>
					<div class="col-6 mb3 mission-element">
						<div class="row">
							<div class="col-sm-1">
								<h2 class="">
									<?php echo $count; ?>
								</h2>
							</div>
							<div class="col-sm-11 col-md-9">
								<h3 class=" font-main">
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