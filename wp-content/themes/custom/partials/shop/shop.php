<section id="shop" class="tacwc">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-6 col-lg-5">
				<h2 class="shop-heading mb2">
					<?php the_field('shop_heading'); ?>
				</h2>
			</div>
		</div>
		<div id="shop-products">
			<?php 
			$post_objects = get_field('shop_products');
			if( $post_objects ): ?>
				<?php foreach( $post_objects as $post): // variable must be called $post (IMPORTANT) ?>
					<?php setup_postdata($post); ?>
					<?php 
					$product_id = $post->ID;
					$product = wc_get_product( $product_id );
					$current_price = $product->get_price();
					?>
					<div class="product shop-product card card-product">
						<a href="<?php the_permalink(); ?>">
							<div class="card-image">
								<?php the_post_thumbnail( 'blog' ); ?>
							</div>
							<div class="card-text">
								<h3 class="card-product-title font-main mb0 mt0">
									<?php the_title(); ?>
								</h3>
								<h4 class="card-product-price font-main m0">
									$<?php echo $current_price; ?>
								</h4>
							</div>
						</a>
					</div>
				<?php endforeach; ?>
				<?php wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>
			<?php endif; ?>
			<div class="product shop-product card card-product shop-product-donation">
				<a href="/donate">
					<div class="card-image">
						<?php 
						$image = get_field('donation_product_image');
						if( !empty($image) ): ?>
							<img src="<?php echo $image['sizes']['blog']; ?>" alt="<?php echo $image['alt']; ?>" />
						<?php endif; ?>
					</div>
					<div class="card-text">
						<h3 class="card-product-title font-main mb0 mt0">
							<?php the_field('donation_product_title'); ?>
						</h3>
						<h4 class="card-product-price font-main m0">
							<?php the_field('donation_product_price'); ?>
						</h4>
					</div>
				</a>
			</div>
		</div>
	</div>
</section>