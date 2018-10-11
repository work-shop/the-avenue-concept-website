<?php if( have_rows('donors_list','189') ): ?>
	<section class="block padded bg-tan" id="donors">
		<div class="container-fluid container-fluid-stretch">
			<div class="row section-header-row">
				<div class="col-xl-8">
					<h3 class="section-header">
						<?php the_field('donors_heading','189'); ?>
					</h3>
				</div>
			</div>
			<?php $count = 1; ?>
				<div class="row section-content-row donors-list mb2">
					<?php while ( have_rows('donors_list','189') ) : the_row(); ?>
						<div class="col-6 col-md-4 col-lg-3 donor">
							<div class="partner-logo">
								<?php if( get_sub_field('donor_link') ): ?>
									<a href="<?php the_sub_field('donor_link'); ?>">
									<?php endif; ?>
									<h4 class="donor-name font-main">
										<?php the_sub_field('donor_name'); ?>
									</h4>
									<?php if( get_sub_field('donor_link') ): ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
						<?php $count++; ?>
					<?php endwhile; ?>
				</div>
				<?php if( is_page('189') == false ): ?>
					<div class="row">
						<div class="col">
							<a href="/donate" class="button">
								Support TAC Today
							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>
	