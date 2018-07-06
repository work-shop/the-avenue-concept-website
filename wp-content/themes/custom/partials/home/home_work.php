<section class="block section-padded spy-target bg-dark-green spy-first" id="home-work">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-lg-12 col-xl-8">
				<h3 class="section-header white">
					Founded in Providence, RI in 2012, The Avenue Concept was the city's first private public art program. Since then it has installed or exhibited more than 150 works of public art, and invested $1.1million in both artwork and infrastucture.
				</h3>
			</div>
		</div>
		<div class="row section-content-row mb4">
			<div class="col-8 offset-2">
				<?php 
				$images = get_field('our_work_gallery');
				$size = 'home_gallery'; 
				if( $images ): ?>
					<div class="slick slick-home">
						<?php foreach( $images as $image ): ?>
							<div>
								<img src="<?php echo $image['sizes']['home_gallery']; ?>" />
								<p class="caption white"><?php echo $image['caption']; ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php if( get_field('map_file') ): ?>
			<div class="row">
				<div class="col-6 offset-3">
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