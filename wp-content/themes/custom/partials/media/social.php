<section class="block padded bg-white" id="social-media">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h3 class="section-header">
					<?php the_field('social_media_links_heading'); ?>
				</h3>
			</div>
		</div>
		<div class="row section-content-row mb2">
			<ul class="social-media-links">
				<?php $social_media_links = get_field('social_media_links','183'); ?>
				<li>
					<a href="<?php echo $social_media_links['facebook_link']; ?>" target="_blank">
						<img src="<?php bloginfo( 'template_directory' );?>/images/facebook.png" class="social-icon">
					</a>
				</li> 											
				<li>
					<a href="<?php echo $social_media_links['instagram_link']; ?>" target="_blank">
						<img src="<?php bloginfo( 'template_directory' );?>/images/instagram.png" class="social-icon">
					</a>
				</li>
				<li>
					<a href="<?php echo $social_media_links['vimeo_link']; ?>" target="_blank">
						<img src="<?php bloginfo( 'template_directory' );?>/images/vimeo.png" class="social-icon">
					</a>
				</li> 
				<li>
					<a href="<?php echo $social_media_links['twitter_link']; ?>" target="_blank">
						<img src="<?php bloginfo( 'template_directory' );?>/images/twitter.png" class="social-icon">
					</a>
				</li>
			</ul>
		</div>
	</div>
</section>
