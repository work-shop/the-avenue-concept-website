<section class="block padded bg-white page-content" id="donation">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h3 class="mb2">
					<?php the_field('donate_section_heading'); ?>
				</h3>
			</div>
		</div>
		<div class="row">
			<h1>Please note - this page is not currently set up to take donations, and the form below is not active. Please check back soon.</h1>
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
			<div class="col-lg-6 donation-form">
				<?php $form_id = get_field('donate_page_form'); ?>
				<?php gravity_form($form_id, false, false, false, '', true, 1); ?>
			</div>
		</div>
	</div>
</section>