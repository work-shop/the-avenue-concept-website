<section class="block spy-target spy-first" id="home-intro">
	<div class="home-intro-top">
		<video muted autoplay playsinline loop class="" id="home-intro-video">
			<source src="<?php the_field('intro_video'); ?>" type="video/mp4">
			</video>
		</div>
		<div class="home-intro-bottom">
			<div class="home-intro-curve">
				<?php include get_template_directory() . '/images/home-intro-curve-02.svg'; ?>
			</div>
			<div class="container-fluid container-fluid-stretch tagline-container">
				<h1 id="tagline">
					<?php the_field('tagline','6'); ?>
				</h1>
			</div>
		</div>
	</section>