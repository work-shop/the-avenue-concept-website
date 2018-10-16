<section class="block padded bg-white" id="blog">
	<div class="container-fluid container-fluid-stretch">
		<div class="row">
			<div class="col-lg-8 d-flex blog-posts">
				<div class="blog-posts-container">
					<?php 
					$args = array( 'post_type' => 'post', 'posts_per_page' => 10 );
					$loop = new WP_Query( $args );
					while ( $loop->have_posts() ) : $loop->the_post(); ?>
						<div class="post filter-target <?php Helpers::filter_categories('events-categories'); ?>">
							<div class="card-post card">
								<?php if ( has_post_thumbnail() ) : ?>
									<div class="card-image">
										<a href="<?php the_permalink(); ?>"  ?>
											<?php the_post_thumbnail('blog'); ?>
										</a>
									</div>
								<?php endif; ?>								
								<div class="card-text">
									<h5 class="font-main card-post-date mb0">
										<?php the_field('post_date'); ?>
									</h5>
									<h4 class="font-main card-post-title mt0 mb1">
										<a href="<?php the_permalink(); ?>">
											<?php the_title(); ?>
										</a>
									</h4>
									<div class="post-categories">
										<?php echo get_the_category_list(', '); ?>
									</div>
								</div>
							</div>		
						</div>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</div>
			</div>
			<div class="col-lg-4 col-xl-3 blog-sidebar d-flex mb2">
				<?php get_template_part('partials/blog/sidebar'); ?>
			</div>
		</div>
	</div>
</section>