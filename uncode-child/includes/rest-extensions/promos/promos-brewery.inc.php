<?php
// IMPORTANT NOTE: Due to an decision mistake on the origin, breweries are type of page
// this should CHANGE in the future, and become a Custom Post Type
// with a Custom Endpoint

// For the moment they are modified using the register_rest_field

add_action( 'rest_api_init', function () {

    // add scores to breweries
    register_rest_field( 'promos', 'brewery_associated', array(
        'get_callback' => function( $promo ) {

            // get brewery object (page)
            $brewery = get_field("brewery_associated", $promo["id"]);
            // return $promo["id"];
            if ($brewery){
                // add brewery data
                return hops_build_product_brewery_response($brewery);
            }else{
                return [];
            }


        },
        'update_callback' => function( $scores, $comment_obj ) {
            return true;
        },
        'schema' => array(
            'description' => __( 'Page brewery.' ),
            'type'        => 'string'
        ),
    ) );


} );



?>