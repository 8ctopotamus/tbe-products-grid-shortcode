<?php
/*
  Plugin Name: TBE Products Grid Shortcode
  Plugin URI:  https://icshelpsyou.com/contact-us
  Description: A customized Woocommerce Products Grid shortcode for TBE Trailers.
  Version:     1.0
  Author:      ICS Helps You
  Author URI:  https://icshelpsyou.com
  License:     GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function tbe_woocommerce_notice() { ?>
	<div class="update-nag notice">
	  <p><?php _e( 'Please install and activate the <a href="https://woocommerce.com/" title="Woocommerce site" target="_blank">Woocommerce</a>. It is required for the <strong>TBE Products Grid Shortcode</strong> plugin to work properly!', 'tbe_products_grid_shortcode' ); ?></p>
	</div>
<?php }

// if ( !class_exists( 'WooCommerce' ) ) {
//   add_action( 'admin_notices', 'tbe_woocommerce_notice' );
// 	return;
// }

/*
 * Load scripts and styles
 */
function fmmh_register_scripts_and_styles() {
	wp_enqueue_style( 'tbe-products-grid-shortcode-styles', plugins_url( '/css/tbe-products-grid-shortcode.css',  __FILE__ ));
}
add_action('wp_enqueue_scripts', 'fmmh_register_scripts_and_styles');


/*
 * Get woocommerce product data attributes
 * NOTE: This is not used in the shortcode below... yet.
 * Just leaving it here for future reference.
 */
function tbe_woo_attributes($product) {
    $attributes = $product->get_attributes();
    if ( ! $attributes ) {
        return;
    }

    $display_result = '';

    foreach ( $attributes as $attribute ) {
        if ( $attribute->get_variation() ) {
            continue;
        }

        $name = $attribute->get_name();

        if ( $attribute->is_taxonomy() ) {

            $terms = wp_get_post_terms( $product->get_id(), $name, 'all' );

            $cwtax = $terms[0]->taxonomy;

            $cw_object_taxonomy = get_taxonomy($cwtax);

            if ( isset ($cw_object_taxonomy->labels->singular_name) ) {
                $tax_label = $cw_object_taxonomy->labels->singular_name;
            } elseif ( isset( $cw_object_taxonomy->label ) ) {
                $tax_label = $cw_object_taxonomy->label;
                if ( 0 === strpos( $tax_label, 'Product ' ) ) {
                    $tax_label = substr( $tax_label, 8 );
                }
            }
            $display_result .= $tax_label . ': ';
            $tax_terms = array();
            foreach ( $terms as $term ) {
                $single_term = esc_html( $term->name );
                array_push( $tax_terms, $single_term );
            }
            $display_result .= implode(', ', $tax_terms) .  '<br />';

        } else {
            $display_result .= $name . ': ';
            $display_result .= esc_html( implode( ', ', $attribute->get_options() ) ) . '<br />';
        }
    }
    echo $display_result;
}

// product grid
function tbe_products_grid_function($args) {
    $cat = $args['category_id'];
    $args = array(
      'post_type' => 'product',
      'posts_per_page' => -1,
      'tax_query' => array(
        array(
            'taxonomy' => 'product_cat',
            'terms' => $cat,
            'operator' => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
        ),
      ),
			'meta_query' => array(
        array(
          'key' => '_stock_status',
          'value' => 'instock'
        )
      )
    );
    $html = '<div class="tbe_products_grid">';
    $loop = new WP_Query( $args );
    while ( $loop->have_posts() ) : $loop->the_post();
    global $product;
      $html .= '<div class="tbe_product">';
      $html .= '<a href="' . get_the_permalink() . '">';
      $html .= '<div class="tbe_product_image" style="background-image: url(' . get_the_post_thumbnail_url($product->id, 'medium') . ');"></div>';
      $html .= apply_filters( 'the_content', get_the_content() );
      $html .= '</a>';
      $html .= '</div>';
    endwhile;
    $html .= '</div>';
    wp_reset_query();
    return $html;
}

add_shortcode('tbe_products_grid', 'tbe_products_grid_function');

?>
