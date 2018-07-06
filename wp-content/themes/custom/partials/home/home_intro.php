<section class="block spy-target bg-white spy-first" id="home-intro">
	<div class="container-fluid container-fluid-stretch">
		<div class="home-intro-header">
			<div class="row">
				<div class="col">
					<div id="logo-home" class="logo static shown">
						<a href="/" title="Home">
							<?php get_template_part('partials/logo'); ?>
						</a>
					</div>
				</div>
				<div class="col responsive-donate-col justify-content-end d-flex">
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="paypal-button-form">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="T3NSG4UVFRPMQ">
						<input type="image" src="http://d3w0jg1q5ypcyu.cloudfront.net/2018/07/donate-button-brand.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" class="paypal-button-image">
						<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" class="paypal-button-hidden-image">
					</form>
				</div>
				<div class="col-md-8">
					<div class="row">
						<div class="col-sm-9">
							<h2 class="section-header brand mt0" id="tagline">
								<?php the_field('tagline'); ?>
							</h2>
						</div>
						<div class="col-3 donate-col justify-content-end d-flex">
							<ul id="home-header-nav">
								<li>
									<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="paypal-button-form">
										<input type="hidden" name="cmd" value="_s-xclick">
										<input type="hidden" name="hosted_button_id" value="T3NSG4UVFRPMQ">
										<input type="image" src="http://d3w0jg1q5ypcyu.cloudfront.net/2018/07/donate-button-brand.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" class="paypal-button-image">
										<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" class="paypal-button-hidden-image">
									</form>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="block" id="home-video">
	<div class="block-background">
		<video muted autoplay playsinline loop class="" id="landing-video">
			<source src="<?php the_field('intro_video'); ?>" type="video/mp4">				
			</video>
		</div>
		<div class="video-blob" id="home-video-blob">
			<img src="<?php bloginfo( 'template_directory' ); ?>/images/blob-clip-video.svg" title="" alt="">	
		</div>
	</section>
