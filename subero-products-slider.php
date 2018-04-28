<?php
/*
Plugin Name: Subero Products Slider
Plugin URI:  http://isaiassubero.com
Description: Shortcodes for displaying WooCommerce Products Sliders
Version:     1.0
Author:      Isaías Subero
Author URI:  http://isaiassubero.com
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
Text Domain: sps
Domain Path: /languages
License:     GPL3

Subero Products Slider is free software: you can redistribute it under the terms of 
the GNU General Public License as published by the Free Software Foundation, 
either version 3 of the License, or any later version.
 
Subero Products Slider is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Subero_Products_Slider {
	public function __construct() {
		add_action( 'init', array($this, 'register_scripts') );
		add_shortcode( 'subero_products_slider', array($this, 'shortcode_callback') );
		add_action( 'wp_footer', array($this, 'initialize_slider') );
	}

	public function register_scripts(){
		wp_enqueue_style( 'slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css', '', '1.8.1'  );
		wp_enqueue_style( 'subero-products-slider', plugin_dir_url(__FILE__) . 'style.css', '', '1.0'  );
		wp_enqueue_style( 'slick-theme', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css', '', '1.8.1' );
		wp_enqueue_script( 'slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array('jquery'), '1.8.1', true);
	}

	private function get_slider_content($attributes) {

		$limit = ($attributes['limit'] == 'false') ? -1 : $attributes['limit'];

		$query_args = array(
			'posts_per_page'    => $limit,
			'no_found_rows'     => 1,
			'post_status'       => 'publish',
			'post_type'         => 'product'
		);

		if ($attributes['product_ids'] != '') {
			$query_args['post__in'] = array_map('intval', explode(',', $attributes['product_ids']) );
		}

		if ($attributes['category'] != '') {
			$query_args['product_cat'] = $attributes['category'];
		}

		if ( $attributes['on_sale'] == 'true' ) {
			
			if ( $attributes['category'] != '' && $attributes['product_ids'] == '' ) { 
				//$query_args['meta_query'] = WC()->query->get_meta_query();
				$query_args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
			}

			if ( $attributes['product_ids'] != '' ) {
				$product_ids = ($attributes['product_ids'] == '') ? array() : array_map('intval', explode(',', $attributes['product_ids']) );
				$query_args['post__in'] = array_intersect( wc_get_product_ids_on_sale(), $product_ids );
			}
		}
		
		$query = new WP_Query( $query_args );

		echo '<div id="sb-products-slider" class="sb-products-slider woocommerce">'; ?>

		<?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
			<div class="sb-slide">
				<?php
				// Revisar si puedo reemplazar este template en mi plugin
				wc_get_template_part('content', 'product');
				?>
			</div>
		<?php endwhile; else : ?>
			<p><?php esc_html_e( 'No products found' ); ?></p>
		<?php endif; // end of loop

		wp_reset_postdata();

		echo '</div>';

		?>
			<div class="swipe-for-more">
				<span><?php _e('Swipe for more', 'sps'); ?></span>
				<img class="wp-image-9269" src="https://evolutionadvance.com/wp-content/uploads/2017/12/swipe-icon-gray.png" alt="swipe" width="200" height="200" />
			</div>
		<?php 
	}

	public function shortcode_callback($atts) {

		$attributes = shortcode_atts( array(
			'product_ids'	=> '',
			'category'		=> '',
			'on_sale'		=> 'false',
			'limit'			=> 10,
		), $atts);

		ob_start();
		$this->get_slider_content($attributes);
		$content = ob_get_clean();
		return $content;
	}

	public function initialize_slider() {
		?>
			<script>
				jQuery('.sb-products-slider').slick({
					accessibility: true,
					arrows: true,
					dots: true,
					infinite: true,
					autoPlay: true,
					speed: 300,
					slidesToShow: 3,
					slidesToScroll: 3,
					responsive: [
						{
						breakpoint: 1024,
						settings: {
							slidesToShow: 3,
							slidesToScroll: 3,
							infinite: true,
							dots: true
						}
						},
						{
						breakpoint: 600,
						settings: {
							slidesToShow: 2,
							slidesToScroll: 2
						}
						},
						{
						breakpoint: 480,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1,
							arrows: false,
						}
						}
						// You can unslick at a given breakpoint now by adding:
						// settings: "unslick"
						// instead of a settings object
					]
				});
			</script>
		<?php 
	}
}

$GLOBALS['sps'] = new Subero_Products_Slider();