<footer class="block pt5 bg-dark" id="footer">
	<div class="container-fluid">
		<div class="row mb4">
			<div class="col-md-6 col-lg-4 footer-col">
				<div id="logo-footer" class="logo mb4">
					<a href="/" title="The Avenue Concept">
						<img src="<?php bloginfo( 'template_directory' ); ?>/images/logo-white.svg" title="The Avenue Concept Logo" alt="The Avenue Concept Logo">
					</a>
				</div>
				<address class="footer-address mb1">
					The Avenue Concept<br>
					304 Lockwood Street<br>
					Providence, RI 02907
				</address>
				<p class="footer-contact-us">
					Contact Us:<br>
					<a href="mailto:hello@theavenueconcept.com" class="white" target="_blank">hello@theavenueconcept.com</a><br>
					(401) 490-0929
				</p>
				<div class="footer-social">
					<?php $social_media_links = get_field('social_media_links','183'); ?>
					<a href="<?php echo $social_media_links['facebook_link']; ?>" target="_blank">
						<img src="<?php bloginfo( 'template_directory' );?>/images/facebook.png" class="social-icon">
					</a> 											
					<a href="<?php echo $social_media_links['instagram_link']; ?>" target="_blank">
						<img src="<?php bloginfo( 'template_directory' );?>/images/instagram.png" class="social-icon">
					</a>
					<a href="<?php echo $social_media_links['vimeo_link']; ?>" target="_blank">
						<img src="<?php bloginfo( 'template_directory' );?>/images/vimeo.png" class="social-icon">
					</a> 
					<a href="<?php echo $social_media_links['twitter_link']; ?>" target="_blank">
						<img src="<?php bloginfo( 'template_directory' );?>/images/twitter.png" class="social-icon">
					</a>
				</div>
			</div>
			<div class="col-sm-12 col-xs-12 col-lg col-md-12 footer-col">
				<div class="row">
					<div class="col-xl col-lg col-md-12 col-sm-12 col-xs-12 footer-nav-col">
						<h4 class="mb2">
							<a href="/programs" class="font-secondary uppercase footer-nav-heading">
								Programs
							</a>
						</h4>
						<ul>
							<?php 
							$args = array(
								'post_parent' => 161,
								'post_type' => 'page',
								'orderby' => 'menu_order'
							);
							$child_query = new WP_Query( $args );
							while ( $child_query->have_posts() ) : $child_query->the_post(); ?>
								<li>
									<a href="<?php the_permalink(); ?>" class="footer-nav-link">
										<?php the_title(); ?>
									</a>
								</li>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
						</ul>
					</div>	
					<div class="col-xl col-lg col-md-12 col-sm-12 col-xs-12 footer-nav-col">
						<h4 class="mb2">
							<a href="/artworks" class="font-secondary uppercase footer-nav-heading">
								Artworks
							</a>
						</h4>
						<ul>
							<li>
								<a href="/artworks">
									Map
								</a>
							</li>
							<li>
								<a href="/artworks">
									On View Now
								</a>
							</li>
							<li>
								<a href="/artworks?on-view=true&program=3-D&view=map">
									3-D Artworks
								</a>
							</li>
							<li>
								<a href="/artworks?on-view=true&program=2-D&view=map">
									2-D Artworks
								</a>
							</li>
						</ul>
					</div>	
					<div class="col-xl col-lg col-md-12 col-sm-12 col-xs-12 footer-nav-col">
						<h4 class="mb2">
							<a href="/about" class="font-secondary uppercase footer-nav-heading">
								About
							</a>
						</h4>
						<ul>
							<li>
								<a href="/about">
									About TAC
								</a>
							</li>
							<li>
								<a href="/press">
									Press
								</a>
							</li>
							<li>
								<a href="/events">
									Upcoming Events
								</a>
							</li>
<!-- 							<li>
								<a href="/get-involved">
									Get Involved
								</a>
							</li> -->
							<li>
								<a href="/blog">
									Blog
								</a>
							</li>
							<li>
								<a href="/contact">
									Contact
								</a>
							</li>
						</ul>
					</div>
					<div class="col-xl col-lg col-md-12 col-sm-12 col-xs-12 footer-nav-col footer-donate-col">
						<h4 class="mb2">
							<a href="/donate" class="font-secondary uppercase footer-nav-heading">
								Donate
							</a>
						</h4>
						<ul>
							<li>
								<a href="/donate#donation">
									Donate Now
								</a>
							</li>
							<li>
								<a href="/donate#ways-to-donate">
									Ways To Donate
								</a>
							</li>
						</ul>
<!-- 						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="paypal-button-form">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="T3NSG4UVFRPMQ">
							<input type="image" src="https://d3w0jg1q5ypcyu.cloudfront.net/2018/07/donate-button-brand.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" class="paypal-button-image">
							<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" class="paypal-button-hidden-image">
						</form> -->
					</div>				
				</div>
			</div>
		</div>
		<div class="row">
			<div class="site-credit pb1">
				<a href="http://workshop.co" target="_blank">
					<h4 class="white font-primary mb0">Site by Work-Shop Design Studio</h4>
				</a>
			</div>
		</div>
	</div>
</footer>