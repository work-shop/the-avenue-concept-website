<section class="block section-padded spy-target bg-dark spy-first" id="home-work">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-lg-12 col-xl-8">
				<h3 class="section-header white">
                    <?php the_field('our_work_heading'); ?>
				</h3>
			</div>
		</div>
		<div class="row section-content-row mb4">
			<div class="col-sm-10 offset-sm-1">
				<?php
				$images = get_field('our_work_gallery');
				$size = 'home_gallery';
				if( $images ): ?>
					<div class="slick slick-home">
						<?php foreach( $images as $image ): ?>
							<div>
								<img src="<?php echo $image['sizes']['home_gallery_cropped']; ?>" />
								<p class="caption white"><?php echo $image['caption']; ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php if( get_field('map_file') ): ?>
			<div class="row">
				<div class="col-8 offset-2 col-sm-6 offset-sm-3">
					<div class="row">
						<div class="col-md-10 offset-md-1">
							<div class="home-work-map blob-background blob-c blob-tan">
								<a href="<?php the_field('map_file');?>" target="_blank">
									<h3 class="dark pt5 pb5 centered"><?php the_field('map_link_text');?></h3>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
