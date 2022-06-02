<?php
// IMPORTANT NOTE: Due to an decision mistake on the origin, breweries are type of page
// this should CHANGE in the future, and become a Custom Post Type
// with a Custom Endpoint

// For the moment they are modified using the register_rest_field

add_action( 'rest_api_init', function () {

    // add scores to breweries
    register_rest_field( 'page', 'brewery', array(
        'get_callback' => function( $page ) {

            // get brewery object
            $brewery = get_post(get_the_ID());
            // add brewery data
            return hops_build_product_brewery_response($brewery);

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