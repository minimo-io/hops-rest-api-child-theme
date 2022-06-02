<?php
/// Add brewery data to the product response

add_filter('woocommerce_rest_prepare_product_object', 'hops_extend_product_response_add_scores', 10, 3); 

function hops_extend_product_response_add_scores($response, $object, $request){
    if (empty($response->data)) return $response;

    $postId = $object->get_id(); //it will fetch product id
    $userId = $request->get_param( 'userId' );
    $postQueryId = $request->get_param( 'id' );

    $postScores = hm_post_scores($postId);
    $response->data['scores'] = Array(
      'opinionCount' => $postScores["opinionCount"],
      'opinionScore' => $postScores["opinionScore"]
    );

    return $response;
}



?>