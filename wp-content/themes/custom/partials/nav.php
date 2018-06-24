
<nav id="nav" class="fixed">
	<div id="logo" class="logo">
		<a href="/" title="Home">
			<?php get_template_part('partials/logo'); ?>
		</a>
	</div>
	<div id="nav-menus">
		<div id="nav-menu-upper">
			<ul>
				<li><a href="">Upper Link</a></li>
			</ul>
		</div>
		<div id="nav-menu-primary">
			<ul>
				<li><a href="">Primary Link</a></li>
				<li><a href="">Primary Link</a></li>
			</ul>
		</div>
	</div>
</nav>
<nav id="mobile-nav">
	<ul class="mobile-nav-items">
		<?php wp_nav_menu(); ?>
	</ul>
</nav>
<div class="hamburger menu-toggle">
	<span class="hamburger-line hl-1"></span>
	<span class="hamburger-line hl-2"></span>
	<span class="hamburger-line hl-3"></span>
</div>

