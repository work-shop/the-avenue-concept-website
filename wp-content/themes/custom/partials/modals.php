<div id="modals">
	<div class="modal off bg-brand" id="modal-staff">
		<div class="modal-container">
			<div class="row mb3">
				<div class="col d-flex justify-content-center">
					<span class="modal-swap-active white ml1 mr1">
						Staff
					</span>
					<a href="#board" class="modal-swap modal-swap-link white ml1 mr1" data-modal-target="modal-board">
						Board
					</a>
				</div>
			</div>
			<?php if( have_rows('staff') ): ?>
				<div class="row">
					<?php  while ( have_rows('staff') ) : the_row(); ?>
						<div class="col-sm-6 modal-person mb2">
							<h3 class="modal-person-name white  centered">
								<?php the_sub_field('person_name'); ?>
							</h3>
							<h4 class="modal-person-title white font-main mb0  centered">
								<?php the_sub_field('person_title'); ?>
							</h4>
							<?php if( get_sub_field('person_email') ): ?>
								<h4 class="modal-person-email font-main centered">
									<a href="mailto:email" target="_blank" class="white modal-person-email-link centered">
										<?php the_sub_field('person_email'); ?>
									</a>
								</h4>
							<?php endif; ?>
						</div>
					<?php endwhile; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="modal off bg-brand" id="modal-board">
		<div class="modal-container p2">
			<div class="row mb3">
				<div class="col d-flex justify-content-center">
					<a href="#staff" class="modal-swap modal-swap-link white ml1 mr1" data-modal-target="modal-staff">
						Staff
					</a>
					<span class="modal-swap-active white ml1 mr1">
						Board
					</span>
				</div>
			</div>
			<?php if( have_rows('board') ): ?>
				<div class="row">
					<?php  while ( have_rows('board') ) : the_row(); ?>
						<div class="col-sm-6 modal-person mb2">
							<h3 class="modal-person-name white mb0 centered">
								<?php the_sub_field('person_name'); ?>
								<?php if(get_sub_field('person_board_title')): ?>, <?php the_sub_field('person_board_title'); ?>
								<?php endif; ?>
							</h3>
							<h4 class="modal-person-title font-main white d-flex justify-content-center centered">
								<?php the_sub_field('person_title'); ?>
							</h4>
						</div>
					<?php endwhile; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div id="modal-close">
		<a href="#" class="modal-close">
			<span class="icon white" data-icon="’">
			</span>
		</a>
	</div>
	<div class="modal-close" id="blanket">
	</div>
</div>