
<section class="block padded bg-white tacwc page-content" id="donation">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h2 class="mb2">
					<?php the_field('donate_section_heading'); ?>
				</h2>
			</div>
		</div>
		<?php wc_print_notices(); ?>
		<div class="row pb4">
			<div class="col-lg-6 col-xl-5 donation-form" id="donation-form-container">
				<form class="cart" action="<?php the_permalink(); ?>" method="post" enctype='multipart/form-data'>
					<?php 
					$post_objects = get_field('donation_levels');
					if( $post_objects ): ?>
						<?php $count = 0; ?>
						<div id="donation-purpose" class="mb2">
							<label class="" for="donation-purpose" >I would like my donation to support:</label>
							<select name="donation-purpose" id="donation-purpose-dropdown" class="" tabindex='2'  aria-invalid="false">
								<?php if( have_rows('donation_purposes') ): ?>
									<?php $count2 = 0; ?>
									<?php while ( have_rows('donation_purposes') ) : the_row(); ?>
										<option value='<?php the_sub_field('donation_purpose'); ?>' <?php if($count2 === 0): ?>selected='selected'<?php endif; ?> ><?php the_sub_field('donation_purpose'); ?></option>
										<?php $count2++; ?>
									<?php endwhile; ?>
								<?php endif; ?>
							</select>
						</div>
						<div id="donation-levels" class="mb4">
							<?php foreach( $post_objects as $post): // variable must be called $post (IMPORTANT) ?>
								<?php setup_postdata($post); ?>
								<?php 
								$product_id = $post->ID;
								$product = wc_get_product( $product_id );
								$current_price = $product->get_price();
								if( $count === 0 ): $currentDonationID = $product_id; endif;
								?>
								<?php if( $product_id === 1517 ){ ?>
									<button class="button-donation-level button-donation-toggle button button-simple">Other Amount</button>
									<?php 
									$name_your_price_id = $product_id->ID;
									$min_price = get_field('minimum_price', $id);
									$show_min_price = false;
									$suggested_price = get_field('suggested_price', $id);
									?>
									<?php // this is the NYP input for the Name Your Price product. All values are currently hardcoded. ?>
									<div class="nyp hidden mt1" id="nyp-fields" data-price="<?php echo $suggested_price; ?>" data-minimum-error="Please enter at least <?php echo $min_price; ?>." data-hide-minimum="1" data-hide-minimum-error="Please enter a higher amount." data-max-price="" data-maximum-error="Please enter less than or equal to %%MAXIMUM%%." data-min-price="<?php echo $min_price; ?>">
										<input id="nyp" name="nyp" type="text" value="<?php echo $suggested_price ?>" title="nyp" class="input-text amount nyp-input text" placeholder="Enter an amount to donate" />
										<?php if( $min_price && $show_min_price ): ?>
											<p class="small">The minimum donation is $<?php echo $min_price ?></p>
										<?php endif; ?>
									</div>
								<?php } else{ ?>
									<button class="button-donation-level button-donation-select button button-simple <?php if($count === 0): echo 'active'; endif; ?>" data-cart-ID="<?php echo $product_id; ?>">
										$<?php echo $current_price; ?>
									</button>
								<?php } ?>
								<?php $count++; ?>
							<?php endforeach; ?>
							<?php wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>
						</div>
						<div id="donation-add-to-cart">
							<?php // This is the submit button for the write-in donation field ?>
							<button type="submit" name="add-to-cart" id="nyp-button" value="<?php echo '1517'; //ID of name your price donation product, hardcoded ?>" class="button-simple button donate-button alt hidden">Donate</button>
							<button type="submit" name="add-to-cart" id="donate-button" class="button button-simple donate-button" value="<?php echo $currentDonationID; ?>">Donate</button>
						</div>
					<?php endif; ?>
				</form>
			</div>
			<div class="col-lg-6 col-xl-5 offset-xl-1 donation-appeal mb2">
				<p class="donation-appeal-intro mb2 hidden">
					<?php the_field('donate_section_intro_text'); ?>
				</p>
				<h3 class="donation-appeal-quote">
					<span class="quotation-mark quotation-mark-open">“</span><?php the_field('donate_quote'); ?><span class="quotation-mark quotation-mark-close">”</span>
				</h3>
				<h4 class="donation-appeal-quote-person mt1">
					<?php the_field('donate_quote_person'); ?><?php if(get_field('donate_quote_person_title')): ?>, <?php the_field('donate_quote_person_title'); ?><?php endif; ?>
				</h4>
			</div>
		</div>
	</div>
</section>