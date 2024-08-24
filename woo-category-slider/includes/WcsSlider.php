<?php
/**
 * WooCommerce Category Slider Shortcode
 *
 * @package widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WcsSlider' ) ) {
	/**
	 * Shortcode Slider class
	 *
	 * @class WcsSlider
	 */
	class WcsSlider {

		/**
		 * Identifier of slider.
		 *
		 * @var string
		 */
		private string $id;

		/**
		 * Categories to include in slider.
		 *
		 * @var array
		 */
		private array $category = array();

		/**
		 * Slider Shortcode Slider constructor.
		 *
		 * @param array $atts {
		 *      Optional. Array of Widget parameters.
		 *
		 *      @type string    $id                     CSS ID of the widget, if empty a random ID will be assigned.
		 *      @type string    $category               List of comma-separated categories of WooCommerce Product
		 *                                              categories to include defaults to all categories (array(0)).
		 * }
		 */
		public function __construct( array $atts = array() ) {
			if ( key_exists( 'id', $atts ) && 'string' === gettype( $atts['id'] ) ) {
				$this->id = $atts['id'];
			} else {
				$this->id = uniqid();
			}
			if ( key_exists( 'category', $atts ) && 'string' === gettype( $atts['category'] ) ) {
				include_once WCS_ABSPATH . 'includes/wcs-functions.php';
				$this->category = wcs_text_sliders_string_to_array_ints( $atts['category'] );
			}
			$this->include_styles_and_scripts();
		}

		/**
		 * Get Testimonials corresponding to this slider.
		 *
		 * @return WP_Term[]
		 */
		public function get_categories(): array {
			$terms = array();

			foreach ( $this->category as $category ) {
				$term_obj = get_term( $category, 'product_cat' );
				if ( ! is_null( $term_obj ) ) {
					$terms[] = $term_obj;
				}
			}

			return $terms;
		}

		/**
		 * Get the ID of this slider.
		 *
		 * @return string
		 */
		public function get_id(): string {
			return $this->id;
		}

		/**
		 * Include all styles and scripts required for this slider to work.
		 */
		public function include_styles_and_scripts() {
			wp_enqueue_style( 'swiper', WCS_PLUGIN_URI . 'assets/css/swiper-bundle.min.css', array(), '11.1.0' );
			wp_enqueue_script( 'swiper', WCS_PLUGIN_URI . 'assets/js/swiper-bundle.min.js', array(), '11.1.0' );
			wp_enqueue_script( 'wcs-swiper-activation', WCS_PLUGIN_URI . 'assets/js/swiper-activation.js', array( 'swiper' ), '1.0', true );
			wp_enqueue_style( 'wcs-swiper-overrides', WCS_PLUGIN_URI . 'assets/css/swiper-overrides.css', array(), '1.0' );
		}

		/**
		 * Localize the slider activation javascript file to activate all activated sliders.
		 *
		 * @param WcsSlider[] $sliders sliders to localize the activation script for.
		 */
		public static function localize_swiper_activation( array $sliders ): void {
			$configs = array();
			foreach ( $sliders as $slider ) {
				$configs[] = array(
					'id' => 'wcs-swiper-container-' . $slider->get_id(),
				);
			}
			wp_localize_script( 'wcs-swiper-activation', 'swiper_configs', $configs );
		}

		/**
		 * Get the contents of the shortcode.
		 *
		 * @return false|string
		 */
		public function do_shortcode() {
			ob_start();
			$categories = $this->get_categories(); ?>
				<div id="wcs-swiper-container-<?php echo esc_attr( $this->id ); ?>" class="swiper-container wcs-swiper-container">
					<div class="swiper-wrapper">
						<?php
						foreach ( $categories as $category ) {
							$banner_image_id = get_term_meta( $category->term_id, 'wcs_slider_image', true );

							if ( $banner_image_id ) {
								$banner_image = wp_get_attachment_image_src( $banner_image_id, 'full' );
								if ( false === $banner_image ) {
									$banner_image = null;
								}
								$banner_image = $banner_image[0];
							} else {
								$banner_image = null;
							}
							?>
							<div class="swiper-slide">
								<div class="category-wrapper">
									<div class="category-image">
										<?php if ( ! is_null( $banner_image ) ) : ?>
											<img src="<?php echo esc_attr( $banner_image ); ?>">
										<?php endif; ?>
									</div>
									<div class="category-text">
										<h3><?php echo esc_html( $category->name ); ?></h3>
										<a class="button category-button" href="<?php echo esc_attr( get_term_link( $category->term_id, 'product_cat' ) ); ?>"><?php echo esc_html( __( 'Show more', 'woo-category-slider' ) ); ?></a>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			<?php
			$ob_content = ob_get_contents();
			ob_end_clean();
			return $ob_content;
		}
	}
}
