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
		<ul id="nav-menu-upper">
			<li id="nav-link-shop" class="">
				<a href="/shop">Shop</a>
			</li>
			<li id="nav-link-login">
				<a href="/my-account">
					<?php
					if( is_user_logged_in() ) {
						//$user = wp_get_current_user();
						//$user_name = $user->display_name;
						//echo $user_name;
						echo 'My Account';
					} else{
						echo 'Login';
					}
					?>
				</a>
			</li>
			<li id="nav-link-cart">
				<a class="cart-customlocation" title="View Your Shopping Cart" href="<?php echo wc_get_cart_url(); ?>">
					<span class="icon" data-icon="i"></span>
					<span id="cart-number"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
				</a>
			</li>	
		</ul>
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
				<a href="/blog" class="dropdown-link closed mobile-closed <?php if( is_single('post') || is_category('post') || is_page(193) ): echo ' nav-current '; endif; ?>" id="nav-link-blog" data-dropdown-target="blog">
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
				<a href="/ave-magazine" class="dropdown-link closed mobile-closed <?php if( is_page(1289) ): echo ' nav-current '; endif; ?>" id="nav-link-magazine" data-dropdown-target="magazine">
					Magazine
					<span class="icon" data-icon="ﬁ"></span>
				</a>
				<ul class="sub-menu" id="sub-menu-magazine">
					<li>
						<a href="/ave-magazine" class="">
							Ave. Magazine
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
						<a href="/donate#donors" class="">
							Donors
						</a>
					</li>
					<li>
						<a href="/donate#ways-to-donate" class="">
							Ways to Donate
						</a>
					</li>
				</ul>
			</li>
			<li class="has-sub-menu closed nav-menu-primary-item">
				<a href="/join" class="dropdown-link closed mobile-closed <?php if( is_page(1522) ): echo ' nav-current '; endif; ?>" id="nav-link-join" data-dropdown-target="join">
					Join
					<span class="icon" data-icon="ﬁ"></span>
				</a>
				<ul class="sub-menu" id="sub-menu-join">
					<li>
						<a href="/join" class="">
							Memberships
						</a>
					</li>
					<li>
						<a href="/my-account/subscriptions" class="">
							Manage Your<br>Membership
						</a>
					</li>
				</ul>
			</li>
		</ul>
	</div>
</nav>
<div class="hamburger menu-toggle">
	<span class="hamburger-line hl-1"></span>
	<span class="hamburger-line hl-2"></span>
	<span class="hamburger-line hl-3"></span>
</div>

