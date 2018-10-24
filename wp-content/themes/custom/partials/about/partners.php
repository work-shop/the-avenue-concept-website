<?php if( have_rows('sponsors_and_partners') ): ?>
	<section class="block padded bg-white" id="about-partners">
		<div class="container-fluid container-fluid-stretch">
			<div class="row section-header-row">
				<div class="col-xl-8">
					<h3 class="mb2">
						<?php the_field('sponsors_and_partners_heading'); ?>
					</h3>
				</div>
			</div>
			<?php $count = 1; ?>
			<div class="row section-content-row partners-list">
				<?php while ( have_rows('sponsors_and_partners') ) : the_row(); ?>
					<div class="col-6 col-xl-3 col-sm-4 mb3 partner">
						<?php if( get_sub_field('organization_website') ): ?>
							<a href="<?php the_sub_field('organization_website'); ?>" class="partner-link">
							<?php endif; ?>
							<?php $image = get_sub_field('organization_logo'); ?>
							<div class="partner-image">
								<div class="partner-logo" style="background-image: url('<?php echo $image['url']; ?>);">
								</div>
							</div>
							<div class="partner-text">
								<?php if( get_sub_field('sponsor_title') ||  get_sub_field('organization_name')): ?>
								<h4 class="partner-title bold font-main">
									<?php if( get_sub_field('organization_name') ): ?>
										<?php the_sub_field('organization_name'); ?><?php endif; ?><?php if( get_sub_field('sponsor_title') ): ?>,<br> <?php the_sub_field('sponsor_title'); ?>
										<?php else: ?>
											<?php the_sub_field('sponsor_title'); ?>
									<?php endif; ?>
								</h4>
							<?php endif; ?>
						</div>
						<?php if( get_sub_field('organization_website') ): ?>
						</a>
					<?php endif; ?>
				</div>
				<?php $count++; ?>
			<?php endwhile; ?>
		</div>
	</div>
</section>
<?php endif; ?>
