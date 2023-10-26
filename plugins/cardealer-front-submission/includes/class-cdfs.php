<?php
/**
 * Installation related functions and actions.
 *
 * @author   PotenzaGlobalSolutions
 * @package  CDFS
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( CDFS_ABSPATH . 'includes/version_update/helper-classes/class-cdfs-logger.php' ); // LOGGER TRAIT.


if ( ! class_exists( 'CDFS' ) ) {
	/**
	 * CDFS Class.
	 */
	class CDFS {

		// USING LOGGER TRAIT FOR LOGGIN PROCESS.
		use CDFS_Logger;

		/**
		 * DB updates and callbacks that need to be run per version.
		 *
		 * @var array
		 */
		private static $db_updates = array(
			'1.0.0' => array(
				'CDFS_update_100_version',
				'CDFS_update_100_db_version',
			),
		);

		/**
		 * Static
		 *
		 * @var $notices .
		 */
		public static $notices = array();

		/**
		 * Static
		 *
		 * @var $_instance .
		 */
		protected static $_instance = null;

		/**
		 * Query vars to add to wp.
		 *
		 * @var $session
		 */
		public $session = null;

		/**
		 * Car Dealer Front End Submission Addon Version.
		 *
		 * @var string
		 */
		public $version = CDFS_VERSION;

		/**
		 * Background update class.
		 *
		 * @var object
		 */
		private static $background_updater;

		/**
		 * Car Dealer User Constructor.
		 */
		public function __construct() {
			/**
			 * Action called when Car Dealer - Front Submission class is loaded.
			 *
			 * @since 1.0
			 * @visible            true
			 */
			do_action( 'cdfs_loaded' );

			$this->init();
			$this->cdfs_includes();
			$this->cdfs_create_pages();
			$this->cdfs_init_hooks();
		}

		/**
		 * Main Car Dealer Front End Submission Instance.
		 *
		 * @return CDFS - Main instance.
		 */
		public static function cdfs_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Init function.
		 *
		 * @return void
		 */
		public static function init() {
			add_action( 'init', array( __CLASS__, 'cdfs_check_update' ), 5 );
			add_action( 'init', array( __CLASS__, 'cdfs_init_background_updater' ), 5 );
			add_action( 'init', array( __CLASS__, 'cdfs_install_actions' ) );
			add_action( 'init', array( __CLASS__, 'cdfs_status' ), 5 );
			add_action( 'init', array( __CLASS__, 'cdfs_notices' ), 5 );
			add_action( 'init', array( __CLASS__, 'cdfs_hide_notices' ), 5 );
			add_action( 'init', array( __CLASS__, 'cdfs_create_user_role' ), 5 );
			add_action( 'wp_dropdown_users_args', array( __CLASS__, 'filter_authors' ), 10, 2 );

			do_action( 'cdfs_init_action' );
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since 2.3
		 */
		private function cdfs_init_hooks() {
			register_activation_hook( CDFS_PLUGIN_FILE, array( __CLASS__, 'cdfs_check_update' ) );
			add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
			add_action( 'init', array( 'CDFS_Shortcodes', 'init' ) );
		}

		/**
		 * Function used to Init Template Functions
		 */
		public function include_template_functions() {
			include_once CDFS_ABSPATH . 'includes/helper/functions/function-cdfs-templates.php';
			include_once CDFS_ABSPATH . 'includes/helper/functions/function-cdfs-car-templates.php';
		}


		/**
		 * Init background updates
		 */
		public static function cdfs_init_background_updater() {
			include_once CDFS_ABSPATH . 'includes/version_update/helper-classes/class-cdfs-background-updater.php';
			self::$background_updater = new CDFS_Background_Updater();
		}

		/**
		 * Function to check update.
		 *
		 * @return void
		 */
		public static function cdfs_check_update() {
			$current_cdfs_version = get_option( 'cdfs_version', null );

			// Fresh installation.
			if ( is_null( $current_cdfs_version ) ) {
				self::cdfs_install();
			}

			// Update.
			if ( version_compare( $current_cdfs_version, max( array_keys( self::$db_updates ) ), '<' ) ) {
				array_push( self::$notices, 'cdfs_update_notice' );
			}
		}


		/**
		 * Install CDFS.
		 */
		public static function cdfs_install() {
			if ( ! is_blog_installed() ) {
				return;
			}

			// Check if we are not already running this routine.
			if ( 'yes' === get_transient( 'cdfs_installing' ) ) {
				return;
			}

			// If we made it till here nothing is running yet, lets set the transient now.
			set_transient( 'cdfs_installing', 'yes', MINUTE_IN_SECONDS * 10 );
			self::cdfs_create_tables();
			self::cdfs_create_files();
			self::cdfs_update_db_version();
			delete_transient( 'cdfs_installing' );

			do_action( 'cdfs_installed' );
		}

		/**
		 * Install actions when a update button is clicked within the admin area.
		 *
		 * This function is hooked into admin_init to affect admin only.
		 */
		public static function cdfs_install_actions() {
			if ( isset( $_GET['do_update_cdfs'] ) && ! empty( $_GET['do_update_cdfs'] ) && isset( $_GET['_cdfs_update_nonce'] ) ) {
				if ( ! wp_verify_nonce( $_GET['_cdfs_update_nonce'], 'cdfs_update_nonce' ) ) {
					wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'cdfs-addon' ) );
				}
				self::CDFS_update();
			}
		}

		/**
		 * What type of request is this?
		 *
		 * @param  string $type admin, ajax, cron or frontend.
		 * @return bool
		 */
		private function cdfs_is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'cron':
					return defined( 'DOING_CRON' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function cdfs_includes() {
			$elementor_is_edit_mode = false;
			if ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) {
				$elementor_is_edit_mode = true;
			}

			/**
			 * Class autoloader.
			 */
			include_once CDFS_ABSPATH . 'includes/class-cdfs-autoloader.php';

			/**
			 * Class Wishlist.
			 */
			include_once CDFS_ABSPATH . 'includes/class-cdfs-wishlist.php';

			/**
			 * Class Review.
			 */
			include_once CDFS_ABSPATH . 'includes/class-cdfs-review.php';

			/**
			 * Core classes.
			 */
			include_once CDFS_ABSPATH . 'includes/functions/cdfs-core-functions.php';
			include_once CDFS_ABSPATH . 'includes/functions/cdfs-cars-functions.php';
			include_once CDFS_ABSPATH . 'includes/functions/cdfs-user-functions.php';
			include_once CDFS_ABSPATH . 'includes/functions/advertise-item-functions.php';
			include_once CDFS_ABSPATH . 'includes/functions/listing-payment-functions.php';
			include_once CDFS_ABSPATH . 'includes/class-cdfs-cache-helper.php'; // Cache Helper.
			include_once CDFS_ABSPATH . 'includes/functions/cdfs-woocommerce-functions.php';
			include_once CDFS_ABSPATH . 'includes/class-cdfs-woocommerce-integration.php';
			include_once CDFS_ABSPATH . 'includes/user-meta/user-list-custom-column.php';

			add_filter( 'cdhl_vc_shortcodes', function( $shortcodes ) {
				$shortcodes['Pricing'] = CDFS_ABSPATH . 'includes/shortcodes/pricing.php';
				return $shortcodes;
			} );

			/**
			 * Class Vehicle Statistics.
			 */
			$views_enabled = cdfs_vehicle_views_enabled();
			if ( $views_enabled ) {
				include_once CDFS_ABSPATH . 'includes/class-cdfs-vehicle-statistics.php';
			}

			/**
			 * Abstract classes.
			 */
			include_once CDFS_ABSPATH . 'includes/abstracts/class-cdfs-session.php';

			if ( $this->cdfs_is_request( 'frontend' ) || $elementor_is_edit_mode ) {
				$this->cdfs_frontend_includes();
			}

			if ( $this->cdfs_is_request( 'frontend' ) ) {
				include_once CDFS_ABSPATH . 'includes/class-cdfs-session-handler.php';
			}

			// Session class, handles session data for users - can be overwritten if custom handler is needed.
			if ( $this->cdfs_is_request( 'frontend' ) ) {
				$session_class = apply_filters( 'cdfs_session_handler', 'CDFS_Session_Handler' );
				$this->session = new $session_class();
			}

			// Dashboard
			include_once trailingslashit( CDFS_ABSPATH ) . 'includes/user-dashboard/user-dashboard-functions.php';
			if ( $this->cdfs_is_request( 'frontend' ) ) {
				include_once trailingslashit( CDFS_ABSPATH ) . 'includes/user-dashboard/user-dashboard-template-hooks.php';
				include_once trailingslashit( CDFS_ABSPATH ) . 'includes/user-dashboard/user-dashboard-template-functions.php';
				include_once trailingslashit( CDFS_ABSPATH ) . 'includes/user-dashboard/class-cdfs-user-dashboard.php';
			}
			if ( $this->cdfs_is_request( 'admin' ) ) {
				include_once CDFS_ABSPATH . 'includes/class-cdfs-admin-scripts.php'; // Admin Scripts.
			}
		}

		/**
		 * Include required frontend files.
		 */
		public function cdfs_frontend_includes() {
			include_once CDFS_ABSPATH . 'includes/helper/hooks/function-cdfs-templates-hook.php'; // Template Hooks.
			include_once CDFS_ABSPATH . 'includes/class-cdfs-front-end-scripts.php'; // Frontend Scripts.
			// Form Handlers.
			include_once CDFS_ABSPATH . 'includes/class-cdfs-form-handler.php';
			include_once CDFS_ABSPATH . 'includes/class-cdfs-cars-form-handler.php';
		}

		/**
		 * Create pages that the plugin relies on, storing page IDs in variables.
		 */
		public static function cdfs_create_pages() {

			$get_new_options = get_option( 'car_dealer_options' );
			if ( get_option( 'cdfs_req_pages_created' ) == true ) { // execute actions only on plugin activation.
				return;
			}

			$pages = apply_filters(
				'cdfs_create_pages',
				array(
					'dealer_login' => array(
						'name'    => esc_html__( 'dealer-login', 'cdfs-addon' ),
						'title'   => esc_html__( 'Dealer Login', 'cdfs-addon' ),
						'content' => '[' . apply_filters( 'cdfs_dealer_login_shortcode_tag', 'cardealer_dealer_login' ) . ']',
						'layout'  => 'full-width',
					),
					'add_car' => array(
						'name'    => esc_html__( 'add-car', 'cdfs-addon' ),
						'title'   => esc_html__( 'Add Car', 'cdfs-addon' ),
						'content' => '[' . apply_filters( 'cdfs_add_car_shortcode_tag', 'cardealer_add_car' ) . ']',
						'layout'  => 'full-width',
					),
				)
			);

			foreach ( $pages as $key => $page ) {
				$page_id = cdfs_create_page(
					esc_sql( $page['name'] ),    // Page Slug
					'cdfs_' . $key . '_page_id', // Page ID Option Key
					$page['title'],              // Page Title
					$page['content'],            // Page Content
					( ! empty( $page['parent'] ) ) ? cdfs_get_page_id( $page['parent'] ) : ''
				);
				if ( array_key_exists( 'layout', $page ) && 'full-width' === $page['layout'] && $page_id ) {
					if ( 'dealer_login' === $key ) {
						$get_new_options['cdfs_dealer_login_page_id'] = $page_id;
					}
					if ( 'add_car' === $key ) {
						$get_new_options['cdfs_add_car_page_id'] = $page_id;
					}
					if ( file_exists( trailingslashit( get_template_directory() ) . 'templates/page-vc_compatible.php' ) ) {
						$current_template = get_post_meta( $page_id, '_wp_page_template', true );
						$new_template     = 'templates/cardealer-front-submission.php';

						if ( $current_template != $new_template ) {
							update_post_meta( $page_id, '_wp_page_template', $new_template );
						}
					}
				}
			}

			update_option( 'car_dealer_options', $get_new_options );
			update_option( 'cdfs_req_pages_created', true );
		}

		/**
		 * Display all alerts and notices
		 */
		public static function cdfs_notices() {
			foreach ( self::$notices as $notice ) {
				add_action( 'admin_notices', array( __CLASS__, $notice ), 9 );
			}
		}

		/**
		 * Remove notices
		 *
		 * @param string $name notice type.
		 *
		 * @return void
		 */
		public static function cdfs_remove_notice( $name ) {
			self::$notices = array_diff( self::$notices, array( $name ) );
			if ( 'cdfs_updated_notice' === $name ) {
				update_option( 'cdfs_version_status', 'up-to-date' );
			}
			self::cdfs_redirect();
		}

		/**
		 * Hide a notice if the GET variable is set.
		 */
		public static function cdfs_hide_notices() {
			if ( isset( $_GET['cdfs-hide-notice'] ) && isset( $_GET['_cdfs_notice_nonce'] ) ) {
				if ( ! wp_verify_nonce( $_GET['_cdfs_notice_nonce'], 'cdfs_hide_notices_nonce' ) ) {
					wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'cdfs-addon' ) );
				}

				$hide_notice = sanitize_text_field( $_GET['cdfs-hide-notice'] );
				self::cdfs_remove_notice( $hide_notice );
			}
		}

		/**
		 * Notice html when updating process is going on.
		 *
		 * @return void
		 */
		public static function cdfs_updating_notice() {
			?>
			<div class="notice notice-info is-dismissible">
				<p><strong><?php esc_html_e( 'Car Dealer Front End Submission Addon update', 'cdfs-addon' ); ?></strong> &#8211; <?php esc_html_e( 'Your database is being updated in the background.', 'cdfs-addon' ); ?> </p>
				<button type="button" class="notice-dismiss"></button>
			</div>
			<?php
		}

		/**
		 * Update notice html
		 *
		 * @return void
		 */
		public static function cdfs_update_notice() {
			?>
			<div id="message" class="updated cdfs-message cdfs-connect">
				<p><strong><?php esc_html_e( 'Car Dealer Front End Submission Addon update', 'cdfs-addon' ); ?></strong> &#8211; <?php esc_html_e( 'We need to update your store database to the latest version.', 'cdfs-addon' ); ?></p>
				<p class="submit"><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'do_update_cdfs', 'true', get_permalink() ), 'cdfs_update_nonce', '_cdfs_update_nonce' ) ); ?>" class="cdfs-update-now button-primary"><?php esc_html_e( 'Run the updater', 'cdfs-addon' ); ?></a></p>
			</div>
			<script type="text/javascript">
				jQuery( '.cdfs-update-now' ).click( 'click', function() {
					return confirm("<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'cdfs-addon' ) ); ?>");
				});
			</script>
			<?php
		}

		/**
		 * Notice html when updating process is completed
		 *
		 * @return void
		 */
		public static function cdfs_updated_notice() {
			?>
			<div class="notice notice-success">
				<p><?php esc_html_e( 'Car Dealer Front End Submission Addon update complete. Thank you for updating to the latest version!', 'cdfs-addon' ); ?></p>
				<a class="cdfs-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cdfs-hide-notice', 'cdfs_updated_notice', remove_query_arg( 'do_update_cdfs' ) ), 'cdfs_hide_notices_nonce', '_cdfs_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'cdfs-addon' ); ?></a>
			</div>
			<?php
		}

		/**
		 * UPDATE PLUGIN VERSION
		 *
		 * @param string $db_version database version.
		 *
		 * @return void
		 */
		public static function cdfs_update_db_version( $db_version = null ) {
			delete_option( 'cdfs_version' );
			add_option( 'cdfs_version', is_null( $db_version ) ? CDFS_VERSION : $db_version );
		}


		/**
		 * Push all needed DB updates to the queue for processing.
		 */
		private static function CDFS_update() {
			$current_db_version = get_option( 'cdfs_version' );
			$update_queued      = false;
			foreach ( self::CDFS_get_db_update_callbacks() as $version => $update_callbacks ) {
				if ( version_compare( $current_db_version, $version, '<' ) ) {
					foreach ( $update_callbacks as $update_callback ) {
						CDFS::log(
							/* translators: %1$s: version,  %2$s: update callback */
							sprintf( esc_html__( 'Queuing %1$s - %2$s', 'cdfs-addon' ), $version, $update_callback ),
							$version,
							'INFO'
						);
						self::$background_updater->push_to_queue( $update_callback );
						$update_queued = true;
					}
				}
			}
			if ( $update_queued ) {
				self::$background_updater->save()->dispatch();
			}
			self::cdfs_redirect();
		}

		/**
		 * Redirect method
		 *
		 * @return void
		 */
		public static function cdfs_redirect() {
			if ( wp_get_referer() ) {
				wp_safe_redirect( wp_get_referer() );
			} else {
				wp_safe_redirect( admin_url( 'admin.php?page=cardealer' ) );
			}
			exit;
		}

		/**
		 * Create files/directories.
		 */
		private static function cdfs_create_files() {
			// Bypass if filesystem is read-only and/or non-standard upload system is used.
			if ( apply_filters( 'CDFS_install_skip_create_files', false ) ) {
				return;
			}

			// Install files and folders for uploading files and prevent hotlinking.
			$upload_dir = wp_upload_dir();
			$files      = array(
				array(
					'base'    => $upload_dir['basedir'] . '/cardealer-front-submission',
					'file'    => 'index.html',
					'content' => '',
				),
				array(
					'base'    => CDFS_LOG_DIR,
					'file'    => '.htaccess',
					'content' => 'deny from all',
				),
				array(
					'base'    => CDFS_LOG_DIR,
					'file'    => 'index.html',
					'content' => '',
				),
			);

			foreach ( $files as $file ) {
				if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
					if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
						fwrite( $file_handle, $file['content'] );
						fclose( $file_handle );
					}
				}
			}
		}

		/**
		 * Create required tables.
		 *
		 * @return void
		 */
		private static function cdfs_create_tables() {
			global $wpdb;
			$wpdb->hide_errors();
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// Execute create table queries.
			dbDelta( self::cdfs_get_schema() );
		}

		/**
		 * Create table queries.
		 */
		private static function cdfs_get_schema() {
			global $wpdb;

			$collate = '';
			if ( $wpdb->has_cap( 'collation' ) ) {
				$collate = $wpdb->get_charset_collate();
			}

			$tables = "
				CREATE TABLE {$wpdb->prefix}cdfs_sessions (
				  session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				  session_key char(32) NOT NULL,
				  session_value longtext NOT NULL,
				  session_expiry BIGINT UNSIGNED NOT NULL,
				  PRIMARY KEY  (session_key),
				  UNIQUE KEY session_id (session_id)
				) $collate;
				";

			return $tables;
		}

		/**
		 * Add notice based on update status
		 */
		public static function cdfs_status() {
			$current_cdfs_version = get_option( 'cdfs_version', null );
			if ( is_null( $current_cdfs_version ) ) {
				return;
			}

			if ( version_compare( $current_cdfs_version, max( array_keys( self::$db_updates ) ), '<' ) ) {
				$updater = new CDFS_Background_Updater();
				if ( $updater->is_updating() ) {
					array_push( self::$notices, 'cdfs_updating_notice' );
				}
			} else {
				$get_version_status = get_option( 'cdfs_version_status' );
				if ( ! empty( $get_version_status ) && 'updated' === $get_version_status ) {
					array_push( self::$notices, 'cdfs_updated_notice' );
				}
			}
		}

		/**
		 * Get list of DB update callbacks.
		 *
		 * @return array
		 */
		public static function cdfs_get_db_update_callbacks() {
			return self::$db_updates;
		}

		/**
		 * Create user role.
		 *
		 * @return void
		 */
		public static function cdfs_create_user_role() {

			$role = get_role( 'car_dealer' );
			if ( is_null( $role ) ) {
				$result = add_role(
					'car_dealer',
					esc_html__( 'Car Dealer', 'cdfs-addon' ),
					array(
						'create_posts' => true,
						'edit_posts'   => true,
						'delete_posts' => false,
					)
				);
				if ( null === $result ) {
					cdfs_add_notice( esc_html__( 'These was an error creating a role!', 'cdfs-addon' ), 'error' );
				}
			}
		}

		/**
		 * Filters the query arguments for the list of users in the dropdown.
		 *
		 * @since 3.4.0
		 *
		 * @param array $args        The query arguments for get_users().
		 * @param array $parsed_args The arguments passed to wp_dropdown_users() combined with the defaults.
		 */
		public static function filter_authors( $args, $parsed_args ) {
			$current_screen = get_current_screen();

			if ( 'cars' ===  $current_screen->id ) {
				$args['role__in'] = [ 'administrator', 'car_dealer', 'subscriber' ];
				unset( $args['who'] );
			}
			return $args;
		}

	}
}
