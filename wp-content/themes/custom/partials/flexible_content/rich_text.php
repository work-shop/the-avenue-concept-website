<?php
$fc = get_field('page_flexible_content');
$fc_row = $fc[$GLOBALS['fc_index']]; 
$section_heading = $fc_row['section_heading'];
$rich_text = $fc_row['rich_text'];
?>
<section class="block flexible-content fc fc-rich-text">
	<div class="container-fc">
		<?php if( $section_heading ): ?>
			<div class="row fc-section-heading fc-row-primary">
				<div class="col-sm-12 fc-col-primary">
					<h2 class="serif fc-section-heading-text">
						<?php echo $section_heading; ?>
					</h2>
				</div>
			</div>
		<?php endif; ?>
		<div class="row fc-row-primary">
			<div class="col-xl-8 col-lg-9 col-md-10 fc-col-primary">
				<div class="rich-text fc-rich-text-container wysiwyg">
					<?php echo $rich_text; ?>
				</div>
			</div>
		</div>
	</div>
</section>