<section class="block padded" id="post-intro">
	<div class="container-fluid container-fluid-stretch">
		<div class="row">
			<div class="col-sm-12 col-md-6 col-lg-6 mb2">
				<p class="post-excerpt">
					<?php the_field('post_excerpt'); ?>
				</p>
			</div>
			<div class="col-sm-12 col-md-6 col-lg-5 offset-lg-1 post-metadata">
				<div class="post-metadata-tags mb1">
					<ul>
						<?php 
						$terms = get_the_terms( $post, 'category' );
						if( $terms ):
							foreach ($terms as $term) : ?>
								<li>
									<a href="/blog?category=<?php echo $term->slug; ?>" class="post-metadata-tag">
										<?php echo $term->name; ?>
									</a>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</div>
				<div class="post-metadata-byline">
					<div class="row">
						<div class="col-md-4">
							<h5 class="uppercase font-main medium post-metadata-label m0">
								Author
							</h5>
						</div>
						<div class="col-md-8">
							<h4 class="post-metadata-value font-main"><?php the_field('author'); ?></h4>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<h5 class="uppercase font-main medium post-metadata-label m0">
								Date
							</h5>
						</div>
						<div class="col-md-8">
							<h4 class="post-metadata-value font-main"><?php the_field('post_date'); ?></h4>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>