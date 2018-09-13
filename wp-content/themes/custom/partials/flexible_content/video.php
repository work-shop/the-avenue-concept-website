<?php
$fc = get_field('page_flexible_content');
$fc_row = $fc[$GLOBALS['fc_index']]; 
$section_heading = $fc_row['section_heading'];
$section_background_color = $fc_row['section_background_color'];
$section_text_color = $fc_row['section_text_color'];
$vimeo_or_youtube = $fc_row['vimeo_or_youtube'];
$vimeo_id = $fc_row['vimeo_id'];
$youtube_id = $fc_row['youtube_id'];
$video_title = $fc_row['video_title'];
?>
<section class="block flexible-content fc fc-video <?php echo $section_background_color; ?>">
	<div class="container-fc">
		<?php if( $section_heading ): ?>
			<div class="row fc-section-heading fc-row-primary">
				<div class="col-sm-12 fc-col-primary">
					<h2 class="serif fc-section-heading-text <?php echo $section_text_color; ?>">
						<?php echo $section_heading; ?>
					</h2>
				</div>
			</div>
		<?php endif; ?>
		<?php if( $video_title ): ?>
			<div class="fc-video-title">
				<h4 class="bold <?php echo $section_text_color; ?>">
					<?php echo $video_title; ?>
				</h4>
			</div>
		<?php endif; ?>
		<div class="fc-video-container">
			<?php if( $vimeo_or_youtube === 'vimeo' ): ?>
				<iframe src="https://player.vimeo.com/video/<?php echo $vimeo_id; ?>" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
			<?php elseif( $vimeo_or_youtube === 'youtube' ): ?>
				<iframe src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>" frameborder="0" allowfullscreen></iframe>
			<?php endif; ?>
		</div>
	</div>
</section>