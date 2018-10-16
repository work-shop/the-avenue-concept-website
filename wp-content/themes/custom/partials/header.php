<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<title>
		<?php 
		if( is_front_page() ){
			bloginfo( 'name' ); echo ' | ';  bloginfo( 'description' );
		} elseif( is_404() ){
			bloginfo( 'name' );
		} 
		else{
			wp_title( false ); echo ' | '; bloginfo( 'name' );
		}
		?>
	</title>

	<?php 
	$social_title = get_field('social_media_title'); 
	$social_description = get_field('social_media_description');
	$social_url = get_field('social_media_url'); 
	$social_image = get_bloginfo( template_directory ) . '/images/social_card_v1.jpg';
	?>

	<!-- Facebook Open Graph data -->
	<meta property="og:title" content="<?php echo $social_title; ?>" />
	<meta property="og:description" content="<?php echo $social_description; ?>" />
	<meta property="og:image" content="<?php echo $social_image; ?>" />
	<meta property="og:url" content="<?php echo $social_url; ?>" />
	<meta property="og:type" content="website" />

	<!-- Twitter Card data -->
	<meta name="twitter:card" value="<?php echo $social_description; ?>">

	<link rel="icon" type="image/png" sizes="16x16" href="<?php bloginfo('template_directory'); ?>/images/favicon-16x16.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php bloginfo('template_directory'); ?>/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="<?php bloginfo('template_directory'); ?>/images/favicon-96x96.png">
	<link rel="apple-touch-icon" href="<?php bloginfo('template_directory'); ?>/images/apple-icon.png">

	<meta name="description" content="<?php bloginfo('description'); ?>">
	<meta name="author" content="Work-Shop Design Studio http://workshop.co">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<?php wp_head(); ?>

</head>
<body <?php body_class('loading before-scroll modal-off menu-closed dropdown-off mobile-dropdown-off'); ?>>

	<?php get_template_part('partials/nav'); //when turning this back on, be sure to modify home.scss to turn back on body padding ?>
	<?php get_template_part('partials/menus'); ?>

	<main id="content">
