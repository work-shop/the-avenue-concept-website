<div class="page-nav before">
	<div class="container-fluid container-fluid-stretch">
		<div class="row">
			<div class="col col-lg-10 offset-lg-2">
				<ul class="page-nav-list">
					<?php if( get_field('coverage_years') ): ?>
						<li>
							<a href="#press-coverage" class="jump">
								Coverage
							</a>
						</li>
					<?php endif; ?>
					<?php if( get_field('press_kit_heading') || get_field('press_kit_description') || get_field('press_kit_link')): ?>
					<li>
						<a href="#press-kit" class="jump">
							Press Kit
						</a>
					</li>
				<?php endif; ?>
				<li>
					<a href="#videos" class="jump">
						Videos
					</a>
				</li>
				<li>
					<a href="#social-media" class="jump">
						Social Media
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>
</div>