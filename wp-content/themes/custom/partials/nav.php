
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
				<a href="/programs" class="dropdown-link mobile-closed <?php if( Helpers::is_tree(161) ): echo ' nav-current '; endif; ?>" id="nav-link-about" data-dropdown-target="programs">
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
				<div id="nav-curve-1" class="nav-curve">
					<?php include get_template_directory() . '/images/nav-curve-1.svg'; ?>
				</div>
			</li>
			<li class="has-sub-menu closed nav-menu-primary-item">
				<a href="/artworks" class="dropdown-link mobile-closed <?php if( Helpers::is_tree(187) ): echo ' nav-current '; endif; ?>" id="nav-link-artworks" data-dropdown-target="artworks">
					Artworks
					<span class="icon" data-icon="ﬁ"></span>
				</a>
				<ul class="sub-menu" id="sub-menu-artworks">
					<li>
						<a href="/artworks" class="">
							Map
						</a>
					</li>
					<li>
						<a href="/artworks" class="">
							On View Now
						</a>
					</li>
					<li>
						<a href="/artworks/?program=3-D" class="">
							3-D
						</a>
					</li>
					<li>
						<a href="/artworks/?program=2-D" class="">
							2-D
						</a>
					</li>
				</ul>
				<div id="nav-curve-2" class="nav-curve">
					<?php include get_template_directory() . '/images/nav-curve-1.svg'; ?>
				</div>
			</li>
			<li class="has-sub-menu closed nav-menu-primary-item">
				<a href="/about" class="dropdown-link mobile-closed <?php if( is_page(159) ): echo ' nav-current '; endif; ?>" id="nav-link-about" data-dropdown-target="about">
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
						<a href="/media" class="">
							Media
						</a>
					</li>
					<li>
						<a href="/events" class="">
							Upcoming Events
						</a>
					</li>
<!-- 					<li>
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
			<li class="nav-menu-primary-item">
				<a href="/blog" class="<?php if( is_single() || is_category() || is_front_page() ): echo ' nav-current '; endif; ?>" id="nav-link-blog" >Blog</a>
			</li>
			<li class="nav-menu-primary-item">
				<a href="/donate" class="<?php if( Helpers::is_tree(189) ): echo ' nav-current '; endif; ?>" id="nav-link-donate" >Donate</a>
			</li>
		</ul>
	</div>

</nav>
<div class="hamburger menu-toggle">
	<span class="hamburger-line hl-1"></span>
	<span class="hamburger-line hl-2"></span>
	<span class="hamburger-line hl-3"></span>
</div>

