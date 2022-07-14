<?php
/// Add order status to products


// New order status AFTER woo 2.2
add_action( 'init', 'hops_register_delivering_status' );
add_filter( 'wc_order_statuses', 'hops_delivering_order_status' );

function hops_register_delivering_status() {
    register_post_status( 'wc-delivering', array(
        'label'                     => _x( 'Delivering', 'Order status', 'woocommerce' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Delivering <span class="count">(%s)</span>', 'Delivering<span class="count">(%s)</span>', 'woocommerce' )
    ) );
}



// Register in wc_order_statuses.
function hops_delivering_order_status( $order_statuses ) {
    $order_statuses['wc-delivering'] = _x( 'Delivering', 'Order status', 'woocommerce' );

    return $order_statuses;
}

?>