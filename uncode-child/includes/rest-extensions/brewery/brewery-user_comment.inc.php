<?php
// IMPORTANT NOTE: Due to an decision mistake on the origin, breweries are type of page
// this should CHANGE in the future, and become a Custom Post Type
// with a Custom Endpoint

// For the moment they are modified using the register_rest_field

add_action( 'rest_api_init', function () {

    // add user_comment value to breweries (pages)
    register_rest_field( 'page', 'user_comment', array(
        'get_callback' => function( $page, $fieldName, $request, $objectType ) {
            //global $wp_rest_additional_fields;
            $userId = $request->get_param("userId");

            // check if user exists
            $user = get_user_by("id", $userId); // check if user exists
            if (!$user) return Array();

            $comments = hops_add_user_comment_to_product_response($userId, $page["id"]);

            if( empty($comments) ) return Array();

            return $comments;
            /*
            $comment_obj = get_comment( $page['user_id'] );
            return (array) $comment_obj;
              */
        },
        'update_callback' => function( $user_comment, $comment_obj ) {
            return true;
        },
        'schema' => array(
            'description' => __( 'User comment.' ),
            'type'        => 'string'
        ),
    ) );


} );

?>