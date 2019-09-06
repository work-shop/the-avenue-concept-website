<?php 
$product_id = $post->ID;
$product = wc_get_product( $product_id );
$current_price = $product->get_price();
$add_to_cart_url = $product->add_to_cart_url();
?>
<section id="product" class="tacwc">
	<div class="container-fluid">
		<div class="row mb3">
			<div class="col-md-6 col-lg-5">
				<h2 class="product-heading mb2">
					<?php the_title(); ?>
				</h2>
				<h4 class="product-price font-main">
					$<?php echo $current_price; ?>
				</h4>
			</div>
		</div>
		<div id="product-main" class="row">
			<div class="col-md-6">
				<div class="wysiwyg product-description mb3">
					<?php the_content(); ?>
				</div>
				<div class="product-add-to-cart">
					<a href="<?php echo $add_to_cart_url; ?>" class=" button button-simple button-small">
						Add To Cart
					</a>
				</div>
			</div>
			<div class="col-md-6">
				<div class="product-image">
					<?php the_post_thumbnail( 'blog' ); ?>
				</div>
			</div>
		</div>
	</div>
</section>