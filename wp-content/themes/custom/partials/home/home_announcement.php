<?php if( get_field('show_announcement') ): ?>
	<section class="block section-padded spy-target bg-white spy-first" id="home-announcement">
		<div class="container-fluid container-fluid-stretch">
			<div class="block-background announcement-background" style="background-image: url('<?php bloginfo( 'template_directory' ); ?>/images/blob-announcement.svg');"></div>
			<div class="row announcement-heading-row pt6 mb3">
				<div class="col-md-9 offset-md-3">
					<h2 class="announcement-heading">
						<?php the_field('announcement_heading'); ?>
					</h2>
				</div>
			</div>
			<div class="row announcement-text-row pb6">
				<div class="col-md-6 offset-md-5 col-lg-4 offset-lg-4">
					<?php 
					$subheading = get_field('announcement_subheading');
					if( $subheading ): ?>
						<h4 class="announcement-subheading">
							<?php echo $subheading ?>
						</h4>
					<?php endif; ?>
					<?php 
					$link = get_field('announcement_link');
					if( $link ): ?>
						<div class="announcement-link">
							<a class="button" href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>"><?php echo $link['title']; ?></a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php else: ?>
		<section class="block bg-white vh10" id="home-separator">
		</section>
	<?php endif; ?>