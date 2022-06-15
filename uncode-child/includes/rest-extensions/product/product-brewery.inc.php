<?php
/// Add brewery data to the product response
/// single and loop

add_filter('woocommerce_rest_prepare_product_object', 'hops_extend_product_response_add_brewery', 10, 3); 


function hops_extend_product_response_add_brewery($response, $object, $request){
    if (empty($response->data)) return $response;

    $postId = $object->get_id(); //it will fetch product id
    $userId = $request->get_param( 'userId' );
    $postQueryId = $request->get_param( 'id' );

    $breweryFromThisPostId = $postId;
    if ($postQueryId) $breweryFromThisPostId = $postQueryId;
    $brewery = get_field("brewery", $breweryFromThisPostId);

    // add brewery data
    $response->data['brewery'] = hops_build_product_brewery_response($brewery); 

    return $response;
}


?>