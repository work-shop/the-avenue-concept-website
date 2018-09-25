<?php if( have_rows('other_donation_options','189') ): ?>
	<section class="block padded bg-white" id="ways-to-donate">
		<div class="container-fluid container-fluid-stretch">
			<div class="row section-header-row">
				<div class="col-xl-8">
					<h3 class="section-header">
						Other Ways To Donate
					</h3>
				</div>
			</div>
			<?php $count = 1; ?>
			<div class="row section-content-row mb2">
				<?php while ( have_rows('other_donation_options','189') ) : the_row(); ?>
					<div class="col-6 col-md-4">
						<div class="donation-option">
							<h4 class="font-main bold">
								<?php the_sub_field('donation_option_label'); ?>
							</h4>
							<?php if( get_sub_field('donation_option_description') ): ?>
								<p class="font-main mb2">
									<?php the_sub_field('donation_option_description'); ?>
								</p>
							<?php endif; ?>
							<?php $link = get_sub_field('donor_link');
							if( $link ): ?>
								<a class="button" href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>"><?php echo $link['title']; ?></a>
							<?php endif; ?>
						</div>
					</div>
					<?php $count++; ?>
				<?php endwhile; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
