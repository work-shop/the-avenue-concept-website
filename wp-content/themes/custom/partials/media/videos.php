<section class="block padded bg-light" id="videos">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h3 class="section-header">
					<?php the_field('vimeo_embeds_heading'); ?>
				</h3>
			</div>
		</div>
		<?php $count = 1; ?>
		<?php if( have_rows('vimeo_embeds') ): ?>
			<div class="row section-content-row donors-list mb2">
				<?php while ( have_rows('vimeo_embeds') ) : the_row(); ?>
					<div class="col-12 col-md-6 mb4">
						<div class="aspect-ratio-box">
							<div class="aspect-ratio-box-inside">
								<iframe src="https://player.vimeo.com/video/<?php the_sub_field('vimeo_id'); ?>" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
							</div>
						</div>
						<?php if( get_sub_field('video_title') ): ?>
							<h3 class="video-title font-main mt1 mb0">
								<?php the_sub_field('video_title'); ?>
							</h3>
						<?php endif; ?>
					</div>
					<?php $count++; ?>
				<?php endwhile; ?>
			</div>
		<?php endif; ?>
	</section>
