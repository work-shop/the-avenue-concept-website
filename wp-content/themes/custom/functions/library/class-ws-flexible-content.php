<?php

class WS_Flexible_Content_Helper{

	public static function fc_background_text( $background_type, $heading, $heading_font, $text_alignment, $text_color, $subheading, $subheading_font, $link_url, $link_text ){ ?>
		<?php if( $heading ): ?>
			<div class="fc-background-heading <?php if( $background_type !== 'multi-column' ): echo $text_alignment; endif; ?>">
				<h2 class="<?php if($heading_font): echo $heading_font; else: echo 'serif'; endif; ?> <?php echo $text_color . ' '; if( $background_type !== 'multi-column' ): echo $text_alignment; endif; ?>"><?php echo $heading; ?></h2>
			</div>
		<?php endif; ?>
		<?php if( $heading ): ?>
			<div class="fc-background-subheading <?php if( $background_type !== 'multi-column' ): echo $text_alignment; endif; ?>">
				<h3 class="<?php if($heading_font): echo $heading_font; else: echo 'serif'; endif; ?> <?php echo $text_color . ' '; if( $background_type !== 'multi-column' ): echo $text_alignment; endif; ?>"><?php echo $subheading; ?></h3>
			</div>
		<?php endif; ?>
		<?php if( $link_url ): ?>
			<div class="fc-background-link fc-button <?php if( $background_type !== 'multi-column' ): echo $text_alignment; endif; ?>">
				<a href="<?php echo $link_url; ?>" class="<?php echo $text_color; ?>"><?php echo $link_text; ?></a>
			</div>
		<?php endif; ?>
	<?php } 

	public static function fc_background_image( $background_type, $background_image, $section_height_image, $background_image_masking ){ ?>
		<?php if( ($background_image && $section_height_image !== 'natural-image') || $background_type === 'multi-column' ): ?>
			<div class="block-background <?php if( $background_type !== 'multi-column' ): echo $background_image_masking; endif; ?>" style="background-image: url('<?php echo $background_image; ?>');">
			</div>
		<?php elseif( $background_type === 'image' && $background_image && $section_height_image === 'natural-image' ): ?>
			<div class="fc-background-natural-image-container <?php echo $background_image_masking; ?>">
				<img src="<?php echo $background_image; ?>">
			</div>
		<?php endif; ?>		
	<?php }

}
?>