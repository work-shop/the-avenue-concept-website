
<nav id="nav" class="fixed before">
	<div id="nav-background">
		<?php include get_template_directory() . '/images/nav-background.svg'; ?>
	</div>
	<div id="logo" class="logo">
		<a href="/" title="Home">
			<?php get_template_part('partials/logo'); ?>
		</a>
	</div>
	<div id="nav-menus">
		<ul class="nav-menus-list">
			<li class="has-sub-menu closed nav-menu-primary-item">
				<a href="/about" class="dropdown-link <?php if( Helpers::is_tree(161) ): echo ' nav-current '; endif; ?>" id="nav-link-about" data-dropdown-target="programs">
					Programs
					<span class="icon" data-icon="ﬁ"></span>
				</a>
				<ul class="sub-menu" id="sub-menu-programs">
					<li>
						<a href="/programs" class="blob-background blob-background-nav"> 
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
							<a href="<?php the_permalink(); ?>" class="blob-background blob-background-nav">
								<?php the_title(); ?>
							</a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</ul>
			</li>
			<li class="has-sub-menu closed nav-menu-primary-item">
				<a href="/about" class="dropdown-link <?php if( Helpers::is_tree(187) ): echo ' nav-current '; endif; ?>" id="nav-link-artworks" data-dropdown-target="artworks">
					Artworks
					<span class="icon" data-icon="ﬁ"></span>
				</a>
				<ul class="sub-menu" id="sub-menu-artworks">
					<li>
						<a href="/artworks" class="blob-background blob-background-nav">
							Map
						</a>
					</li>
					<li>
						<a href="/artworks/on-view-now" class="blob-background blob-background-nav">
							On View Now
						</a>
					</li>
					<li>
						<a href="/artworks/sculptures" class="blob-background blob-background-nav">
							Sculptures
						</a>
					</li>
					<li>
						<a href="/artworks/murals" class="blob-background blob-background-nav">
							Murals
						</a>
					</li>
				</ul>
			</li>
			<li class="has-sub-menu closed nav-menu-primary-item">
				<a href="/programs" class="dropdown-link <?php if( is_page(159) ): echo ' nav-current '; endif; ?>" id="nav-link-about" data-dropdown-target="about">
					About
					<span class="icon" data-icon="ﬁ"></span>
				</a>
				<ul class="sub-menu" id="sub-menu-about">
					<li>
						<a href="/about" class="blob-background blob-background-nav">
							About TAC
						</a>
					</li>
					<li>
						<a href="/media" class="blob-background blob-background-nav">
							Media
						</a>
					</li>
					<li>
						<a href="/events" class="blob-background blob-background-nav">
							Upcoming Events
						</a>
					</li>
					<li>
						<a href="/get-involved" class="blob-background blob-background-nav">
							Get Involved
						</a>
					</li>
					<li>
						<a href="/contact" class="blob-background blob-background-nav">
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

