<?php
$fc = get_field('page_flexible_content');
$fc_row = $fc[$GLOBALS['fc_index']]; 
$section_heading = $fc_row['section_heading'];
$section_background_color = $fc_row['section_background_color'];
$section_text_color = $fc_row['section_text_color'];
$list_items = $fc_row['list_items'];
?>
<section class="block flexible-content fc fc-labelled-list <?php echo $section_background_color; ?>">
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
		<?php if( $list_items ): ?>
			<div class="row fc-row-primary">
				<div class="col-xl-8 col-lg-9 col-md-10 fc-col-primary">
					<?php foreach ($list_items as $list_item): ?> 
						<div class="row fc-labelled-list-item">
							<div class="fc-labelled-list-label cl-md-4 col-lg-3">
								<?php if( $list_item['list_item_label'] ): ?>
									<h4 class="<?php echo $section_text_color; ?>">
										<?php echo $list_item['list_item_label']; ?>
									</h4>
								<?php endif; ?>
							</div>
							<div class="fc-labelled-list-body cl-md-8 col-lg-9">
								<?php if( $list_item['list_item_body'] ): ?>
									<div class="wysiwyg <?php echo $section_text_color; ?>">
										<?php echo $list_item['list_item_body']; ?>
									</div>
								<?php endif; ?>
								<?php if( $list_item['link_url'] && $list_item['link_text']): ?>
									<div class="fc-labelled-list-link fc-button">
										<a href="<?php echo $list_item['link_url']; ?>" class="<?php echo $section_text_color; ?>">
											<?php echo $list_item['link_text']; ?>
										</a>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>		
		<?php endif; ?>
	</div>
</section>