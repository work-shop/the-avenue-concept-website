<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<?php wc_print_notices(); ?>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<?php if( $_GET['renew'] ): 
	$renewing = true;
else: 
	$renewing = false;
endif;
?>

<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

	<div class="u-columns col2-set row justify-content-center" id="customer_login">
		<div class="col-md-5 col-lg-4 login-register-help mb2">
			<?php if( $renewing ): ?>
				<h3 class="bold">
					To renew your membership,
					<br> 
					please read the instructions below: 
				</h3>
				<h4 class="bold error">
					As a service to our members who had email addresses on file with us, we’ve already created an account for you, which is linked to your email. 
					<br>
					<br>
					You will need to activate your account, if you have not done so already. To activate your account, <a href="/my-account/lost-password" class="underline">reset your password</a> using the email you provided us with your membership.
					<br>
					<br>
					Your new account login will replace all previous Museum logins.
					<br>
					<br>
					If we created an account for you, we sent you an email about the account, to the email address we had on file.
				</h4>
				<div class="button mt1">
					<a href="/member-account-information" target="_blank" class="white">More Info About Member Accounts</a>
				</div>
				<?php else: ?>
					<h3 class="bold">
						Please log in, <br>
						or create a new account.
					</h3>
					<h4>
						<div class="button mt1">
							<a href="/contact" target="_blank" class="white">Questions? Contact Us.</a>
						</div>
					</h4>
				<?php endif; ?>
			</div>
			<div class="u-column1 col-12 col-sm-10 col-md-5 col-lg-4">
			<?php endif; ?>
			<!--login form-->
			<div class="nam-login-form">
				<form class="woocommerce-form woocommerce-form-login login" method="post">
					<div class="form-top">
						<h4 class="bold">Log in</h4>
					</div>
					<div class="form-body">
						<?php do_action( 'woocommerce_login_form_start' ); ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
						</p>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
							<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" />
						</p>
						<?php do_action( 'woocommerce_login_form' ); ?>

						<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
						<button type="submit" class="woocommerce-Button button" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>
					</div>

					<div class="form-bottom">
						<label class="woocommerce-form__label woocommerce-form__label-for-checkbox inline">
							<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
						</label>
						<p class="woocommerce-LostPassword lost_password">
							<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
						</p>
						<?php do_action( 'woocommerce_login_form_end' ); ?>
					</div>
				</form>
			</div>

			<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

			</div>

			<?php if ( $renewing === false ): ?>
				<!--registration form-->
				<div class="u-column2 col-12 col-sm-10 offset-sm-1 offset-md-0 col-md-5 col-lg-4">
					<div class="nam-registration-form">

						<form method="post" class="woocommerce-form woocommerce-form-register register">
							<div class="form-top">
								<h4 class="bold">Create a New Account</h4>
							</div>

							<div class="form-body">
								<?php do_action( 'woocommerce_register_form_start' ); ?>

								<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

									<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
										<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
										<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
									</p>

								<?php endif; ?>

								<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
									<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
									<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
								</p>

								<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

									<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
										<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
										<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
									</p>

								<?php endif; ?>

								<?php do_action( 'woocommerce_register_form' ); ?>

								<p class="woocommerce-FormRow form-row">
									<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
									<button type="submit" class="woocommerce-Button button" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
								</p>

								<?php do_action( 'woocommerce_register_form_end' ); ?>

							</div>
						</form>
					</div>
				</div>
			<?php endif; ?>

		</div><!--/row-->
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
