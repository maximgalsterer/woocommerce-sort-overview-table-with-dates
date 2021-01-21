/* In order for the following instructions to work, we need variable products in WooCommerce that have a date on at least one property. In our example, we use a property called “Date” (slug: date). Here, dates are stored in the date format YYYY-mm-dd. */

// Product table overview 
function product_table_overview() {
  global $wp_query;
  $cat = $wp_query->get_queried_object();

  $args = array(
    'post_type'   => 'product',
    'numberposts' => -1,
    'post_status' => 'publish',
    'product_cat' => $cat->slug
  );
  $products = get_posts( $args );

  $rows = array();
  foreach ( $products as $product ) :
    $product_id = $product->ID;
    $product_variation = wc_get_product( $product->ID );
    if ( $product_variation->product_type == 'variable' ) {
      $variations = $product_variation->get_available_variations();

      foreach ( $variations as $variation ):
        $variation_id = $variation['variation_id'];
        $variation    = new WC_Product_Variation( $variation_id );
        $attributes   = $variation->get_variation_attributes();
        $date         = $attributes['attribute_pa_date'];
        $date_name    = get_term_by( 'slug', $datum, 'pa_date' );

        if ( !empty( $date ) ) {
          $rows[] = array( $product_id, $product->post_title, $variation_id, $date, $date_name->name );
        }
      endforeach;
    }
  endforeach;

  // Sort array by dates
  usort( $rows, function($a, $b) {
    return strtotime( $a[4] ) <=> strtotime( $b[4] );
  });
  ?>

  <h2 class="product-table-overview-title"><?php _e( 'Dates in the overview', 'tsc' ); ?></h2>
  <table class="product-table-overview"><thead><tr><th><?php _e( 'Image', 'commotion' ); ?></th><th><?php _e( 'Date', 'commotion' ); ?></th><th><?php _e( 'Product', 'commotion' ); ?></th><th><?php _e( 'Cart', 'commotion' ); ?></th></tr></thead><tbody>
    <?php
    foreach ( $rows as $row ) :
      if ( date( 'Y-m-d', strtotime( $row[4] ) ) > date( 'Y-m-d' ) ) :
        ?>
        <tr><td><img src="<?= get_the_post_thumbnail_url( $row[0], 'woocommerce_thumbnail' ) ?>" alt="<?= $row[1] ?>"></td><td><a href="<?= get_permalink( $row[0] ) ?>'?attribute_pa_datum=<?= $row[3] ?>"><?= $row[4] ?></a></td><td><a href="<?= get_permalink( $row[0] ) ?>?attribute_pa_datum=<?= $row[3] ?>"><strong><?= $row[1] ?></strong></a></td><td><a href="/?add-to-cart=<?= $row[0] ?>&variation_id=<?= $row[2] ?>&attribute_pa_datum=<?= $row[4] ?>"><i class="fas fa-shopping-bag"></i> <?php _e( 'Add to cart', 'woocommerce' ); ?></a></td></tr>
        <?php
      endif;
    endforeach;
    ?>
  </tbody></table>
<?php
}
// add_action( 'woocommerce_before_shop_loop', 'product_table_overview' ); // Activate to show overview before the shop loop
add_action( 'woocommerce_after_shop_loop', 'product_table_overview' ); // Overview after the shop loop
