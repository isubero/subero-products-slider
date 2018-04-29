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
		
		/* Ajax functions */
		add_action( 'wp_ajax_get_slider_content_ajax_handler', array($this, 'get_slider_content_ajax_handler') );
		add_action( 'wp_ajax_nopriv_get_slider_content_ajax_handler', array($this, 'get_slider_content_ajax_handler') );
	}

	public function register_scripts(){
		wp_enqueue_style( 'slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css', '', '1.8.1'  );
		wp_enqueue_style( 'slick-theme', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css', '', '1.8.1' );
		wp_enqueue_style( 'subero-products-slider', plugin_dir_url(__FILE__) . 'style.css', '', '1.1' );
		
		wp_enqueue_script( 'slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array('jquery'), '1.8.1', true);
		
		wp_enqueue_script( 'sb-products-slider',  plugins_url( 'assets/js/scripts.js', __FILE__ ), array('jquery', 'slick'), '1.0', true);
		wp_localize_script( 'sb-products-slider', 'sps_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	public function get_slider_content($attributes) {

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
			} else {
				$query_args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
			}
		}
		
		$query = new WP_Query( $query_args );

		echo '<div id="sb-products-slider" class="sb-products-slider woocommerce">'; ?>

		<?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
			<div class="sb-slide">
				<?php wc_get_template_part('content', 'product'); ?>
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

	public function get_slider_content_ajax_handler() {
		
		$limit = ($_POST['limit'] == 'false') ? -1 : $_POST['limit'];

		$query_args = array(
			'posts_per_page'    => $limit,
			'no_found_rows'     => 1,
			'post_status'       => 'publish',
			'post_type'         => 'product'
		);

		if ($_POST['product_ids'] != '') {
			$query_args['post__in'] = array_map('intval', explode(',', $_POST['product_ids']) );
		}

		if ($_POST['category'] != '') {
			$query_args['product_cat'] = $_POST['category'];
		}

		if ( $_POST['on_sale'] == 'true' ) {
			
			if ( $_POST['category'] != '' && $_POST['product_ids'] == '' ) { 
				$query_args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
			}

			if ( $_POST['product_ids'] != '' ) {
				$product_ids = ($_POST['product_ids'] == '') ? array() : array_map('intval', explode(',', $_POST['product_ids']) );
				$query_args['post__in'] = array_intersect( wc_get_product_ids_on_sale(), $product_ids );
			} else {
				$query_args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
			}
		}

		$query = new WP_Query( $query_args );

		$slider_id = uniqid('sps_');

		echo '<div id="'.$slider_id.'" class="subero-products-slider woocommerce '.$slider_id.'">'; ?>

		<?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
			<div class="sb-slide">
				<?php wc_get_template_part('content', 'product'); ?>
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

		wp_die(); // End ajax call
	}

	public function shortcode_callback($atts) {

		$attributes = shortcode_atts( array(
			'product_ids'	=> '',
			'category'		=> '',
			'on_sale'		=> 'false',
			'limit'			=> 10,
		), $atts);
		
		$content = '<div class="sb-products-slider-wrapper" 
						products="'.$attributes['product_ids'].'"
						category="'.$attributes['category'].'"
						on-sale="'.$attributes['on_sale'].'"
						limit="'.$attributes['limit'].'"
					>
						Cargando...
					</div>';
		
		return $content;
	}

	public function initialize_slider() {
		?>
			<script>
			jQuery(document).ready(function() {
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
			});
			</script>
		<?php 
	}
}

$GLOBALS['sps'] = new Subero_Products_Slider();