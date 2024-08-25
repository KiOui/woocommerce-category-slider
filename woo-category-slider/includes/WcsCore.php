<?php
/**
 * Woo Category Slider Core
 *
 * @package woo-category-slider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WcsCore' ) ) {
	/**
	 * Woo Category Slider core class.
	 *
	 * @class WcbCore
	 */
	class WcsCore {


		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public string $version = '1.0.0';

		/**
		 * The single instance of the class
		 *
		 * @var WcsCore|null
		 */
		private static ?WcsCore $instance = null;

		/**
		 * Registered sliders.
		 *
		 * @var array
		 */
		private array $sliders = array();

		/**
		 * Woo Category Slider Core
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return WcsCore
		 */
		public static function instance(): WcsCore {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			$this->define_constants();
			$this->actions_and_filters();
			$this->add_term_meta();
			$this->add_shortcodes();
		}

		/**
		 * Define constants of the plugin.
		 */
		private function define_constants(): void {
			$this->define( 'WCS_ABSPATH', dirname( WCS_PLUGIN_FILE ) . '/' );
			$this->define( 'WCS_FULLNAME', 'woo-category-slider' );
		}

		/**
		 * Define if not already set.
		 *
		 * @param string $name  the name.
		 * @param string $value the value.
		 */
		private static function define( string $name, string $value ): void {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Initialise Woo Category Slider Core.
		 */
		public function init(): void {
			$this->initialise_localisation();
			do_action( 'woo_category_slider_init' );
		}

		/**
		 * Initialise the localisation of the plugin.
		 */
		private function initialise_localisation(): void {
			load_plugin_textdomain( 'woo-category-slider', false, plugin_basename( dirname( WCS_PLUGIN_FILE ) ) . '/languages/' );
		}

		/**
		 * Add pluggable support to functions.
		 */
		public function pluggable(): void {
			include_once WCS_ABSPATH . 'includes/wcs-functions.php';
		}

		/**
		 * Add actions and filters.
		 */
		private function actions_and_filters(): void {
			add_action( 'after_setup_theme', array( $this, 'pluggable' ) );
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'wp_footer', array( $this, 'localize_slider_script' ) );
		}

		/**
		 * Add term meta data fields.
		 *
		 * @return void
		 */
		private function add_term_meta(): void {
			include_once WCS_ABSPATH . '/includes/meta/class-term-meta.php';

			$term_meta = new TermMeta(
				'product_cat',
				array(
					array(
						'id' => 'wcs_slider_image',
						'label' => 'Slider Image',
						'desc' => 'Slider image for the category',
						'type' => 'image',
					),
				)
			);
		}

		/**
		 * Add the Category slider shortcode.
		 */
		public function add_shortcodes(): void {
			add_shortcode( 'wcs_category_slider', array( $this, 'do_shortcode_slider' ) );
		}

		/**
		 * Do the shortcode of a text slider.
		 *
		 * @param $atts
		 * @return false|string
		 */
		public function do_shortcode_slider( $atts ) {
			if ( gettype( $atts ) != 'array' ) {
				$atts = array();
			}

			include_once WCS_ABSPATH . 'includes/WcsSlider.php';
			$shortcode = new WcsSlider( $atts );
			$this->sliders[] = $shortcode;
			return $shortcode->do_shortcode();
		}

		/**
		 * Localize the slider script so that the sliders activate.
		 */
		public function localize_slider_script() {
			if ( count( $this->sliders ) > 0 ) {
				include_once WCS_ABSPATH . 'includes/WcsSlider.php';
				WcsSlider::localize_swiper_activation( $this->sliders );
			}
		}
	}
}
