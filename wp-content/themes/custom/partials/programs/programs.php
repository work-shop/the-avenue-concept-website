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
<section class="block padded bg-white page-content" id="programs">
	<div class="container-fluid container-fluid-stretch">
		<?php 
		$args = array(
			'post_parent' => 161,
			'post_type' => 'page',
			'orderby' => 'menu_order'
		);
		$child_query = new WP_Query( $args );
		while ( $child_query->have_posts() ) : $child_query->the_post(); ?>
			<div class="row program-row pt1 pb1 mb2" id="program-<?php global $post; echo $post->post_name; ?>">
				<div class="program-col-image">
					<?php 
					$image = get_field('program_image');
					if( !empty($image) ): ?>
						<div class="program-image">
							<img src="<?php echo $image['sizes']['page_hero']; ?>" alt="<?php echo $image['alt']; ?>" />
						</div>
					<?php endif; ?>
				</div>
				<div class="program-col-text">
					<h3 class="program-title mb2 mt2">
						<?php the_title(); ?>
					</h3>
					<p class="program-description">
						<?php the_field('program_description'); ?>
					</p>
					<a href="<?php the_permalink(); ?>" class="program-link">
						Learn More
					</a>
				</div>
			</div>
		<?php endwhile; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</section>