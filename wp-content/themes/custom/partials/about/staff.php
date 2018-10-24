<section class="block padded bg-white" id="about-staff">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h3 class="section-header">
					Staff
				</h3>
			</div>
		</div>
		<?php if( have_rows('staff') ): ?>
			<?php $count = 1; ?>
			<div class="row section-content-row staff-list">
				<?php while ( have_rows('staff') ) : the_row(); ?>
					<div class="col-6 col-sm-6 col-md-6 col-lg-3 mb3 staff-person person">
						<div class="person-image">
							<?php $image = get_sub_field('person_image');
							$image = $image['sizes']['person']; ?>
							<img src="<?php echo $image; ?>" >
						</div>
						<div class="person-text">
							<h4 class="bold font-main person-name mb0">
								<?php the_sub_field('person_name'); ?>
							</h4>
							<h4 class="font-main person-title">
								<?php the_sub_field('person_title'); ?>
							</h4>
							<?php if( get_sub_field('person_email') ): ?>
								<h4 class="font-main person-email">
									<a href="mailto:<?php the_sub_field('person_email'); ?>">
										<?php $first_name = explode(' ', get_sub_field('person_name'), 2); ?>
										Email <?php echo $first_name[0]; ?>
									</a>
								</h4>
							<?php endif; ?>
						</div>
					</div>
					<?php $count++; ?>
				<?php endwhile; ?>
			</div>
		<?php endif; ?>
	</div>
</section>