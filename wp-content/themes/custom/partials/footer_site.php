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
								<a href="/media">
									Media
								</a>
							</li>
							<li>
								<a href="/events">
									Upcoming Events
								</a>
							</li>
							<li>
								<a href="/get-involved">
									Get Involved
								</a>
							</li>
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
					<div class="col-xl col-lg col-md-12 col-sm-12 col-xs-12 footer-nav-col">
						<div class="mb2 footer-donate-button">
							<a href="/donate" class="font-secondary uppercase footer-nav-heading d-flex align-items-center justify-content-center">
								Donate
							</a>
						</div>
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