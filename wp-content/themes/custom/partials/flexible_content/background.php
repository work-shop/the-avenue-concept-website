<?php
$fc = get_field('page_flexible_content');
$fc_row = $fc[$GLOBALS['fc_index']]; 
$section_heading = $fc_row['section_heading'];
$background_type = $fc_row['background_type'];
$background_image = $fc_row['background_image'];
$background_image = $background_image['sizes']['page_hero'];
$background_image_masking = $fc_row['background_image_masking'];
$background_image_masking .= ' mask ';
$background_color = $fc_row['background_color'];
$section_height = $fc_row['section_height'];
$section_height_image = $fc_row['section_height_image'];
$multi_column_layout = $fc_row['multi_column_layout'];
$image_width = $fc_row['image_width'];
$include_text= $fc_row['include_text'];
$text_color = $fc_row['text_color'];
$text_alignment = $fc_row['text_alignment'];
$heading = $fc_row['heading'];
$heading_font = $fc_row['heading_font'];
$subheading = $fc_row['subheading'];
$subheading_font = $fc_row['subheading_font'];
$link_text = $fc_row['link_text'];
$link_url = $fc_row['link_url'];
$fc_background_classes = 'fc-background-' . $background_type . ' ';
?>

<?php if( $background_type === 'image' ): 
$fc_background_classes .= 'fc-background-image-height-' . $section_height_image . ' ';
endif; ?>
<?php if( $background_type === 'color' ): 
$fc_background_classes .= 'fc-background-height-' . $section_height . ' ';
$fc_background_classes .=  $background_color . ' ';
endif; ?>

<section class="block flexible-content fc fc-background <?php echo $fc_background_classes; ?>">
	<?php if( $background_type === 'image' ): ?>
		<?php WS_Flexible_Content_Helper::fc_background_image( $background_type, $background_image, $section_height_image, $background_image_masking ); ?>
		<?php if( $include_text ): ?>
			<div class="container-fc fc-background-text">
				<div class="row">
					<div class="col-sm-12">
						<?php WS_Flexible_Content_Helper::fc_background_text( $background_type, $heading, $heading_font, $text_alignment, $text_color, $subheading, $subheading_font, $link_url, $link_text); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

	<?php elseif( $background_type === 'multi-column'): ?>
		<div class="fc-background-multi-column-container <?php echo $multi_column_layout; ?> <?php echo $image_width; ?>">
			<div class="fc-background-multi-column-1 <?php if( $multi_column_layout === 'image-right' ): echo $background_color; ?> <?php endif; ?>">
				<div class="fc-background-multi-column-1-inner">
					<?php if( $multi_column_layout === 'image-left' ): ?>
						<?php WS_Flexible_Content_Helper::fc_background_image( $background_type, $background_image, $section_height_image, $background_image_masking ); ?>
					<?php else: ?>
						<?php WS_Flexible_Content_Helper::fc_background_text( $background_type, $heading, $heading_font, $text_alignment, $text_color, $subheading, $subheading_font, $link_url, $link_text); ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="fc-background-multi-column-2 <?php if( $multi_column_layout === 'image-left' ): echo $background_color; ?> <?php endif; ?>">
				<div class="fc-background-multi-column-2-inner">
					<?php if( $multi_column_layout === 'image-right' ): ?>
						<?php WS_Flexible_Content_Helper::fc_background_image( $background_type, $background_image, $section_height_image, $background_image_masking ); ?>
					<?php else: ?>
						<?php WS_Flexible_Content_Helper::fc_background_text( $background_type, $heading, $heading_font, $text_alignment, $text_color, $subheading, $subheading_font, $link_url, $link_text); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>

	<?php elseif( $background_type === 'color'): ?>
		<?php if( $include_text ): ?>
			<div class="container-fc fc-background-text">
				<div class="row">
					<div class="col-sm-12">
						<?php WS_Flexible_Content_Helper::fc_background_text( $background_type, $heading, $heading_font, $text_alignment, $text_color, $subheading, $subheading_font, $link_url, $link_text); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		
	<?php endif; ?>
</section>

