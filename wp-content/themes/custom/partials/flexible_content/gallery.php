<?php
$fc = get_field('page_flexible_content');
$fc_row = $fc[$GLOBALS['fc_index']]; 
$section_heading = $fc_row['section_heading'];
$section_background_color = $fc_row['section_background_color'];
$section_text_color = $fc_row['section_text_color'];
$gallery = $fc_row['gallery'];
$gallery_title = $fc_row['gallery_title'];
?>
<section class="block flexible-content fc fc-gallery <?php echo $section_background_color; ?>">
	<div class="container-fc">
		<?php if( $section_heading ): ?>
			<div class="row fc-section-heading">
				<div class="col-sm-12">
					<h2 class="serif fc-section-heading-text <?php echo $section_text_color; ?>">
						<?php echo $section_heading; ?>
					</h2>
				</div>
			</div>
		<?php endif; ?>
		<?php if( $gallery ): ?>
			<?php if( $gallery_title ): ?>
				<div class="fc-gallery-title">
					<h4 class="centered bold <?php echo $section_text_color; ?>">
						<?php echo $gallery_title; ?>
					</h4>
				</div>
			<?php endif; ?>
			<div class="fc-gallery-slick slick slick-default">
				<?php foreach ($gallery as $image): ?> 
					<div class="slick-slide fc-gallery-slide">
						<div class="fc-gallery-slide-image-container">
							<div class="fc-gallery-slide-image" style="background-image: url('<?php echo $image['sizes']['page_hero']; ?>');">
							</div>
						</div>
						<div class="fc-gallery-slide-caption-container">
							<?php if( $image['caption'] ): ?>
								<p class="fc-image-caption"><?php echo $image['caption']; ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>