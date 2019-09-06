
<?php get_template_part('partials/header'); ?>

<?php global $post;
//check if this is a child page of the donate page
//this is a hacky workaround to catch all the child pages instead of making their own templates
if ( $post->post_parent === 189 ) { ?>

	<?php get_template_part('partials/donate/donation_wc' ); ?>

	<?php get_template_part('partials/donate/ways' ); ?>

	<?php get_template_part('partials/donate/donors' ); ?>

	<?php // else if a programs sub page ?>
<?php } else if ( $post->post_parent === 161 ) { ?>

	<?php get_template_part('partials/page/page_hero' ); ?>

	<?php get_template_part('partials/programs/program_intro' ); ?>

	<?php get_template_part('partials/flexible_content/flexible_content' ); ?>

	<?php // else not a donate or programs child page, so it's a generic page ?>
<?php } else { ?>

	<?php get_template_part('partials/page/page_hero' ); ?>

	<?php get_template_part('partials/page/page_nav' ); ?>

	<?php get_template_part('partials/flexible_content/flexible_content' ); ?>

<?php } ?>

<?php get_template_part('partials/footer' ); ?>

