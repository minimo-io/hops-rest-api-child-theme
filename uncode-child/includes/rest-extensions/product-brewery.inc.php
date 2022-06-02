<?php
/// Add brewery data to the product response

add_filter('woocommerce_rest_prepare_product_object', 'hops_extend_product_response_add_brewery', 10, 3); 

function hops_extend_product_response_add_brewery($response, $object, $request){
    if (empty($response->data)) return $response;

    $postId = $object->get_id(); //it will fetch product id
    $userId = $request->get_param( 'userId' );
    $postQueryId = $request->get_param( 'id' );

    // this whatsapp thing must be removed and included in the brewery stuff below.
    $breweryFromThisPostId = $postId;
    if ($postQueryId) $breweryFromThisPostId = $postQueryId;

    $brewery = get_field("brewery", $breweryFromThisPostId);
    $response->data['brewery_whatsapp'] = get_field("whatsapp", $brewery->ID);

    /*
    $response->data['breweryX'] = Array(
        "ID": 103817,
        "post_date_gmt": "2022-05-31 17:54:44",
        "post_modified_gmt": "2022-05-31 18:53:57",
        "post_title": "Dabier Brewery",
        "post_excerpt": "Guiada por un viejo espíritu, Dabier llegó en el 2021 a purificar el mercado de la cerveza artesanal uruguayo ofreciendo productos enfocados en la calidad que se diferencien de otras marcas por su tomabilidad y suavidad, convirtiéndose en el mejor producto de entrada a la cervecería artesanal.",
        "brewery_image": "https://hops.uy/wp-content/uploads/2022/05/dabier-brewery-logo.png",

        required this.id,
        required this.avatar,
        required this.name,
        required this.location, 
        required this.followers,
        required this.beersCount,
        required this.bgColor,
        required this.description,
        required this.scoreAvg,
        required this.scoreCount,
    );
        $response->data['brewery_whatsapp'] = get_field("whatsapp", $brewery->ID);
    */
    return $response;
}



?>