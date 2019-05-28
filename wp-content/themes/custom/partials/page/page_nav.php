	<?php if( get_field('include_sub_navigation_menu') ){ ?>
		<div class="page-nav present before">
			<div class="container-fluid container-fluid-stretch">
				<div class="row">
					<div class="col col-lg-10 offset-lg-2">
						<?php if( have_rows('sub_navigation_menu') ): ?>
							<ul class="page-nav-list">
								<?php  while ( have_rows('sub_navigation_menu') ) : the_row(); ?>
									<li>
										<a href="#fc-<?php the_sub_field('section_id'); ?>" class="jump">
											<?php the_sub_field('link_text'); ?>
										</a>
									</li>
								<?php endwhile; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>