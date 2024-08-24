<?php
/**
 * Woo Category Slider functions
 *
 * @package woo-category-slider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wcs_text_sliders_string_to_array_ints' ) ) {
	/**
	 * Convert a string of comma separated integers to an array of ints.
	 *
	 * @param string $input string of comma separated integers.
	 *
	 * @return int[]
	 */
	function wcs_text_sliders_string_to_array_ints( string $input ): array {
		$splitted  = explode( ',', $input );
		$array_ids = array();
		foreach ( $splitted as $element ) {
			$converted_int = filter_var(
				$element,
				FILTER_VALIDATE_INT,
				array(
					'flags'   => FILTER_NULL_ON_FAILURE,
					'options' => array(
						'min_range' => 0,
					),
				)
			);
			if ( isset( $converted_int ) ) {
				$array_ids[] = $converted_int;
			}
		}

		return $array_ids;
	}
}
