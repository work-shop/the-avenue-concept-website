<?php if( get_field('coverage')): ?>
<section class="block padded bg-white" id="press-coverage">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h3 class="mb2">
					<?php the_field('coverage_heading'); ?>
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
