/* In order for the following instructions to work, we need variable products in WooCommerce that have a date on at least one property. In our example, we use a property called “Date” (slug: date). Here, dates are stored in the date format YYYY-mm-dd. */

// Sort dates and hide past dates in the select box on product single pages
function hide_past_dates( $html, $args ) {
	$options               = $args['options'];
	$product               = $args['product'];
	$attribute             = $args['attribute'];
	$name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
	$id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
	$class                 = $args['class'];
	$show_option_none      = $args['show_option_none'] ? true : false;
	$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' );

	if ( empty( $options ) && !empty( $product ) && !empty( $attribute ) ) {
		$attributes = $product->get_variation_attributes();
		$options = $attributes[$attribute];
	}

	if ( $attribute == 'pa_date' ) :
		$html = '<select id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
		$html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

		if ( !empty( $options ) ) {
			if ( $product && taxonomy_exists( $attribute ) ) {
				$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
				$terms = json_decode( json_encode( $terms ), true ); // Convert object to array
				usort( $terms, function($a, $b) {
					return strtotime( $a['name'] ) <=> strtotime( $b['name'] );
				});

				// Output from array
				foreach ( $terms as $term ) {
					if ( in_array( $term['slug'], $options ) && date( 'Y-m-d', strtotime( $term['name'] ) ) > date( 'Y-m-d' ) ) {
						$html .= '<option value="' . esc_attr( $term['slug'] ) . '" ' . selected( sanitize_title( $args['selected'] ), $term['slug'], false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term['name'] )  ) . '</option>';
					}
				}
			} else {
				foreach ( $options as $option ) {
					$selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
					$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
				}
			}
		}
		$html .= '</select>';
	endif;

	return $html;
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'hide_past_dates', 10, 2 );
