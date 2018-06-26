<section class="block section-padded spy-target bg-brand spy-first vh100" id="home-work">
	<div class="container-fluid container-fluid-stretch">
		<div class="row section-header-row">
			<div class="col-lg-12 col-xl-8">
				<h2 class="section-header white">
					Founded in Providence, RI in 2012, The Avenue Concept was the city's first private public art program. Since then it has installed or exhibited more than 150 works of public art, and invested $1.1million in both artwork and infrastucture.
				</h2>
			</div>
		</div>
		<div class="row section-content-row">
			<div class="col">
				<?php if( have_rows('field') ): ?>
					<ul>
						<?php  while ( have_rows('field') ) : the_row(); ?>
							<li>
								<?php the_sub_field('field'); ?>
							</li>
						<?php endwhile; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>