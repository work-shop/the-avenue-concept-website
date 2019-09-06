<section id="join" class="tacwc">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-6 col-lg-5">
				<h2 class="join-heading mb2">
					<?php the_field('join_heading'); ?>
				</h2>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 col-lg-5 mb5">
				<p class="join-introduction mb3">
					<?php the_field('join_introduction'); ?>
				</p>
				<div class="join-button">
					<?php $link = get_field('join_introduction_button'); ?>
					<?php if( $link ): ?>
						<a href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>" class="button button-simple">
								<?php echo $link['title']; ?>
							</a>
						<?php endif; ?>
				</div>
			</div>
			<div class="col-md-6 col-lg-6 offset-lg-1 join-membership-levels">
				<h2 class="membership-levels-heading">
					Membership Levels
				</h2>
				<?php 
				$post_objects = get_field('membership_levels');
				if( $post_objects ): ?>
					<div data-accordion-group>
						<?php foreach( $post_objects as $post): // variable must be called $post (IMPORTANT) ?>
							<?php setup_postdata($post); ?>
							<?php 
							$product_id = $post->ID;
							$product = wc_get_product( $product_id );
							$current_price = $product->get_price();
							$add_to_cart_url = $product->add_to_cart_url();
							?>
							<div class="accordion multi-collapse membership-level" data-accordion>
								<div class="accordion-label" data-control>
									<h4 class="accordion-title">
										<span class="bold mr2"><?php the_title(); ?></span> <span class="membership-level-price">$<?php echo $current_price; ?></span>
									</h4>
									<span class="icon" data-icon="”"></span>
								</div>
								<div class="accordion-body" data-content>
									<div class="accordion-content-inner">
										<div class="wysiwyg mb3">
											<?php the_content(); ?>
										</div>
										<?php if( !is_user_logged_in() && has_membership_in_cart() ): ?>
										<div class="bg-error p1 mt2">
											<h4 class="font-main m0 error">
												You already have a membership in your <a href="/cart" class="underline">cart.</a> <br>Memberships are limited to one per customer.
											</h4>
										</div>
										<?php else: ?>
											<div class="accordion-link membership-link-button d-flex justify-content-center">
												<a href="<?php echo $add_to_cart_url; ?>" class=" button button-simple button-small">
													Purchase New Membership
												</a>
												<a href="/renew-your-membership" class="ml1 button button-simple button-small hidden">
													Renew Your Membership
												</a>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<?php wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>