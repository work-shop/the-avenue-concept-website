<?php if( get_field('show_hero') ): ?>
	<?php $hero_image = get_field('hero_image');
	$hero_image = $hero_image['sizes']['page_hero'];
	?>
	<section class="block page-hero" id="page-hero">
		<div class="block-background page-hero-image" style="background-image: url('<?php echo $hero_image; ?>');">
		</div>
	</section>
<?php endif; ?>