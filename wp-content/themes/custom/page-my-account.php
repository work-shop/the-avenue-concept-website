
<?php get_template_part('partials/header'); ?>

<?php if( is_user_logged_in() ){ ?>
	<?php get_template_part('partials/ecommerce/page_nav'); ?>
<?php } else{} ?>

<?php get_template_part('partials/ecommerce_content' ); ?>

<?php get_template_part('partials/footer' ); ?>
