<?php if( get_field('press_kit_heading') || get_field('press_kit_description') || get_field('press_kit_link')): ?>
<section class="block padded bg-white" id="press-kit">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h3 class="section-header">
					<?php the_field('press_kit_heading'); ?>
				</h3>
			</div>
		</div>
		<div class="row section-content-row mb2">
			<div class="col">
				<p class="mb2">
					<?php the_field('press_kit_description'); ?>
				</p>
				<?php $link = get_field('press_kit_link');
				if( $link ): ?>
					<a class="button" href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>"><?php echo $link['title']; ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
