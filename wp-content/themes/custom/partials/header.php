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
	if( get_field('social_media_title') ):
		$social_title = get_field('social_media_title'); 
	else:
		$social_title = get_bloginfo( 'name' );
	endif;
	if( get_field('social_media_description') ):
		$social_description = get_field('social_media_description');
	else:
		$social_description = '';
	endif;
	if( get_field('social_media_url') ):
		$social_url = get_field('social_media_url'); 
	else: 
		$social_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	endif;
	if( get_field('social_media_image') ):
		$social_image_array = get_field('social_media_image');
		$social_image = $social_image_array['sizes']['fb'];
	else:
		$social_image = get_bloginfo( template_directory ) . '/images/social_card_v1.jpg';
	endif;

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

	<script src="https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyCFQ2F5EK1p-g-xShVMv51XClmBGPTIUFQ"></script>

	<?php wp_head(); ?>

	<?php
	$sitewide_alert_on = get_field('show_sitewide_alert', 'option');
	if( $sitewide_alert_on === true ):
		if( !isset($_COOKIE['tac_show_sitewide_alert']) || $_COOKIE['tac_show_sitewide_alert'] === false ):
			$sitewide_alert_class = 'sitewide-alert-on';
			$show_sitewide_alert = true;
		endif;
	endif;
	$announcement_on = get_field('show_announcement', '6');
	if( is_page(6) && $announcement_on === true ):
		$announcement_class = 'announcement-on';
		$show_announcement = true;
		//if( !isset($_COOKIE['tac_show_announcement']) || $_COOKIE['tac_show_announcement'] === false ):
		//endif;
	endif;
	?>

</head>
<body <?php body_class('loading before-scroll modal-off menu-closed dropdown-off mobile-dropdown-off curve-off artworks-error-off' . $sitewide_alert_class . ' ' . $announcement_class . ' '); ?>>

	<?php get_template_part('partials/announcements'); ?>
	<?php get_template_part('partials/nav'); ?>
	<?php get_template_part('partials/menus'); ?>

	<main id="content">
