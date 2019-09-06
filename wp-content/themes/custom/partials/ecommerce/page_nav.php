<div class="page-nav present before page-nav-tacwc" id="page-nav">
	<div class="container-fluid container-fluid-stretch">
		<div class="row">
			<div class="col">
				<?php 
				$user = wp_get_current_user();
				$name = $user->user_firstname;
				if( $name ):
					$page_title = 'Welcome Back, ' . $name;
				else:
					$page_title = 'Welcome Back';
				endif;
				?>
				<h3 class="page-nav-title"><?php echo $page_title; ?></h3>
				<ul class="page-nav-list">
					<li>
						<a href="/my-account/">My Account</a>
					</li>
					<li>
						<a href="/my-account/orders/">Orders</a>
					</li>
					<li>
						<a href="/my-account/subscriptions/">Memberships</a>
					</li>
					<li>
						<a href="/my-account/edit-address/">Addresses</a>
					</li>
					<li>
						<a href="/my-account/payment-methods/">Payment Methods</a>
					</li>
					<li>
						<a href="/my-account/edit-account/">Account Details</a>
					</li>
					<li>
						<a href="<?php echo wp_logout_url('/my-account') ?>" class="page-nav-link">
							Logout
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
