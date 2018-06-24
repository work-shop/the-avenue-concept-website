<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<title>
		<?php 
		if( is_front_page() ){
			bloginfo( 'name' ); echo ' | ';  bloginfo( 'description' );
		} else{
			wp_title( false ); echo ' | '; bloginfo( 'name' );
		}
		?>
	</title>

	<!-- typekit or other remote fonts -->

	<link rel="icon" type="image/png" sizes="16x16" href="<?php bloginfo('template_directory'); ?>/images/favicon-16x16.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php bloginfo('template_directory'); ?>/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="<?php bloginfo('template_directory'); ?>/images/favicon-96x96.png">
	<link rel="apple-touch-icon" href="<?php bloginfo('template_directory'); ?>/images/apple-icon.png">

	<meta name="description" content="<?php bloginfo('description'); ?>">
	<meta name="author" content="Work-Shop">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<?php wp_head(); ?>

</head>
<body <?php body_class('loading before-scroll modal-off menu-closed dropdown-off'); ?>>

	<?php get_template_part('partials/nav'); ?>
	<?php get_template_part('partials/menus'); ?>

	<main id="content">
