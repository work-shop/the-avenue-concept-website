<div id="nav-blanket"></div>
<div id="nav-curve-outer" class="nav-curve">
	<?php include get_template_directory() . '/images/nav-curve-1.svg'; ?>
</div>
<nav id="nav" class="fixed before">

	<div id="nav-background">
		<?php //include get_template_directory() . '/images/nav-background.svg'; ?>
	</div>
	<div id="logo" class="logo">
		<a href="/" title="Home">
			<?php get_template_part('partials/logo'); ?>
		</a>
	</div>
	<div id="nav-menus">
		<ul class="nav-menus-list">
			<li class="has-sub-menu closed nav-menu-primary-item">
				<a href="/programs" class="dropdown-link closed mobile-closed <?php if( Helpers::is_tree(161) ): echo ' nav-current '; endif; ?>" id="nav-link-about" data-dropdown-target="programs">
					Programs
					<span class="icon" data-icon="ﬁ"></span>
				</a>
				<ul class="sub-menu" id="sub-menu-programs">
					<li>
						<a href="/programs" class=""> 
							All Programs
						</a>
					</li>
					<?php 
					$args = array(
						'post_parent' => 161,
						'post_type' => 'page',
						'orderby' => 'menu_order'
					);
					$child_query = new WP_Query( $args );
					while ( $child_query->have_posts() ) : $child_query->the_post(); ?>
						<li>								
							<a href="<?php the_permalink(); ?>" class="">
								<?php the_title(); ?>
							</a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</ul>
			</li>
			<li class="has-sub-menu closed nav-menu-primary-item">
				<a href="/artworks?view=thumbnails" class="dropdown-link closed mobile-closed <?php if( Helpers::is_tree(187) ): echo ' nav-current '; endif; ?>" id="nav-link-artworks" data-dropdown-target="artworks">
					Artworks
					<span class="icon" data-icon="ﬁ"></span>
				</a>
				<ul class="sub-menu" id="sub-menu-artworks">
					<li>
						<a href="/artworks?view=map" class="">
							Map
						</a>
					</li>
					<li>
						<a href="/artworks?view=thumbnails" class="">
							On View Now
						</a>
					</li>
					<li>
						<a href="/artworks/?program=3-D" class="">
							3-D Artworks
						</a>
					</li>
					<li>
						<a href="/artworks/?program=2-D" class="">
							2-D Artworks
						</a>
					</li>
				</ul>
			</li>
			<li class="has-sub-menu closed nav-menu-primary-item">
				<a href="/about" class="dropdown-link closed mobile-closed <?php if( is_page(159) ): echo ' nav-current '; else: ' not-current'; endif; ?>" id="nav-link-about" data-dropdown-target="about">
					About
					<span class="icon" data-icon="ﬁ"></span>
				</a>
				<ul class="sub-menu" id="sub-menu-about">
					<li>
						<a href="/about" class="">
							About TAC
						</a>
					</li>
					<li>
						<a href="/press" class="">
							Press
						</a>
					</li>
					<li>
						<a href="/events" class="">
							Upcoming Events
						</a>
					</li>
					<!--<li>
						<a href="/get-involved" class="">
							Get Involved
						</a>
					</li> -->
					<li>
						<a href="/contact" class="">
							Contact
						</a>
					</li>
				</ul>
			</li>
			<li class="has-sub-menu closed nav-menu-primary-item">
				<a href="/blog" class="dropdown-link closed mobile-closed <?php if( is_single() || is_category() || is_page(193) ): echo ' nav-current '; endif; ?>" id="nav-link-blog" data-dropdown-target="blog">
					Blog
					<span class="icon" data-icon="ﬁ"></span>
				</a>
				<ul class="sub-menu" id="sub-menu-blog">
					<li>
						<a href="/blog" class="">
							Recent Posts
						</a>
					</li>
					<li>
						<a href="/blog?category=3d" class="">
							Sculptures
						</a>
					</li>
					<li>
						<a href="/blog?category=2d" class="">
							Murals
						</a>
					</li>
				</ul>
			</li>
			<li class="has-sub-menu closed nav-menu-primary-item">
				<a href="/donate" class="dropdown-link closed mobile-closed <?php if( is_page(189) ): echo ' nav-current '; endif; ?>" id="nav-link-donate" data-dropdown-target="donate">
					Donate
					<span class="icon" data-icon="ﬁ"></span>
				</a>
				<ul class="sub-menu" id="sub-menu-donate">
					<li>
						<a href="/donate#donation" class="">
							Donate Now
						</a>
					</li>
					<li>
						<a href="/donate#ways-to-donate" class="">
							Ways to Donate
						</a>
					</li>
				</ul>
			</li>
<!-- 			<li class="nav-menu-primary-item" id="nav-menu-donate-item">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="paypal-button-form">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="T3NSG4UVFRPMQ">
					<span class="extra-text">Donate</span>
					<input type="image" src="<?php bloginfo('template_directory'); ?>/images/donate-button-new.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" class="paypal-button-image">
					<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" class="paypal-button-hidden-image">
				</form>
			</li> --> 
		</ul>
	</div>
</nav>
<div class="hamburger menu-toggle">
	<span class="hamburger-line hl-1"></span>
	<span class="hamburger-line hl-2"></span>
	<span class="hamburger-line hl-3"></span>
</div>

