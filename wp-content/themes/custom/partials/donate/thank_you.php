
	<!-- Load Facebook SDK for JavaScript -->
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.0';
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>

<?php 
$hero_image = get_field('hero_image');
$hero_image = $hero_image['sizes']['page_hero'];
?>
<section class="block page-hero thank-you-hero" id="page-hero">
	<div class="block-background page-hero-image mask-dark" style="background-image: url('<?php echo $hero_image; ?>');">
	</div>
	<div class="thank-you-text flex-center">
		<div>
			<h1 class="white">
				<?php the_field('thank_you_heading'); ?>
			</h1>
			<a class="thank-you-share-button" href="http://www.facebook.com/sharer/sharer.php?u=[https://theavenueconcept.org/donate]&title=[I Just Donated To The Avenue Concept]" onclick="javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;">Share</a>
<!-- 			<div class="fb-share-button" data-href="https://theavenueconcept.org/donate" data-layout="button_count">
				
			</div> -->
		</div>
	</div>
</section>
