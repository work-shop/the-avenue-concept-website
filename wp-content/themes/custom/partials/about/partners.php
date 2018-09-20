<?php if( have_rows('sponsors_and_partners') ): ?>
	<section class="block padded bg-white" id="about-partners">
		<div class="container-fluid container-fluid-stretch">
			<div class="row section-header-row">
				<div class="col-xl-8">
					<h3 class="section-header">
						<?php the_field('sponsors_and_partners_heading'); ?>
					</h3>
				</div>
			</div>
			<?php $count = 1; ?>
			<div class="row section-content-row partners-list">
				<?php while ( have_rows('sponsors_and_partners') ) : the_row(); ?>
					<div class="col-4 col-lg-3 mb3 partner">
						<div class="partner-logo">
							<?php if( get_sub_field('organization_website') ): ?>
								<a href="<?php the_sub_field('organization_website'); ?>">
								<?php endif; ?>
								<?php $image = get_sub_field('organization_logo');
								$image = $image['sizes']['person']; ?>
								<img src="<?php echo $image; ?>" >
								<?php if( get_sub_field('organization_website') ): ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
					<?php $count++; ?>
				<?php endwhile; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
