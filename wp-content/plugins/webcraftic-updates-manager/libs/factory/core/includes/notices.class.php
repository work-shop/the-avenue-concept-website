<?php
	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( !class_exists('Wbcr_Factory400_RegisterNotice') ) {
		/**
		 * Регистрируем уведомление для администратора
		 * После инициализации плагина, уведомление появится в верхней части панели администратора
		 */
		class Wbcr_Factory400_RegisterNotice {

			protected $message;
			protected $type;
			protected $where;
			protected $dismiss_time;

			/**
			 * @param Wbcr_Factory400_Plugin $plugin
			 * @param string $message - текст уведомления.
			 * @param string $type - тип уведомления (ошибка, обновление, предупреждение)
			 * @param array $where - на каких страницах показывать уведомление
			 * @param string $dismiss_time - на какой период отключать уведомление
			 */
			public function __construct(Wbcr_Factory400_Plugin $plugin, $message, $type, array $where = array(), $dismiss_time = YEAR_IN_SECONDS)
			{
				if( empty($message) ) {
					throw new Exception('Message can not be empty');
				}

				$this->message = $message;
				$this->type = $type;
				$this->where = $where;
				$this->dismiss_time = $dismiss_time;

				// Check AJAX submit
				if( defined('DOING_AJAX') && DOING_AJAX ) {
					add_action('wp_ajax_' . $this->prefix . '_dismiss_suggestions', array(
						&$this,
						'dismiss_suggestions'
					));
					// Admin area (except install or activate plugins page)
				} elseif( !in_array(basename($_SERVER['PHP_SELF']), array(
					'plugins.php',
					'plugin-install.php',
					'update.php'
				))
				) {
					add_action('wp_loaded', array(&$this, 'load_notices_suggestions'), PHP_INT_MAX);
				}
			}

			/**
			 * Determines the admin notices display
			 */
			private function disableNagNotices()
			{
				return (defined('DISABLE_NAG_NOTICES') && DISABLE_NAG_NOTICES);
			}

			/**
			 * Check the suggestions dismissed timestamp
			 */
			private function check_suggestions()
			{

				// Compare timestamp
				$timestamp = $this->get_dismissed_timestamp('suggestions');
				if( empty($timestamp) || (time() - $timestamp) > ($this->days_dismissing_suggestions * 86400) ) {

					// Check AJAX submit
					if( defined('DOING_AJAX') && DOING_AJAX ) {
						add_action('wp_ajax_' . $this->prefix . '_dismiss_suggestions', array(
							&$this,
							'dismiss_suggestions'
						));
						// Admin area (except install or activate plugins page)
					} elseif( !in_array(basename($_SERVER['PHP_SELF']), array(
						'plugins.php',
						'plugin-install.php',
						'update.php'
					))
					) {
						add_action('wp_loaded', array(&$this, 'load_notices_suggestions'), PHP_INT_MAX);
					}
				}
			}

			// AJAX Handlers
			// ---------------------------------------------------------------------------------------------------

			/**
			 * Dismiss suggestions
			 */
			public function dismiss_suggestions()
			{
				if( !empty($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], $this->prefix . '-dismiss-suggestions') ) {
					$this->update_dismissed_timestamp('suggestions');
				}
			}

			// Activation timestamp management
			// ---------------------------------------------------------------------------------------------------

			/**
			 * Retrieves the plugin activation timestamp
			 */
			private function get_activation_timestamp()
			{
				return (int)get_option($this->prefix . '_activated_on');
			}

			/**
			 * Updates activation timestamp
			 */
			private function update_activation_timestamp()
			{
				update_option($this->prefix . '_activated_on', time(), true);
			}

			/**
			 * Removes activation timestamp
			 */
			private function delete_activation_timestamp()
			{
				delete_option($this->prefix . '_activated_on');
			}

			// Dismissed timestamp management
			// ---------------------------------------------------------------------------------------------------

			/**
			 * Current timestamp by key
			 */
			private function get_dismissed_timestamp($key)
			{
				return (int)get_option($this->prefix . '_dismissed_' . $key . '_on');
			}

			/**
			 * Update with the current timestamp
			 */
			private function update_dismissed_timestamp($key)
			{
				update_option($this->prefix . '_dismissed_' . $key . '_on', time(), true);
			}

			/**
			 * Removes dismissied option
			 */
			private function delete_dismissed_timestamp($key)
			{
				delete_option($this->prefix . '_dismissed_' . $key . '_on');
			}


			public function admin_notices_suggestions()
			{
				$plugin_data = get_plugin_data($this->plugin_file);

				?>
				<div class="<?php echo esc_attr($this->prefix); ?>-dismiss-suggestions notice notice-success is-dismissible" data-nonce="<?php echo esc_attr(wp_create_nonce($this->prefix . '-dismiss-suggestions')); ?>">
					<p><?php echo str_replace('%plugin%', $plugin_data['Name'], $this->suggestions_message); ?></p>
					<ul><?php foreach($this->missing as $plugin) : ?>

							<li><strong><?php echo $this->suggestions[$plugin]['name']; ?></strong>
								<a href="<?php echo esc_url($this->get_install_url($plugin)); ?>">
									<?php if( in_array(get_locale(), array(
										'ru_RU',
										'bel',
										'kk',
										'uk',
										'bg',
										'bg_BG',
										'ka_GE'
									)) ): ?>
										(Установить бесплатно)
									<?php else: ?>
										(Install for free)
									<?php endif ?>
								</a><br/><?php echo $this->suggestions[$plugin]['desc']; ?></li>

						<?php endforeach; ?></ul>
				</div>
			<?php
			}

			// Javascript code
			// ---------------------------------------------------------------------------------------------------

			/**
			 * Footer script for Suggestions
			 */
			public function admin_footer_suggestions()
			{
				?>
				<script type="text/javascript">
					jQuery(function($) {

						$(document).on('click', '.<?php echo $this->prefix; ?>-dismiss-suggestions .notice-dismiss', function() {
							$.post(ajaxurl, {
								'action': '<?php echo $this->prefix; ?>_dismiss_suggestions',
								'nonce': $(this).parent().attr('data-nonce')
							});
						});

					});
				</script>
			<?php
			}
		}
	}