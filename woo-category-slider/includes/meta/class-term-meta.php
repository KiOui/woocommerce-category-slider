<?php
/**
 * TermMeta class
 *
 * @package woo-category-slider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TermMeta' ) ) {
	/**
	 * TermMeta class
	 *
	 * This class is able to render custom meta fields for terms.
	 *
	 * @class TermMeta
	 */
	class TermMeta {

		/**
		 * Name of the category.
		 *
		 * @var string
		 */
		private string $category;

		/**
		 * Form fields for the category.
		 *
		 * @var array
		 */
		private array $form_fields;

		/**
		 * TermMeta constructor.
		 *
		 * @param string $category the category type to add the form data to.
		 * @param array  $form_fields array of (label: Label of meta box, desc: Description of meta box, id: ID of meta box
		 *  (used in database), type: Type of meta box (for input field)).
		 */
		public function __construct( string $category, array $form_fields ) {
			$this->category = $category;
			$this->form_fields = $form_fields;
			$this->actions_and_filters();
		}

		/**
		 * Actions and filters.
		 */
		public function actions_and_filters(): void {
			add_action( $this->category . '_add_form_fields', array( $this, 'show_form_fields' ) );
			add_action( $this->category . '_edit_form_fields', array( $this, 'show_form_fields' ) );
			add_action( 'created_term', array( $this, 'save_form_fields' ), 10, 3 );
			add_action( 'edit_term', array( $this, 'save_form_fields' ), 10, 3 );
		}

		/**
		 * Get the name of the nonce corresponding to this meta box.
		 *
		 * @return string name of the nonce corresponding to this meta box
		 */
		private function get_nonce_name(): string {
			return $this->category . '_nonce';
		}

		/**
		 * Creates HTML for the custom meta box
		 */
		public function show_form_fields( string|WP_Term $tag ): void {
			wp_nonce_field( basename( __FILE__ ), $this->get_nonce_name() ); ?>
				<table class="form-table">
					<?php foreach ( $this->form_fields as $field ) : ?>
						<?php
						if ( 'string' === gettype( $tag ) ) {
							$meta = null;
						} else {
							$meta = get_term_meta( $tag->term_id, $field['id'], true );
						}
						?>
						<tr>
							<th><label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td>
								<?php
								switch ( $field['type'] ) :
									case 'number':
										?>
												<input type="number"
												   <?php
													if ( array_key_exists( 'required', $field ) && $field['required'] ) :
														?>
														required
														<?php
												   endif;
													?>
												   <?php
													if ( array_key_exists( 'step', $field ) ) :
														?>
														step="<?php echo esc_attr( $field['step'] ); ?>"
														<?php
												   endif;
													?>
												   <?php
													if ( array_key_exists( 'min', $field ) ) :
														?>
														min=<?php echo esc_attr( $field['min'] ); ?>
														<?php
												   endif;
													?>
												   <?php
													if ( array_key_exists( 'max', $field ) ) :
														?>
														max=<?php echo esc_attr( $field['max'] ); ?>
														<?php
												   endif;
													?>
													   name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $meta ); ?>" />
												<br>
												<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
											<?php
										break;
									case 'text':
										?>
											<input type="text"
												   <?php
													if ( array_key_exists( 'required', $field ) && $field['required'] ) :
														?>
														required
														<?php
												   endif;
													?>
												   name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $meta ); ?>" />
											<br>
											<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
																	<?php
										break;
									case 'textarea':
										?>
											<textarea style="width: 100%; min-height: 200px;"
													<?php
													if ( array_key_exists( 'required', $field ) && $field['required'] ) :
														?>
														required
														<?php
													endif;
													?>
												   name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_textarea( $meta ); ?></textarea>
											<br>
											<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
																	<?php
										break;
									case 'checkbox':
										?>
											<input type="checkbox"
												   <?php
													if ( array_key_exists( 'required', $field ) && $field['required'] ) :
														?>
														required
														<?php
												   endif;
													?>
												   name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>"
																	<?php
																	if ( $meta ) :
																		echo 'checked=checked';
									endif;
																	?>
			 />
											<br>
											<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
											<?php
										break;
									case 'select':
										?>
											<select name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>">
												<?php foreach ( $field['options'] as $option ) : ?>
													<option
													<?php
													if ( $meta == $option['value'] ) :
														echo 'selected="selected"';
			endif
													?>
			 value="<?php echo esc_attr( $option['value'] ); ?>">
														<?php echo esc_html( $option['label'] ); ?>
													</option>
												<?php endforeach ?>
											</select>
											<br>
											<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
											<?php
										break;
									case 'image':
										if ( $meta ) {
											$image_url = wp_get_attachment_thumb_url( $meta );
											if ( false === $image_url ) {
												$image_url = null;
											}
										} else {
											$image_url = null;
										}
										?>
										<div id="<?php echo esc_html( 'wcs_' . $field['id'] ); ?>" style="float: left; margin-right: 10px;"><img style="display:
															<?php
															if ( null === $image_url ) {
																echo 'none';
															} else {
																echo 'block'; }
															?>
										" src="<?php echo esc_url( $image_url ); ?>" width="60px" height="60px" /></div>
										<div style="line-height: 60px;">
											<input type="hidden" id="<?php echo esc_html( $field['id'] ); ?>" name="<?php echo esc_html( $field['id'] ); ?>" value="<?php echo esc_html( $meta ); ?>" />
											<button type="button" class="<?php echo esc_attr( 'wcs_upload_image_button_' . $field['id'] ); ?> button"><?php esc_html_e( 'Upload/Add image', 'woo-category-slider' ); ?></button>
											<button type="button" class="<?php echo esc_attr( 'wcs_remove_image_button_' . $field['id'] ); ?> button"><?php esc_html_e( 'Remove image', 'woo-category-slider' ); ?></button>
										</div>
										<script type="text/javascript">

											// Only show the "remove image" button when needed
											if ( ! jQuery( '#<?php echo esc_html( $field['id'] ); ?>' ).val() ) {
												jQuery( '.<?php echo esc_attr( 'wcs_remove_image_button_' . $field['id'] ); ?>' ).hide();
											}

											// Uploading files
											var file_frame;

											jQuery( document ).on( 'click', '.<?php echo esc_attr( 'wcs_upload_image_button_' . $field['id'] ); ?>', function( event ) {

												event.preventDefault();

												// If the media frame already exists, reopen it.
												if ( file_frame ) {
													file_frame.open();
													return;
												}

												// Create the media frame.
												file_frame = wp.media.frames.downloadable_file = wp.media({
													title: '<?php esc_html_e( 'Choose an image', 'woo-category-slider' ); ?>',
													button: {
														text: '<?php esc_html_e( 'Use image', 'woo-category-slider' ); ?>'
													},
													multiple: false
												});

												// When an image is selected, run a callback.
												file_frame.on( 'select', function() {
													var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
													var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

													jQuery( '#<?php echo esc_html( $field['id'] ); ?>' ).val( attachment.id );
													jQuery( '#<?php echo esc_html( 'wcb_' . $field['id'] ); ?>' ).find( 'img' ).attr( 'src', attachment_thumbnail.url ).removeAttr("style").show();
													jQuery( '.<?php echo esc_attr( 'wcb_remove_image_button_' . $field['id'] ); ?>' ).show();
												});

												// Finally, open the modal.
												file_frame.open();
											});

											jQuery( document ).on( 'click', '.<?php echo esc_attr( 'wcs_remove_image_button_' . $field['id'] ); ?>', function() {
												jQuery( '#<?php echo esc_html( 'wcb_' . $field['id'] ); ?>' ).find( 'img' ).attr( 'src', null ).removeAttr("style").hide();
												jQuery( '#<?php echo esc_html( $field['id'] ); ?>' ).val( '' );
												jQuery( '.<?php echo esc_attr( 'wcs_remove_image_button_' . $field['id'] ); ?>' ).hide();
												return false;
											});

											jQuery( document ).ajaxComplete( function( event, request, options ) {
												if ( request && 4 === request.readyState && 200 === request.status
													&& options.data && 0 <= options.data.indexOf( 'action=add-tag' ) ) {

													var res = wpAjax.parseAjaxResponse( request.responseXML, 'ajax-response' );
													if ( ! res || res.errors ) {
														return;
													}
													// Clear Thumbnail fields on submit
													jQuery( '#<?php echo esc_html( 'wcb_' . $field['id'] ); ?>' ).find( 'img' ).attr( 'src', null ).removeAttr("style").hide();
													jQuery( '#<?php echo esc_html( $field['id'] ); ?>' ).val( '' );
													jQuery( '.<?php echo esc_attr( 'wcs_remove_image_button_' . $field['id'] ); ?>' ).hide();
													// Clear Display type field on submit
													jQuery( '#display_type' ).val( '' );
													return;
												}
											} );

										</script>
										<div class="clear"></div>
										<?php
										break;
			endswitch;
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php
		}

		/**
		 * Saves custom meta tag data
		 *
		 * @param int    $term_id the term id to save the data for.
		 * @param int    $tt_id Term taxonomy ID.
		 * @param string $taxonomy Taxonomy slug.
		 * @return int: $term_id
		 */
		public function save_form_fields( int $term_id, int $tt_id, string $taxonomy ): int {
			if ( ! isset( $_POST[ $this->get_nonce_name() ] ) || ! wp_verify_nonce( wp_unslash( $_POST[ $this->get_nonce_name() ] ), basename( __FILE__ ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				return $term_id;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $term_id;
			}

			if ( $taxonomy === $this->category ) {
				foreach ( $this->form_fields as $field ) {
					$old = get_term_meta( $term_id, $field['id'], true );
					$new = isset( $_POST[ $field['id'] ] ) ? wp_unslash( $_POST[ $field['id'] ] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$value_updated = $new != $old;
					if ( $new ) {
						if ( $value_updated ) {
							update_term_meta( $term_id, $field['id'], $new );
						}
                        // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found -- The control structure below is more clear.
					} else {
						// Value has been removed or is not set.
						if ( $field['required'] ) {
							update_term_meta( $term_id, $field['id'], $field['default'] );
						} elseif ( $value_updated ) {
							delete_term_meta( $term_id, $field['id'], $old );
						}
					}
				}
			}
			return $term_id;
		}
	}
}
