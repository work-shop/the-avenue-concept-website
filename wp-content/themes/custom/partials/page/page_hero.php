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
					<div class="col-lg-7 col-md-5 hero-back d-flex align-items-end justify-content-end justify-content-md-start">
						<?php 
						if ( Helpers::is_tree(161) && is_page(161) === false ): 
							$link = '/programs';
							$title = 'All Programs';
						elseif ( is_single() ):
							$link = '/blog';
							$title = 'Blog';
						?>
						<a href="<?php echo $link; ?>" class="hero-back-link">
							<span class="icon mr1" data-icon="‰"></span>
							Back to <?php echo $title; ?>
						</a>
					<?php endif; ?>
				</div>
				<div class="col-lg-5 col-md-7">
					<h1 class="page-hero-title d-flex justify-content-end">
						<?php the_title(); ?>
					</h1>
				</div>
			</div>
		</div>		
	</div>
</section>
<?php endif; ?>