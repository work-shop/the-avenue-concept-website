<section class="block padded bg-light" id="sorry">
	<div class="container-fluid container-fluid-stretch">
		<div class="row">
			<div class="col-xl-8 mt6 mb6">
				<h2 class="error">
					Sorry, something went wrong with the transaction.</h2>
				<p class="error mb2">
					<?php echo $_GET['response']; ?>
				</p>
				<p>
					Please <a href="/donate" class="underline">try again</a>, or if you have issues, <a href="/contact" class="underline">contact us.</a>
				</p>
			</div>
		</div>
	</div>
</section>