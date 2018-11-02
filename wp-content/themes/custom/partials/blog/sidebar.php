<div class="sidebar filters">
	<div class="row">
		<div class="col">
			<h5 class="sidebar-heading font-main">
				Filter By Topic
			</h5>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<ul class="sidebar-categories">
				<li class="filter-all">
					<a href="#" class="filter-button filter-button-category" data-target="all" id="filter-button-all">
						All
					</a>
				</li>
				<br>
				<?php
				$categories = get_categories( array(
					'orderby' => 'name',
					'order'   => 'ASC'
				) );
				foreach( $categories as $category ) : ?>
					<li>
						<a href="#" class="filter-button filter-button-category" data-target="<?php echo $category->slug; ?>">
							<?php echo $category->name; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>