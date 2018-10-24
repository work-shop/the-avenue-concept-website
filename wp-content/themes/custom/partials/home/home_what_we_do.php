<section class="block pt4 pb6" id="home-what-we-do">
	<div class="container-fluid">
		<div class="row">
			<div class="col">
				<h3 class="section-header">
					<?php the_field('what_we_do_heading'); ?>
				</h3>
			</div>
		</div>
		<div class="row">
			<?php $wwd = get_field('what_we_do_content'); ?>
			<div class="col-6 col-lg-3 wwd-col">
				<div class="wwd-image">
					<?php include get_template_directory() . '/images/sculpture.svg'; ?>
				</div>
				<h3 class="wwd-title">
					<?php echo $wwd['item_1']; ?>
				</h3>
			</div>
			<div class="col-6 col-lg-3 wwd-col">
				<div class="wwd-image">
					<?php include get_template_directory() . '/images/sculpture.svg'; ?>
				</div>
				<h3 class="wwd-title">
					<?php echo $wwd['item_2']; ?>
				</h3>
			</div>
			<div class="col-6 col-lg-3 wwd-col">
				<div class="wwd-image">
					<?php include get_template_directory() . '/images/sculpture.svg'; ?>
				</div>
				<h3 class="wwd-title">
					<?php echo $wwd['item_3']; ?>
				</h3>
			</div>
			<div class="col-6 col-lg-3 wwd-col">
				<div class="wwd-image">
					<?php include get_template_directory() . '/images/sculpture.svg'; ?>
				</div>
				<h3 class="wwd-title">
					<?php echo $wwd['item_4']; ?>
				</h3>
			</div>
		</div>
	</div>
</section>