<section class="block section-padded spy-target bg-lime spy-first" id="home-contact">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-lg-12 col-xl-8">
				<h2 class="section-header">
					<?php the_field('contact_heading'); ?>
				</h2>
			</div>
		</div>
		<div class="row section-content-row">
			<?php if( have_rows('contact_links') ): ?>
				<?php  while ( have_rows('contact_links') ) : the_row(); ?>
					<div class="col contact-link-col">
						<?php 
						$link = get_sub_field('link');
						if( $link ): ?>
							<div class="contact-link blob-background blob-dark">
								<a class="" href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>">
									<h3 class="centered pt2 pb2 white"><?php echo $link['title']; ?></h3>
								</a>
							</div>
						<?php endif; ?>
					</div>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>
	</div>
</section>