<?php
/// Add brewery data to the order response

// add_filter('woocommerce_rest_prepare_product_object', 'hops_extend_product_response_add_scores', 10, 3); 
add_filter( "woocommerce_rest_prepare_shop_order_object", "hops_extend_order_response_add_brewery", 10, 3 );

function hops_extend_order_response_add_brewery($response, $object, $request){
    if (empty($response->data)) return $response;

    $orderId = $object->get_id(); //it will fetch order id
    $order = wc_get_order( $orderId );
    $a_orderData = Array();
    if ($order){

        $firstProductId = false;
        // Get and Loop Over Order Items
        foreach ( $order->get_items() as $item_id => $item ) {
            $firstProductId = $item->get_product_id();

            break;
        }

        if ($firstProductId){
            // get brewery
            $brewery = get_field("brewery", $firstProductId );
            $a_orderData = hops_build_product_brewery_response($brewery);
        }
       
        $response->data['brewery'] = $a_orderData;
    }

    // $userId = $request->get_param( 'userId' );
    // $postQueryId = $request->get_param( 'id' );

    // $brewery = get_post(get_the_ID());

    // // add brewery data
    // return hops_build_product_brewery_response($brewery);


    // $response->data['brewery'] = hops_build_product_brewery_response($brewery);
    $response->data['brewery'] = $a_orderData;

    return $response;
}



?>