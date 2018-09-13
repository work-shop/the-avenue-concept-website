<?php
$fc = get_field('page_flexible_content');
$fc_row = $fc[$GLOBALS['fc_index']]; 
$section_heading = $fc_row['section_heading'];
$section_background_color = $fc_row['section_background_color'];
$section_text_color = $fc_row['section_text_color'];
$paragraphs = $fc_row['paragraphs'];
?>
<section class="block flexible-content fc fc-repeating-paragraphs <?php echo $section_background_color; ?>">
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
		<?php if( $paragraphs ): ?>
			<?php foreach ($paragraphs as $paragraph): ?> 
				<div class="row fc-repeating-paragraphs-row fc-row-primary">
					<div class="col-xl-8 col-lg-9 col-md-10 fc-col-primary">
						<?php if( $paragraph['heading'] ): ?>
							<h3 class="<?php echo $section_text_color; ?>">
								<?php echo $paragraph['heading']; ?>
							</h3>
						<?php endif; ?>
						<?php if( $paragraph['paragraph'] ): ?>
							<p class="<?php echo $section_text_color; ?>">
								<?php echo $paragraph['paragraph']; ?>
							</p>
						<?php endif; ?>
						<?php if( $paragraph['link_url'] && $paragraph['link_text']): ?>
							<div class="fc-repeating-paragraphs-link fc-button">
								<a href="<?php echo $paragraph['link_url']; ?>" class="<?php echo $section_text_color; ?>">
									<?php echo $paragraph['link_text']; ?>
								</a>
							</div>
						<?php endif; ?>	
						<?php if ( $element !== end($paragraphs) ): ?>
						<div class="fc-repeating-paragraphs-separator bg-<?php echo $section_text_color; ?>"></div>				
					<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</section>