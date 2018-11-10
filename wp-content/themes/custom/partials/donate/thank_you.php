
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
	<!--<div class="block-background page-hero-image mask-dark" style="background-image: url('<?php echo $hero_image; ?>');">
	</div> -->
	<div class="home-intro-top thank-you-intro-top">
			<video muted autoplay playsinline loop class="" id="home-intro-video">
				<source src="<?php the_field('intro_video',6); ?>" type="video/mp4">
			</video>
		</div>
		<div class="thank-you-text flex-center">
			<div class="thank-you-text-inner">
				<h1 class="white">
					<?php the_field('thank_you_heading'); ?>
				</h1>
				<div class="thank-you-share-button-container flex-center">
					<a class="thank-you-share-button" href="http://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Ftheavenueconcept.org%2Fdonate&title=I Just Donated To The Avenue Concept" onclick="javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=630,width=630');return false;">
						Tell Your Friends
					</a>
				</div>
			<!--<div class="fb-share-button" data-href="https://theavenueconcept.org/donate" data-layout="button_count">			
			</div> -->
		</div>
	</div>
</section>
