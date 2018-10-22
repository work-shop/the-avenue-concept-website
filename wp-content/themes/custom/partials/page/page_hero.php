<?php if( get_field('show_hero') ): ?>
	<?php $hero_image = get_field('hero_image');
	$hero_image = $hero_image['sizes']['page_hero'];
	?>
	<section class="block page-hero present" id="page-hero">
		<div class="block-background page-hero-image" style="background-image: url('<?php echo $hero_image; ?>');">
		</div>
	</section>
	<section class="page-title-container">
		<div class="page-hero-curve">
			<?php include get_template_directory() . '/images/page-hero-curve-02.svg'; ?>
		</div>
		<div class="page-hero-text">
			<div class="container-fluid container-fluid-stretch">
				<div class="row">
					<div class="col-lg-5 offset-lg-7">
						<h1 class="page-hero-title d-flex justify-content-end">
							<?php the_title(); ?>
						</h1>
					</div>
				</div>
			</div>		
		</div>
	</section>
	<?php endif; ?>