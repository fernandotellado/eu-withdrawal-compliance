<?php
/**
 * AyudaWP EU Withdrawal Promotional Banner.
 *
 * Renders the AyudaWP rotating promo box on plugin admin screens.
 * Class name is unique to avoid collisions with other AyudaWP plugins.
 *
 * @package AyudaWP_EU_Withdrawal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EU Withdrawal Promo Banner class.
 */
class Aeuw_Promo_Banner {

	/**
	 * Current plugin slug to exclude from recommendations.
	 *
	 * @var string
	 */
	private $current_plugin_slug;

	/**
	 * CSS class prefix used in markup and styles.
	 *
	 * @var string
	 */
	private $css_prefix;

	/**
	 * Constructor.
	 *
	 * @param string $current_plugin_slug Current plugin slug (excluded from recommendations).
	 * @param string $css_prefix          CSS class prefix.
	 */
	public function __construct( $current_plugin_slug, $css_prefix ) {
		$this->current_plugin_slug = $current_plugin_slug;
		$this->css_prefix          = $css_prefix;
	}

	/**
	 * Get AyudaWP plugins catalog.
	 *
	 * @return array
	 */
	private function get_plugins_catalog() {
		return array(
			'vigilante'                                                => array(
				'icon'        => 'dashicons-shield',
				'title'       => __( 'Complete WordPress security', 'eu-withdrawal-compliance' ),
				'description' => __( 'All-in-one security plugin: firewall, login protection, security headers, 2FA, file integrity monitoring, and activity logging.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Vigilant', 'eu-withdrawal-compliance' ),
			),
			'gozer'                                                    => array(
				'icon'        => 'dashicons-admin-network',
				'title'       => __( 'Restrict site access', 'eu-withdrawal-compliance' ),
				'description' => __( 'Force visitors to log in before accessing your site with extensive exception controls for pages, posts, and user roles.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Gozer', 'eu-withdrawal-compliance' ),
			),
			'vigia'                                                    => array(
				'icon'        => 'dashicons-visibility',
				'title'       => __( 'Monitor AI crawler activity', 'eu-withdrawal-compliance' ),
				'description' => __( 'Track which AI bots visit your site, analyze their behavior, and take control with blocking rules and robots.txt management.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install VigIA', 'eu-withdrawal-compliance' ),
			),
			'ai-share-summarize'                                       => array(
				'icon'        => 'dashicons-share',
				'title'       => __( 'Boost your AI presence', 'eu-withdrawal-compliance' ),
				'description' => __( 'Add social sharing and AI summarize buttons. Help visitors share your content and let AIs learn from your site while getting backlinks.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install AI Share & Summarize', 'eu-withdrawal-compliance' ),
			),
			'ai-content-signals'                                       => array(
				'icon'        => 'dashicons-flag',
				'title'       => __( 'Control AI content usage', 'eu-withdrawal-compliance' ),
				'description' => __( 'Cloudflare-endorsed plugin to define how AI systems can use your content: for training, search results, or both.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install AI Content Signals', 'eu-withdrawal-compliance' ),
			),
			'wpo-tweaks'                                               => array(
				'icon'        => 'dashicons-performance',
				'title'       => __( 'Speed up your WordPress', 'eu-withdrawal-compliance' ),
				'description' => __( 'Comprehensive performance optimizations: critical CSS, lazy loading, cache rules, and 30+ tweaks with zero configuration.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Zero Config Performance', 'eu-withdrawal-compliance' ),
			),
			'no-gutenberg'                                             => array(
				'icon'        => 'dashicons-edit-page',
				'title'       => __( 'Back to Classic Editor', 'eu-withdrawal-compliance' ),
				'description' => __( 'Completely remove Gutenberg, FSE styles, and block widgets. Restore the classic editing experience with better performance.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install No Gutenberg', 'eu-withdrawal-compliance' ),
			),
			'anticache'                                                => array(
				'icon'        => 'dashicons-hammer',
				'title'       => __( 'Development toolkit', 'eu-withdrawal-compliance' ),
				'description' => __( 'Bypass all caching during development. Auto-detects cache plugins, enables debug mode, and includes maintenance screen.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Anti-Cache Kit', 'eu-withdrawal-compliance' ),
			),
			'auto-capitalize-names-ayudawp'                            => array(
				'icon'        => 'dashicons-editor-textcolor',
				'title'       => __( 'Fix customer names', 'eu-withdrawal-compliance' ),
				'description' => __( 'Auto-capitalize names and addresses in WordPress and WooCommerce. Keep invoices and reports professionally formatted.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Auto Capitalize', 'eu-withdrawal-compliance' ),
			),
			'easy-actions-scheduler-cleaner-ayudawp'                   => array(
				'icon'        => 'dashicons-database-remove',
				'title'       => __( 'Clean Action Scheduler', 'eu-withdrawal-compliance' ),
				'description' => __( 'Remove millions of completed, failed, and old actions from WooCommerce Action Scheduler. Reduce database size instantly.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Scheduler Cleaner', 'eu-withdrawal-compliance' ),
			),
			'native-sitemap-customizer'                                => array(
				'icon'        => 'dashicons-networking',
				'title'       => __( 'Customize your sitemap', 'eu-withdrawal-compliance' ),
				'description' => __( 'Control WordPress native sitemap: exclude post types, taxonomies, specific posts, and authors. No bloat, just options.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Sitemap Customizer', 'eu-withdrawal-compliance' ),
			),
			'post-visibility-control'                                  => array(
				'icon'        => 'dashicons-hidden',
				'title'       => __( 'Control post visibility', 'eu-withdrawal-compliance' ),
				'description' => __( 'Hide posts from homepage, archives, feeds, or REST API while keeping them accessible via direct URL.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Post Visibility', 'eu-withdrawal-compliance' ),
			),
			'widget-visibility-control'                                => array(
				'icon'        => 'dashicons-welcome-widgets-menus',
				'title'       => __( 'Smart widget display', 'eu-withdrawal-compliance' ),
				'description' => __( 'Show or hide widgets based on pages, post types, categories, user roles, and more. Works with any theme.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Widget Visibility', 'eu-withdrawal-compliance' ),
			),
			'search-replace-text-blocks'                               => array(
				'icon'        => 'dashicons-search',
				'title'       => __( 'Search & replace in blocks', 'eu-withdrawal-compliance' ),
				'description' => __( 'Find and replace text across all your Gutenberg blocks. Bulk edit content without touching the database directly.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Search Replace Blocks', 'eu-withdrawal-compliance' ),
			),
			'seo-read-more-buttons-ayudawp'                            => array(
				'icon'        => 'dashicons-admin-links',
				'title'       => __( 'Better read more links', 'eu-withdrawal-compliance' ),
				'description' => __( 'Customize excerpt "read more" links with buttons, custom text, and nofollow option. Improve CTR and SEO.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install SEO Read More', 'eu-withdrawal-compliance' ),
			),
			'show-only-lowest-prices-in-woocommerce-variable-products' => array(
				'icon'        => 'dashicons-tag',
				'title'       => __( 'Cleaner variable prices', 'eu-withdrawal-compliance' ),
				'description' => __( 'Display only the lowest price for WooCommerce variable products instead of confusing price ranges.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Lowest Price', 'eu-withdrawal-compliance' ),
			),
			'multiple-sale-prices-scheduler'                           => array(
				'icon'        => 'dashicons-calendar-alt',
				'title'       => __( 'Schedule sale prices', 'eu-withdrawal-compliance' ),
				'description' => __( 'Set multiple future sale prices for WooCommerce products. Plan promotions in advance with start and end dates.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Sale Scheduler', 'eu-withdrawal-compliance' ),
			),
			'easy-store-management-ayudawp'                            => array(
				'icon'        => 'dashicons-store',
				'title'       => __( 'Simplify store management', 'eu-withdrawal-compliance' ),
				'description' => __( 'Clean up WordPress admin for Store Managers. Hide unnecessary menus, keep only orders, products, and customers, plus quick access shortcuts.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Easy Store', 'eu-withdrawal-compliance' ),
			),
			'lightbox-images-for-divi'                                 => array(
				'icon'        => 'dashicons-format-gallery',
				'title'       => __( 'Lightbox for Divi', 'eu-withdrawal-compliance' ),
				'description' => __( 'Add native lightbox functionality to Divi theme images. No jQuery, fast loading, fully customizable.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Divi Lightbox', 'eu-withdrawal-compliance' ),
			),
			'scheduled-posts-showcase'                                 => array(
				'icon'        => 'dashicons-clock',
				'title'       => __( 'Show visitors what is coming up next', 'eu-withdrawal-compliance' ),
				'description' => __( 'Display your scheduled and future posts on the frontend to gain and retain visits.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Scheduled Posts Showcase', 'eu-withdrawal-compliance' ),
			),
			'periscopio'                                               => array(
				'icon'        => 'dashicons-rss',
				'title'       => __( 'Custom Dashboard News', 'eu-withdrawal-compliance' ),
				'description' => __( 'Add your own custom feeds and links to the news and events dashboard widget and replace WordPress default one.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install Periscope', 'eu-withdrawal-compliance' ),
			),
			'noindexer'                                                => array(
				'icon'        => 'dashicons-editor-unlink',
				'title'       => __( 'Control search indexing', 'eu-withdrawal-compliance' ),
				'description' => __( 'Tell search engines what not to index. Apply noindex per post, page, or entire post types with simple override controls.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install NoIndexer', 'eu-withdrawal-compliance' ),
			),
			'core-diet'                                                => array(
				'icon'        => 'dashicons-food',
				'title'       => __( 'Reduce WordPress Fat', 'eu-withdrawal-compliance' ),
				'description' => __( 'Put your WordPress on a diet. Disable unnecessary default features to improve performance and reduce bloat.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Install DietPress', 'eu-withdrawal-compliance' ),
			),
		);
	}

	/**
	 * Get AyudaWP services catalog.
	 *
	 * @return array
	 */
	private function get_services_catalog() {
		return array(
			'maintenance' => array(
				'icon'        => 'dashicons-admin-tools',
				'title'       => __( 'Need help with your website?', 'eu-withdrawal-compliance' ),
				'description' => __( 'Professional WordPress maintenance: security monitoring, regular backups, performance optimization, and priority support.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Learn more', 'eu-withdrawal-compliance' ),
				'url'         => 'https://mantenimiento.ayudawp.com',
			),
			'consultancy' => array(
				'icon'        => 'dashicons-businessman',
				'title'       => __( 'WordPress consultancy', 'eu-withdrawal-compliance' ),
				'description' => __( 'One-on-one online sessions to solve your WordPress doubts, get expert advice, and make better decisions for your project.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Book a session', 'eu-withdrawal-compliance' ),
				'url'         => 'https://servicios.ayudawp.com/producto/consultoria-online-wordpress/',
			),
			'hacked'      => array(
				'icon'        => 'dashicons-sos',
				'title'       => __( 'Hacked website?', 'eu-withdrawal-compliance' ),
				'description' => __( 'Fast recovery service for compromised WordPress sites. We clean malware, fix vulnerabilities, and restore your site security.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Get help now', 'eu-withdrawal-compliance' ),
				'url'         => 'https://servicios.ayudawp.com/producto/wordpress-hackeado/',
			),
			'development' => array(
				'icon'        => 'dashicons-editor-code',
				'title'       => __( 'Custom development', 'eu-withdrawal-compliance' ),
				'description' => __( 'Need a custom plugin, theme modifications, or specific functionality? We build tailored WordPress solutions for your needs.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Request a quote', 'eu-withdrawal-compliance' ),
				'url'         => 'https://servicios.ayudawp.com/producto/desarrollo-wordpress/',
			),
			'hosting'     => array(
				'icon'        => 'dashicons-cloud-saved',
				'title'       => __( 'Hosting built for WordPress', 'eu-withdrawal-compliance' ),
				'description' => __( 'Google Cloud servers, automatic geo-located daily backups, and 24/7 expert support. Speed, security, and migration tools included.', 'eu-withdrawal-compliance' ),
				'button'      => __( 'Learn more', 'eu-withdrawal-compliance' ),
				/* translators: SiteGround affiliate URL. Change this URL in translations to use a localized landing page. */
				'url'         => __( 'https://stgrnd.co/telladowpbox', 'eu-withdrawal-compliance' ),
			),
		);
	}

	/**
	 * Get random plugins excluding the current one.
	 *
	 * @param int $count Number of plugins to return.
	 * @return array
	 */
	private function get_random_plugins( $count = 2 ) {
		$plugins = $this->get_plugins_catalog();

		// Remove current plugin from the list (no self-recommendation).
		unset( $plugins[ $this->current_plugin_slug ] );

		$random_keys = array_rand( $plugins, min( $count, count( $plugins ) ) );

		if ( ! is_array( $random_keys ) ) {
			$random_keys = array( $random_keys );
		}

		$result = array();
		foreach ( $random_keys as $key ) {
			$result[ $key ] = $plugins[ $key ];
		}

		return $result;
	}

	/**
	 * Get a random service.
	 *
	 * @return array
	 */
	private function get_random_service() {
		$services   = $this->get_services_catalog();
		$random_key = array_rand( $services );

		return $services[ $random_key ];
	}

	/**
	 * Render the promotional banner.
	 *
	 * @param string $layout Layout: 'horizontal' (3 columns) or 'vertical' (sidebar widgets).
	 */
	public function render( $layout = 'horizontal' ) {
		if ( 'vertical' === $layout ) {
			$this->render_vertical();
			return;
		}

		$this->render_horizontal();
	}

	/**
	 * Render the horizontal layout (3-column grid).
	 */
	private function render_horizontal() {
		$plugins = $this->get_random_plugins( 2 );
		$service = $this->get_random_service();
		$prefix  = $this->css_prefix;
		?>
		<div class="<?php echo esc_attr( $prefix ); ?>-promo-notice">
			<h4><?php esc_html_e( 'Starter kit for your site', 'eu-withdrawal-compliance' ); ?></h4>
			<div class="<?php echo esc_attr( $prefix ); ?>-promo-columns">

				<?php foreach ( $plugins as $slug => $plugin ) : ?>
					<div class="<?php echo esc_attr( $prefix ); ?>-promo-column">
						<span class="dashicons <?php echo esc_attr( $plugin['icon'] ); ?>"></span>
						<h5><?php echo esc_html( $plugin['title'] ); ?></h5>
						<p><?php echo esc_html( $plugin['description'] ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $slug . '&TB_iframe=true&width=772&height=618' ) ); ?>" class="button thickbox">
							<?php echo esc_html( $plugin['button'] ); ?>
						</a>
					</div>
				<?php endforeach; ?>

				<div class="<?php echo esc_attr( $prefix ); ?>-promo-column">
					<span class="dashicons <?php echo esc_attr( $service['icon'] ); ?>"></span>
					<h5><?php echo esc_html( $service['title'] ); ?></h5>
					<p><?php echo esc_html( $service['description'] ); ?></p>
					<a href="<?php echo esc_url( $service['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary">
						<?php echo esc_html( $service['button'] ); ?>
					</a>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Render the vertical layout (stacked sidebar widgets).
	 */
	private function render_vertical() {
		$plugins = $this->get_random_plugins( 2 );
		$service = $this->get_random_service();
		$prefix  = $this->css_prefix;

		foreach ( $plugins as $slug => $plugin ) :
			?>
			<div class="<?php echo esc_attr( $prefix ); ?>-sidebar-widget <?php echo esc_attr( $prefix ); ?>-promo-widget">
				<span class="dashicons <?php echo esc_attr( $plugin['icon'] ); ?>"></span>
				<h3><?php echo esc_html( $plugin['title'] ); ?></h3>
				<p><?php echo esc_html( $plugin['description'] ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $slug . '&TB_iframe=true&width=772&height=618' ) ); ?>" class="button thickbox">
					<?php echo esc_html( $plugin['button'] ); ?>
				</a>
			</div>
			<?php
		endforeach;
		?>

		<div class="<?php echo esc_attr( $prefix ); ?>-sidebar-widget <?php echo esc_attr( $prefix ); ?>-promo-widget">
			<span class="dashicons <?php echo esc_attr( $service['icon'] ); ?>"></span>
			<h3><?php echo esc_html( $service['title'] ); ?></h3>
			<p><?php echo esc_html( $service['description'] ); ?></p>
			<a href="<?php echo esc_url( $service['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary">
				<?php echo esc_html( $service['button'] ); ?>
			</a>
		</div>
		<?php
	}
}
