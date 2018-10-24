<section class="block padded bg-brand" id="about-board">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h3 class="mb1 white">
					Board
				</h3>
			</div>
		</div>
		<?php if( have_rows('board') ): ?>
			<?php $count = 1; ?>
			<div class="row section-content-row staff-list">
				<?php while ( have_rows('board') ) : the_row(); ?>
					<div class="col-6 col-sm-6 col-md-6 col-lg-3 mb2 board-person person">
						<div class="person-image">
							<?php $image = get_sub_field('person_image');
							$image = $image['sizes']['person']; ?>
							<img src="<?php echo $image; ?>" >
						</div>
						<div class="person-text">
							<h4 class="bold font-main person-name mb0">
								<?php the_sub_field('person_name'); ?>
							</h4>
							<h4 class="font-main person-board-title mb1">
								<?php the_sub_field('person_board_title'); ?>
							</h4>
							<h4 class="font-main person-title">
								<?php the_sub_field('person_title'); ?>
							</h4>
						</div>
					</div>
					<?php $count++; ?>
				<?php endwhile; ?>
			</div>
		<?php endif; ?>
	</div>
</section>