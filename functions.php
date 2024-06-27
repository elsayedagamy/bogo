<?php

add_action('wp_enqueue_scripts', 'salient_child_enqueue_styles', 100);

function salient_child_enqueue_styles() {
    $nectar_theme_version = nectar_get_theme_version();
    wp_enqueue_style('salient-child-style', get_stylesheet_directory_uri() . '/style.css', '', $nectar_theme_version);

    if (is_rtl()) {
        wp_enqueue_style('salient-rtl', get_template_directory_uri() . '/rtl.css', array(), '1', 'screen');
    }
}

/**
 * Ensure only one quantity is added to the cart
 */
add_filter('woocommerce_add_to_cart_quantity', 'set_single_quantity', 10, 2);

/**
 * Set the quantity of the product being added to the cart to one.
 *
 * @param int $quantity The quantity to be added.
 * @param int $product_id The ID of the product being added.
 * @return int Always returns 1 to set the quantity to one.
 */
function set_single_quantity($quantity, $product_id) {
    return 1; // Always set quantity to 1
}

/**
 * Apply BOGO discount to the least expensive items in the cart.
 */
add_action('woocommerce_cart_calculate_fees', 'apply_bogo_discount', 10, 1);

/**
 * Calculate and apply the BOGO discount.
 *
 * @param WC_Cart $cart The WooCommerce cart object.
 */
function apply_bogo_discount($cart) {
    // Prevent applying discount in admin or during AJAX requests
    if (is_admin() && !defined('DOING_AJAX')) return;

    // Initialize an array to hold item prices
    $items_prices = array();

    // Loop through cart items and gather prices
    foreach ($cart->get_cart() as $cart_item) {
        for ($i = 0; $i < $cart_item['quantity']; $i++) {
            $items_prices[] = floatval($cart_item['data']->get_price());
        }
    }

    // Get the number of items in the cart
    $num_items = count($items_prices);

    // If there are less than 2 items, do not apply the discount
    if ($num_items < 2) return;

    // Sort the item prices in ascending order
    sort($items_prices);

    // Initialize the discount amount
    $discount = 0;

    // Calculate the discount by summing up the prices of the least expensive items
    for ($i = 0; $i < floor($num_items / 2); $i++) {
        $discount += $items_prices[$i];
    }

    // If there is a discount, apply it to the cart
    if ($discount > 0) {
        // Clear previous notices and add a new one
        wc_clear_notices();
        wc_add_notice(__("Buy One Get One Free: Discount Applied!"), 'notice');

        // Apply the discount as a fee to the cart
        $cart->add_fee(__("BOGO Discount"), -$discount, true);
    }
}





?>
