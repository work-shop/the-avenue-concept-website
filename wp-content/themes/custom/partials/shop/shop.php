<section id="shop" class="tacwc">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-5 offset-md-7">
				<h1 class="page-hero-title d-flex justify-content-end">
					<?php the_field('shop_heading'); ?>
				</h1>
			</div>
		</div>
		<div id="shop-main" class="row padded">
			<div id="shop-products" class="col-lg-8">
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
						<div class="product shop-product card card-product filter-target <?php Helpers::filter_categories('product_cat'); ?>">
							<a href="<?php the_permalink(); ?>">
								<div class="card-image">
									<?php the_post_thumbnail( 'shop' ); ?>
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
				<div class="product shop-product card card-product shop-product-donation filter-target donations">
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
			<div id="shop-sidebar" class="col-lg-4">
				<div class="sidebar filters shop-filters">
					<div class="row">
						<div class="col">
							<h5 class="sidebar-heading font-main">
								Filter By Type
							</h5>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<ul class="sidebar-categories">
								<li class="filter-all">
									<a href="#" class="filter-button filter-button-category" data-target="all" id="filter-button-all">
										All
									</a>
								</li>
								<br>
								<?php 
								$orderby = 'name';
								$order = 'asc';
								$hide_empty = true ;
								$cat_args = array(
									'orderby'    => $orderby,
									'order'      => $order,
									'hide_empty' => $hide_empty,
								);
								$product_categories = get_terms( 'product_cat', $cat_args );
								if( !empty($product_categories) ):
									foreach ($product_categories as $key => $category) : ?>
										<li>
											<a href="#" class="filter-button filter-button-category" data-target="<?php echo $category->slug; ?>">
												<?php echo $category->name; ?>
											</a>
										</li>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>