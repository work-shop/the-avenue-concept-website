<section class="block padded bg-white page-content" id="donation">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-12">
				<h2 class="error">
					Please note this page is not currently set up to take donations, and the below form is inactive. 
				</h2>
			</div>
			<div class="col-xl-8">
				<h3 class="mb2">
					<?php the_field('donate_section_heading'); ?>
				</h3>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-6 donation-appeal mb2">
				<p class="donation-appeal-intro mb2">
					<?php the_field('donate_section_intro_text'); ?>
				</p>
				<h3 class="donation-appeal-quote">
					<span class="quotation-mark quotation-mark-open">“</span><?php the_field('donate_quote'); ?><span class="quotation-mark quotation-mark-close">”</span>
				</h3>
				<h4 class="donation-appeal-quote-person">
					<?php the_field('donate_quote_person'); ?><?php if(get_field('donate_quote_person_title')): ?>, <?php the_field('donate_quote_person_title'); ?><?php endif; ?>
				</h4>
			</div>
			<div class="col-lg-6 donation-form" id="donation-form-container">
				<?php $override = true; ?>
				<?php if(  $_SERVER['HTTP_HOST'] == 'localhost:8080' || is_user_logged_in() || $override ): ?>
					<?php $form_id = get_field('donate_page_form'); ?>
					<?php gravity_form($form_id, false, false, false, '', true, 1); ?>
					<?php else: ?>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="paypal-button-form">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="T3NSG4UVFRPMQ">
							<input type="image" src="http://d3w0jg1q5ypcyu.cloudfront.net/2018/07/donate-button-brand.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" class="paypal-button-image">
							<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" class="paypal-button-hidden-image">
						</form>
					<?php endif; ?>
					<div id="paypal-target">
					</div>
				</div>
			</div>
		</div>
	</section>