<?php
// add the data promo object to every promo
add_action( 'rest_api_init', function () {

    
    // add scores to breweries
    register_rest_field( 'promos', 'data', array(
        'get_callback' => function( $promo ) {

            return hops_build_promo_response($promo["id"]);

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