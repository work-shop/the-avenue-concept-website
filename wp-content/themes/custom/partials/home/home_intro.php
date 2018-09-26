<section class="block spy-target spy-first" id="home-intro">
	<video muted autoplay playsinline loop class="" id="home-intro-video">
		<source src="<?php the_field('intro_video'); ?>" type="video/mp4">				
	</video>
	<div class="tagline-container">
		<h1 id="tagline">
			<?php the_field('tagline','6'); ?>
		</h1>
	</div>
	<div class="home-intro-curve">
		<?php include get_template_directory() . '/images/home-intro-curve.svg'; ?>
	</div>
</section>