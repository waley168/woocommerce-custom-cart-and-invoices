<?php
/**
 * linkinmall Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package linkinmall
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_LINKINMALL_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'linkinmall-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_LINKINMALL_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

function ueda_add_custom_option(){
    global $product;
	switch ($product->get_id()) {
   case 1381:
     echo "<div id='calendar1' class='calendar-wrap'></div>";
     break;
   case 1592:
     echo "<div id='calendar2' class='calendar-wrap'></div>";
     break;
   case 1596:
     echo "<div id='calendar3' class='calendar-wrap'></div>";
     break;
	}
}
add_action( 'woocommerce_before_add_to_cart_button', 'ueda_add_custom_option');

function ueda_add_note_to_cart( $cart_item_data, $product_id, $variation_id ) {
    $bookingDate = filter_input( INPUT_POST, 'bookingDate' );
	$bookingTime = filter_input( INPUT_POST, 'optTime' );
	$adultCounter = filter_input( INPUT_POST, 'adultCounter' );
	$childrenCounter = filter_input( INPUT_POST, 'childrenCounter' );
	$babyCounter = filter_input( INPUT_POST, 'babyCounter' );

    $cart_item_data['bookingDate'] = $bookingDate;
	$cart_item_data['bookingTime'] = $bookingTime;
	$cart_item_data['adultCounter'] = $adultCounter;
	$cart_item_data['childrenCounter'] = $childrenCounter;
	$cart_item_data['babyCounter'] = $babyCounter;

    return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'ueda_add_note_to_cart', 10, 3 );

function ueda_get_note_item_data( $item_data, $cart_item ) {

		$item_data[] = array(
			'key' => __( '日期', 'ueda' ),
			'display' => wc_clean($cart_item['bookingDate'])
		);
		$item_data[] = array(
			'key' => __( '時間', 'ueda' ),
			'display' => wc_clean($cart_item['bookingTime'])
		);
	if ( $cart_item['adultCounter'] > 0 ){
		$item_data[] = array(
			'key' => __( '成人', 'ueda' ),
			'display' => wc_clean($cart_item['adultCounter'])
		);
	}
	if ( $cart_item['childrenCounter'] > 0 ){
		$item_data[] = array(
			'key' => __( '兒童', 'ueda' ),
			'display' => wc_clean($cart_item['childrenCounter'])
		);
	}
	if ( $cart_item['babyCounter'] > 0 ){
		$item_data[] = array(
			'key' => __( '幼兒', 'ueda' ),
			'display' => wc_clean($cart_item['babyCounter'])
		);
	}
	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'ueda_get_note_item_data', 10, 2 );

// Save cart item custom meta as order item meta data and display it everywhere on orders and email notifications.

add_filter( 'woocommerce_add_cart_item_data', 'cxc_save_custom_fields_data_to_cart', 10, 2 );
function cxc_save_custom_fields_data_to_cart( $cart_item_data, $product_id ) {

	if( isset( $_POST['totalMoney'] ) && ! empty( $_POST['totalMoney'] )  ) {
        // Set the custom data in the cart item
		$cart_item_data['totalMoney'] = (float) sanitize_text_field( $_POST['totalMoney'] );

        // Make each item as a unique separated cart item
		$cart_item_data['unique_key'] = md5( microtime().rand() );
	}

	return $cart_item_data;
}

add_action( 'woocommerce_before_calculate_totals', 'cxc_change_cart_item_price', 99, 1 );
function cxc_change_cart_item_price( $cart ) {

	if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) ){  		
		return;
	}

	if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ){
		return;
	}
    // Loop through cart items
	foreach ( $cart->get_cart() as $cart_item ) {
        // Set the new price  		
		if( isset($cart_item['totalMoney']) ){
			$cart_item['data']->set_price( $cart_item['totalMoney'] );
		}
	}
}

function wpdocs_selectively_enqueue_admin_script( $hook ) {
    if ( 'edit.php' != $hook ) {
        return;
    }
    wp_enqueue_script( 'my_custom_script', 'https://www.linkinmall.tw/wp-content/themes/linkinmall/shop_order_sortable.js', array(), null, true);
	wp_enqueue_style( 'my_custom_css', 'https://www.linkinmall.tw/wp-content/themes/linkinmall/shop_order_css.css', array(), null, false);
}
add_action( 'admin_enqueue_scripts', 'wpdocs_selectively_enqueue_admin_script' );

// ADDING 2 NEW COLUMNS WITH THEIR TITLES (keeping "Total" and "Actions" columns at the end)
add_filter( 'manage_edit-shop_order_columns', 'custom_shop_order_column', 20 );
function custom_shop_order_column($columns)
{
    $reordered_columns = array();
	$column01 = '方案';
	$column02 = '日期';
	$column03 = '時間';
	$column04 = '成人';
	$column05 = '兒童';
	$column06 = '幼兒';

    // Inserting columns to a specific location
    foreach( $columns as $key => $column){
        $reordered_columns[$key] = $column;
        if( $key ==  'order_status' ){
            // Inserting after "Status" column
            $reordered_columns['my-column01'] = __( $column01,'theme_domain');
			$reordered_columns['my-column02'] = __( $column02,'theme_domain');
			$reordered_columns['my-column03'] = __( $column03,'theme_domain');
			$reordered_columns['my-column04'] = __( $column04,'theme_domain');
			$reordered_columns['my-column05'] = __( $column05,'theme_domain');
			$reordered_columns['my-column06'] = __( $column06,'theme_domain');
        }
    }
    return $reordered_columns;
}

// Adding custom fields meta data for each new column (example)
add_action( 'manage_shop_order_posts_custom_column' , 'custom_orders_list_column_content', 20, 2 );
function custom_orders_list_column_content( $column, $post_id )
{
    switch ( $column )
    {
        case 'my-column01' :
            $order = wc_get_order( $post_id );
            foreach ($order->get_items() as $item_id => $item ) {
				$product_name   = $item->get_name(); // Get the item name (product name)
			}
            if(!empty($product_name))
                echo $product_name;
            break;
		case 'my-column02' :
            $my_var_one = get_post_meta( $post_id, 'billing_datetime', true );
            if(!empty($my_var_one))
                echo $my_var_one;
            break;
		case 'my-column03' :
            $my_var_two = get_post_meta( $post_id, 'billing_timeget', true );
            if(!empty($my_var_two))
                echo $my_var_two;
            break;
		case 'my-column04' :
            $my_var_three = get_post_meta( $post_id, 'billing_adult', true );
            if(!empty($my_var_three))
                echo $my_var_three;
            break;
		case 'my-column05' :
            $my_var_four = get_post_meta( $post_id, 'billing_children', true );
            if(!empty($my_var_four))
                echo $my_var_four;
            break;
		case 'my-column06' :
            $my_var_five = get_post_meta( $post_id, 'billing_baby', true );
            if(!empty($my_var_five))
                echo $my_var_five;
            break;
    }
}