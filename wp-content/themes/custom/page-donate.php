
<?php get_template_part('partials/header'); ?>

<?php //get_template_part('partials/ecommerce_content' ); ?>

<?php if(false): ?>
<section class="vh100 pt9">
	<div class="container-fluid">
		<?php //echo do_shortcode('[woocommerce_one_page_checkout template="product-list" product_ids="2127,2128,2126"]'); ?>
		<?php //the_content(); ?>
	</div>
</section>
<?php endif; ?>

<?php //get_template_part('partials/page/page_hero' ); ?>

<?php //get_template_part('partials/donate/intro' ); ?>

<?php get_template_part('partials/donate/donation_wc' ); ?>

<?php get_template_part('partials/donate/ways' ); ?>

<?php get_template_part('partials/donate/donors' ); ?>

<?php get_template_part('partials/footer' ); ?>