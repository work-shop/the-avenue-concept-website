<div class="page-nav before">
	<div class="container-fluid container-fluid-stretch">
		<div class="row">
			<div class="col col-lg-10 offset-lg-2">
				<ul class="page-nav-list">
					<li>
						<a href="#about-mission" class="jump">
							Mission
						</a>
					</li>
					<li>
						<a href="#about-values" class="jump">
							Values
						</a>
					</li>
					<?php if( have_rows('impact') ): ?>
						<li>
							<a href="#about-impact" class="jump">
								Impact
							</a>
						</li>
					<?php endif; ?>
					<li>
						<a href="#about-staff" class="jump">
							Staff
						</a>
					</li>
					<li>
						<a href="#about-board" class="jump">
							Board
						</a>
					</li>
					<?php if( have_rows('sponsors_and_partners') ): ?>
						<li>
							<a href="#about-partners" class="jump">
								Partners
							</a>
						</li>
					<?php endif; ?>
					<?php if( have_rows('donors_list','189') ): ?>
						<li>
							<a href="#donors" class="jump">
								Donors
							</a>
						</li>
					<?php endif; ?>
					<li>
						<a href="#contact" class="jump">
							Contact
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>