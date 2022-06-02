<?php
// IMPORTANT NOTE: Due to an decision mistake on the origin, breweries are type of page
// this should CHANGE in the future, and become a Custom Post Type
// with a Custom Endpoint

// For the moment they are modified using the register_rest_field


add_action( 'rest_api_init', function () {

    // add scores to breweries
    register_rest_field( 'page', 'scores', array(
        'get_callback' => function( $page ) {

            $postScores = hm_post_scores( $page["id"] );
            return Array(
              'opinionCount' => $postScores["opinionCount"],
              'opinionScore' => $postScores["opinionScore"],
            );

        },
        'update_callback' => function( $scores, $comment_obj ) {
            return true;
        },
        'schema' => array(
            'description' => __( 'Post score.' ),
            'type'        => 'string'
        ),
    ) );


} );

?>