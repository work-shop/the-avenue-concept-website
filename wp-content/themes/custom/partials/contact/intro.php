<?php if( is_page(181) ): ?>
	<div id="contact-intro">	
		<div class="container-fluid container-fluid-stretch">
			<div class="row">
				<div class="col-md-5 offset-md-7">
					<h1 class="page-hero-title d-flex justify-content-end">
						<?php the_title(); ?>
					</h1>
				</div>
			</div>
		</div>
	</div>
	<div class="page-nav before">
		<div class="container-fluid container-fluid-stretch">
			<div class="row">
				<div class="col col-lg-10 offset-lg-2">
					<ul class="page-nav-list">
						<li>
							<a href="#contact" class="jump">
								Contact Info
							</a>
						</li>
						<li>
							<a href="#contact-form" class="jump">
								Contact Form
							</a>
						</li>
						<li>
							<a href="#contact-newsletter" class="jump">
								Newsletter
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<section class="block padded bg-white" id="contact">
	<div class="container-fluid container-fluid-stretch">
		<div class="row">
			<div class="col-12 col-sm-12 col-md-6 col-lg-4 bg-brand contact-address p2">
				<address class="mb2 white">
					<?php the_field('address','181'); ?>
				</address>
				<h4 class="white font-main mb0">
					<a href="mailto:<?php the_field('contact_email_address','181'); ?>
					" target="_blank" class="white">
					<?php the_field('contact_email_address','181'); ?>
				</a>
			</h4>
			<h4 class="white font-main">
				<?php the_field('contact_phone_number','181'); ?>
			</h4>
			<?php if( is_page(181) == false ): ?>
				<h4 class="mt2">
					<a href="/contact" class="button">
						Contact Us
					</a>
				</h4>
			<?php endif; ?>
		</div>
		<div class="col contact-map">
			<?php $google_maps_api_key = 'AIzaSyBwlv2Z5B46ikeknPsD8b7C5O0GD2Pxx7E'; ?>
			<iframe class="" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?q=The%20Avenue%20Concept&key=<?php echo $google_maps_api_key; ?>" allowfullscreen></iframe>
		</div>
	</div>
</div>
</section>
