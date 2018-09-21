<div class="page-nav present before">
	<div class="container-fluid container-fluid-stretch">
		<div class="row">
			<div class="col col-lg-10 offset-lg-2">
				<ul class="page-nav-list">
					<?php 
					$args = array(
						'post_parent' => 161,
						'post_type' => 'page',
						'orderby' => 'menu_order'
					);
					$child_query = new WP_Query( $args );
					while ( $child_query->have_posts() ) : $child_query->the_post(); ?>
						<li>
							<a href="#program-<?php global $post; echo $post->post_name; ?>" class="jump">
								<?php the_title(); ?>
							</a>
						</li>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</ul>
			</div>
		</div>
	</div>
</div>