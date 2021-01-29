<?php
if (! defined('WP_DEBUG')) {
	die( 'Direct access forbidden.' );
}
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
});
function add_theme_caps() {
    if( $role = get_role( 'administrator' ) ){
        $role->add_cap('unfiltered_html');
    }
}
add_action( 'admin_init', 'add_theme_caps' );


//add excerpt/short description on products
function mh_short_des_product() {
	$excerpt = get_the_excerpt();
	if($excerpt) {
		$excerpt = substr($excerpt, 0, 50);
		$result = substr($excerpt, 0, strrpos($excerpt, ' '));
		echo '<div class="product-excerpt"> <p>'.$result.'...  </p></div>';
	}
}
//add_action( 'woocommerce_after_shop_loop_item_title', 'mh_short_des_product', 40 );

// deskteam360-janicka-marie-page
add_action( 'elementor/query/deskteam360-marie-search-page', function( $query ) {
	$query->set( 's', $_GET['s'] );
} );


// START code hide prices for guest
add_action( 'wp', 'check_page_product' );
function check_page_product() {
  	if ( is_post_type_archive( 'product' ) || is_tax( get_object_taxonomies( 'product' ) ) ) {
		  add_action('woocommerce_after_shop_loop_item', 'custom_add_to_cart_product_archive', 5 );
  	}
	if( is_shop() ) {
		add_action( 'woocommerce_after_shop_loop_item_title', 'shop_item_not_login', 10, 0 );  
	}
}
function shop_item_not_login( ) { 
	$terms = get_the_terms( get_the_ID(), 'product_cat' );
	if($terms) {
		foreach ($terms as $term) {
         	$product_cat = $term->slug;
			if($product_cat == "biologique-recherche" || $product_cat == "environ") {
			  	wwp_hide_price_add_cart_not_logged_in();
			}
         }
	}
};
function custom_add_to_cart_product_archive() {
	global $post;
	$member_status = wc_memberships_is_user_active_member( get_current_user_id(), 'only-for-member' );
	$terms = get_the_terms( $post->ID, 'product_cat' );
	if($terms) {
			foreach ($terms as $term) {
				$product_cat = $term->slug;
				
				if($product_cat == "biologique-recherche") {
					if( is_user_logged_in() ) {
						if( !$member_status ) {
							echo '<style> li.post-'.$post->ID.' .ct-woo-card-actions { display: none;}</style>';
							echo '<div class="" style="padding-top: 20px; margin-top: auto;"> <a class="button btn-not-login" style="font-size: small; padding-left: 10%; padding-right: 10%;" href="/biologique-recherche-contact-page">' . __('Request Customized Regimine', 'theme_name') . '</a> </div>';
						}

					}
					else {
						echo '<style> li.post-'.$post->ID.' .ct-woo-card-actions { display: none;}</style>';
					echo '<div class="" style="padding-top: 20px; margin-top: auto;"> <a class="button btn-not-login" href="' . get_permalink(wc_get_page_id('myaccount')) . '">' . __('Login to See Prices', 'theme_name') . '</a> </div>';
					}

				}
				
				if( $product_cat == "environ" && !is_user_logged_in() ) {
					echo '<style> li.post-'.$post->ID.' .ct-woo-card-actions { display: none;}</style>';
					echo '<div class="" style="padding-top: 20px; margin-top: auto;"> <a class="button btn-not-login" href="' . get_permalink(wc_get_page_id('myaccount')) . '">' . __('Login to See Prices', 'theme_name') . '</a> </div>';
					
				}
				
				
			 }
	}
}

add_action('wp', 'single_product_price', 25);
function single_product_price() {
    $terms = get_the_terms( get_the_ID(), 'product_cat' );
	$member_status = wc_memberships_is_user_active_member( get_current_user_id(), 'only-for-member' );
	if ( is_product() ) {
		if($terms) {
			foreach ($terms as $term) {
				$product_cat = $term->slug;
				if($product_cat == "biologique-recherche" ) {
					if( is_user_logged_in() ) {
						if( !$member_status ) {
							add_filter( 'woocommerce_product_single_add_to_cart_text', function() {
								return "Request Customized Regimine";
							} );
							add_filter( 'woocommerce_get_price_html', 'single_price_html_callback', 10, 2 );
							add_action('wp_head', 'single_not_login_css');
						}
					}
					else {
						add_filter( 'woocommerce_product_single_add_to_cart_text', 'custom_single_addtocart_text' );
						add_filter( 'woocommerce_get_price_html', 'single_price_html_callback', 10, 2 );
						add_action('wp_head', 'single_not_login_css');
					}

				}
				if( $product_cat == "environ" && !is_user_logged_in() ) {
					add_filter( 'woocommerce_product_single_add_to_cart_text', 'custom_single_addtocart_text' );
					add_filter( 'woocommerce_get_price_html', 'single_price_html_callback', 10, 2 );
					add_action('wp_head', 'single_not_login_css');
				}
			 }
		}
	}
}
function custom_single_addtocart_text() {
    return "Login to See Prices";
}
function single_price_html_callback( $price, $product ) {
    $price= '$??';
    return $price;
}
function single_not_login_css() {
    ?>
        <style>
            #dt360-addtocart .quantity {
                display: none;
            }
			#dt360-addtocart .ct-cart-actions {
				display: flex;
			}
			#dt360-addtocart .single_add_to_cart_button {
				width: 80%;
				margin: auto;
			}
			
        </style>
    <?php
}

//add_action( 'init', 'wwp_hide_price_add_cart_not_logged_in' );
function wwp_hide_price_add_cart_not_logged_in() {
    if ( !is_user_logged_in() ) {
        remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
        //remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
        //remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
        //remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
        //add_action( 'woocommerce_single_product_summary', 'wwp_print_login_to_see', 31 );
        add_action( 'woocommerce_after_shop_loop_item', 'wwp_print_login_to_see', 11 );
    }
}

add_action( 'woocommerce_before_shop_loop_item_title', 'price_shop_item_loop', 10, 0 );  
function price_shop_item_loop() { 
	add_filter( 'woocommerce_get_price_html', 'change_product_price_display' );
	//add_filter( 'woocommerce_cart_item_price', 'change_product_price_display' );
}; 
function change_product_price_display( $price ) {
	$member_status = wc_memberships_is_user_active_member( get_current_user_id(), 'only-for-member' );
	$terms = get_the_terms( get_the_ID(), 'product_cat' );
		if($terms) {
			foreach ($terms as $term) {
				$product_cat = $term->slug;
				if( $product_cat == "biologique-recherche" ) {
					if(is_user_logged_in()) {
						if ( !$member_status ) {
							$price = '';
						} 
					}
					else {
						$price = '';
					}
					
				}
				if( $product_cat == "environ" && !is_user_logged_in() ) {
					$price = '';
				}
			}
		}

    return $price;
}

add_filter( 'woocommerce_add_to_cart_validation', 'add_to_cart_validation', 10, 3 );
function add_to_cart_validation ( $passed, $product_id, $quantity ) {
	$member_status = wc_memberships_is_user_active_member( get_current_user_id(), 'only-for-member' );
	$terms = get_the_terms( $product_id, 'product_cat' );
	if($terms) {
		foreach ($terms as $term) {
			$product_cat = $term->slug;
			if($product_cat == "biologique-recherche") {
				if( is_user_logged_in() ) { 
					if( !$member_status ) {
						wp_safe_redirect('/biologique-recherche-contact-page');
						exit();
					}
				}
				else{
					wp_safe_redirect( get_permalink(wc_get_page_id('myaccount')) );
					exit();
				}
			}
			if( $product_cat == "environ" && !is_user_logged_in() ) {
				wp_safe_redirect( get_permalink(wc_get_page_id('myaccount')) );
				exit();
			}
		}
	}

	return $passed;
}

function wwp_print_login_to_see() {
    echo '<a class="button btn-not-login" href="' . get_permalink(wc_get_page_id('myaccount')) . '">' . __('Login to See Prices', 'theme_name') . '</a>';
}
// END code hide prices for guest

// To change add to cart text on single product page
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_single_add_to_cart_text' ); 
function woocommerce_custom_single_add_to_cart_text() {
    return __( 'Add to Bag', 'woocommerce' ); 
}

// To change add to cart text on product archives(Collection) page
add_filter( 'woocommerce_product_add_to_cart_text', 'woocommerce_custom_product_add_to_cart_text' );  
function woocommerce_custom_product_add_to_cart_text() {
		global $product;
		$text = $product->is_purchasable() ? __( 'Add to Bag', 'woocommerce' ) : __( 'Read more', 'woocommerce' );
	return $text;
}
// end -  add to cart change text


// Start - Hide price on top gold nav
add_action('wp_head', 'checkout_page' );
function checkout_page() {
	if( !is_page( wc_get_page_id( 'checkout' ) ) ) {
		echo '<style> #dt360-gold-nav .elementor-menu-cart__toggle .woocommerce-Price-amount { display: none; } </style>';
	}
}
// End - Hide price on top gold nav

// Start - Change View Cart Text
add_filter( 'gettext', function( $translated_text ) {
    if ( 'View cart' === $translated_text ) {
        $translated_text = 'View Bag';
    }
    return $translated_text;
} );
// End - Change View Cart Text


// Start - margin top gold nav when login
add_action('wp_head', 'gold_nav' );
function gold_nav() {
	if ( is_user_logged_in() ) {
		echo '<style> @media (max-width: 768px) { section.elementor-sticky--active#dt360-gold-nav { top: 45px; } } </style>';
	}
}
// End - - margin top gold nav when login

function debug_console($var) {
	echo '<script>console.log(' .json_encode( $var ) .')</script>';
}

// Start - Shop Product Brand 
add_action( 'woocommerce_before_shop_loop_item_title', 'product_brand_shop_item', 10, 0 );  
function product_brand_shop_item() { 
	$terms = get_the_terms( get_the_ID(), 'product_cat' );
	if($terms) {
		foreach ($terms as $term) {
         	$product_cat = $term->name;
			if($term->parent == 157) {
				echo '<h2 id="product-brand-shop">'.$product_cat.'</h2>';
			}
         }
	}
};
// End - Shop Product Brand 

// Start - Change Cart Totals Text
add_filter( 'gettext', 'change_cart_totals_text', 20, 3 );
function change_cart_totals_text( $translated, $text, $domain ) {
    if( is_cart() && $translated == 'Cart totals' ){
        $translated = __('Bag Totals', 'woocommerce');
    }
    return $translated;
}
// End - Change Cart Totals Text

//Start - Featured image media logo
if (!is_admin()) {
	add_filter( 'wp_get_attachment_image_src', 'featured_press_thumbnail', 10, 4);
}
function featured_press_thumbnail( $image, $attachment_id, $size, $icon ) {
		global $post;
		//$parent = get_post_ancestors( $attachment_id );
		//$id = (int) $parent[0];
		// $medialogo_meta_id = get_post_meta( $id, 'media_logo', true);
		$medialogo_meta_id = get_field('media_logo', $post->ID, false);
		$media_logo_url = wp_get_attachment_url( $medialogo_meta_id );
		if ( !empty($media_logo_url) && !is_single() ) {
			 $image[0] = $media_logo_url;
		}
		return $image;
}
//End - Featured image media logo

//Start - product archive show per page
add_filter( 'loop_shop_per_page', 'new_loop_shop_per_page', 20 );
function new_loop_shop_per_page( $cols ) {
  // $cols contains the current number of products per page based on the value stored on Options –> Reading
  // Return the number of products you wanna show per page.
  $cols = 99999999;
  return $cols;
}
//End - product archive show per page 

//Start - add to cart message
add_filter( 'wc_add_to_cart_message', 'add_to_cart_message', 10, 2 ); 
function add_to_cart_message( $message, $product_id ) { 
    $message = sprintf(esc_html__('« %s » has been added by to your bag.','woocommerce'), get_the_title( $product_id ) ); 
    return $message; 
}
//End - add to cart message

//Start - empty cart message
remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
add_action( 'woocommerce_cart_is_empty', 'custom_empty_cart_message', 10 );
function custom_empty_cart_message() {
    $html  = '<p class="cart-empty woocommerce-info">';
    $html .= wp_kses_post( apply_filters( 'wc_empty_cart_message', __( 'Your bag is currently empty.', 'woocommerce' ) ) );
    echo $html . '</p></div>';
}
//End -empty cart message

//Start - replace howdy text
add_filter( 'admin_bar_menu', 'replace_wordpress_howdy', 25 );
function replace_wordpress_howdy( $wp_admin_bar ) {
	$my_account = $wp_admin_bar->get_node('my-account');
	$newtext = str_replace( 'Howdy,', 'Hello,', $my_account->title );
	$wp_admin_bar->add_node( array(
	'id' => 'my-account',
	'title' => $newtext,
	) );
}
//End - replace howdy text

//disable update specific plugin
 function disable_plugin_updates( $value ) {

    $pluginsToDisable = [
        'essential-addons-for-elementor-lite/essential_adons_elementor.php',
        // 'plugin-folder2/plugin2.php'
    ];

    if ( isset($value) && is_object($value) ) {
        foreach ($pluginsToDisable as $plugin) {
            if ( isset( $value->response[$plugin] ) ) {
                unset( $value->response[$plugin] );
            }
        }
    }
    return $value;
}
add_filter( 'site_transient_update_plugins', 'disable_plugin_updates' );
?>
