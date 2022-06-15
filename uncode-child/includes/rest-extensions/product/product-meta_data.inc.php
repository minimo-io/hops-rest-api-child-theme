<?php
/// Add brewery data to the product response
/// single and loop

add_filter('woocommerce_rest_prepare_product_object', 'hops_extend_product_response_remove_meta_data', 10, 3); 


function hops_extend_product_response_remove_meta_data($response, $object, $request){
    if (empty($response->data)) return $response;

    // add brewery data
    $response->data['meta_data'] = null;

    return $response;
}


?>