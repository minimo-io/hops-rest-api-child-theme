<?php
/// Add brewery data to the product response

add_filter('woocommerce_rest_prepare_product_object', 'hops_extend_product_response_add_user_comment', 10, 3); 

function hops_extend_product_response_add_user_comment($response, $object, $request){
    if (empty($response->data)) return $response;

    $postId = $object->get_id(); //it will fetch product id
    $userId = $request->get_param( 'userId' );
    $postQueryId = $request->get_param( 'id' );

    $response->data['user_comment'] = Array(); // init empty

    
    $comments = hops_add_user_comment_to_product_response($userId, $postId);
    if( empty($comments) ) return $response; // <<<<<<<<<< WATCH
    $response->data['user_comment'] = $comments;


    return $response;
}



?>