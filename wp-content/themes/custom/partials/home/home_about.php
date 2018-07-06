<section class="block section-padded spy-target bg-brand spy-first" id="home-about">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-xl-8">
				<h2 class="section-header white">
					<?php the_field('about_heading'); ?>
				</h2>
			</div>
		</div>
		<?php if( have_rows('mission_elements') ): ?>
			<?php $count = 1; ?>
			<div class="row section-content-row mb4">
				<?php  while ( have_rows('mission_elements') ) : the_row(); ?>
					<div class="col-6 mb3 mission-element">
						<div class="row">
							<div class="col-sm-1">
								<h2 class="white">
									<?php echo $count; ?>
								</h2>
							</div>
							<div class="col-sm-11 col-md-9">
								<h3 class="white font-main">
									<?php the_sub_field('mission_element_text'); ?>
								</h3>
							</div>
						</div>
					</div>
					<?php $count++; ?>
				<?php endwhile; ?>
			</div>
		<?php endif; ?>
		<div class="row">
			<div class="col-md-3 offset-md-2">
				<div class="blob-background blob-button blob-b blob-white">
					<a href="#staff" class="modal-toggle" data-modal-target="modal-staff">
						<h3 class="dark-green centered pt3 pb3">
							Staff
						</h3>
					</a>
				</div>
			</div>
			<div class="col-md-3 offset-md-1">
				<div class="blob-background blob-button blob-d blob-white">
					<a href="#board" class="modal-toggle" data-modal-target="modal-board">
						<h3 class="dark-green centered m0 pt3 pb3">
							Board
						</h3>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>