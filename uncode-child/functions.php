<?php
// Hops customizations for Uncode.

// Enable the option show in rest
add_filter( 'acf/rest_api/field_settings/show_in_rest', '__return_true' );
// Enable the option edit in rest
add_filter( 'acf/rest_api/field_settings/edit_in_rest', '__return_true' );
add_action('after_setup_theme', 'uncode_language_setup');
remove_filter('the_excerpt', 'wpautop');
add_filter( 'wp_rest_cache/allowed_endpoints', 'hm_add_app_manifest_endpoint', 10, 1 );
add_filter('woocommerce_short_description', 'hm_woocommerce_short_description',10, 1);
add_action( 'rest_api_init', function () {
  // get beers by brewery
  register_rest_route( 'hops/v1', '/beers/breweryID/(?P<breweryID>[^/]+)', array(
     'methods' => 'GET',
     'callback' => 'hm_get_beers_by_brewery_id',
   ) );
   // get beers that are trending
   // the ones that received the best 5 or 4 stars.
   register_rest_route( 'hops/v1', '/beers/trending', array(
      'methods' => 'GET',
      'callback' => 'hm_get_beers_trending',
    ) );
    // get most voted beers
    register_rest_route( 'hops/v1', '/beers/mostVoted', array(
       'methods' => 'GET',
       'callback' => 'hm_get_beers_most_voted',
     ) );
     // get premium beers
     register_rest_route( 'hops/v1', '/beers/premium', array(
        'methods' => 'GET',
        'callback' => 'hm_get_beers_premium',
      ) );

   // set user preferences
   register_rest_route( 'hops/v1', '/updateUser', array(
      'methods' => Array('POST'),
      'callback' => 'hm_set_user_preferences_by_user_id',
    ) );

    // get user preferences custom fields
    register_rest_route( 'hops/v1', '/getUser/userID/(?P<userId>[\d]+)/(?P<type>[a-zA-Z0-9_-]+)', array(
       'methods' => 'GET',
       'callback' => 'hm_get_user_preferences_from_id',
     ) );

     // update comment for beer or brewery
     register_rest_route( 'hops/v1', '/updateComment', array(
        'methods' => Array('POST'),
        'callback' => 'hm_add_edit_user_comment',
      ) );


});
add_action("admin_init", "hm_beer_count_sync");
add_action('save_post', 'hm_refreshCache', 1); // clear REST cache after save
add_filter( 'avatar_defaults', 'hm_modify_default_avatar' ); // change default avatar

add_action( 'user_register', 'hm_define_displayname' );
add_action( 'profile_update', 'hm_define_displayname' );
add_filter('duplicate_comment_id', '__return_false'); // allow duplicate comments
add_filter('woocommerce_rest_prepare_product_object', 'hops_extend_product_response', 10, 3); // extend product response
// add extra field (author comment) for pages
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

// basic product filtering function
function hm_get_beers_base_iteration($args, $data){

     $p = wc_get_products($args);
     $products = array();
     $userId = (isset($data["userId"]) ? $data["userId"] : "0" );

     foreach ($p as $product) {
         $a_product = $product->get_data();
         $comments = hops_add_user_comment_to_product_response($userId, $a_product["id"]);


         $a_product["featured_image"] = hm_get_image_src($a_product["image_id"]);
         $a_product['user_comment'] = $comments; //try to add user comment for product if any

         if (class_exists('ACF')) $a_product["bg_color"] = get_field("bg_color", $a_product["id"]);

         $products[] = $a_product;


     }
     return new WP_REST_Response($products, 200);

}

// those beers that have been voted last
function hm_get_beers_trending(){
  return false;
}

function hm_get_beers_most_voted($data){

  $args = wp_parse_args( $args, array(
      'limit' => 10,
      'page' => 1,
      'status' => array( 'publish' ),
      /*
      'meta_key' => 'YOUR_FIELD_ID',
      'meta_value' => array('yes'), //'meta_value' => array('yes'),
      'meta_compare' => 'IN', //'meta_compare' => 'NOT IN'
      */
      'orderby' => 'meta_value_num', //'meta_compare' => 'NOT IN'
      'order' => 'desc', //'meta_compare' => 'NOT IN'
      'meta_key' => 'score', //'meta_compare' => 'NOT IN'
   ) );

   return hm_get_beers_base_iteration($args, $data);

}

function hops_add_user_comment_to_product_response($userId, $postId){
  // check if user exists
  $user = get_user_by("id", $userId); // check if user exists
  if (!$user) return Array();

  $comment = get_comments(array(
    'user_id' => $userId, // use user_id
    'post_id' => $postId
  ));

  if( empty($comment) ) return Array();

  $comments = (array)$comment;
  $newCommentArray = Array();

  foreach ( $comments as $commentKey => $commentObj ) {
    $comment_rating = get_field("score", $commentObj);
    $commentObj = (array)$commentObj;
    $commentObj["comment_rating"] = $comment_rating;
    $newCommentArray[] = (object)$commentObj;

  }
  //var_dump($newCommentArray);die();
  return $newCommentArray;
}

// extend product response to add for example if user has commented.
function hops_extend_product_response($response, $object, $request) {
    if (empty($response->data))
        return $response;

    $response->data['user_comment'] = Array(); // init empty
    $response->data['scores'] = Array(
      'opinionCount' => 0,
      'opinionScore' => 0.0
    );
    $postId = $object->get_id(); //it will fetch product id
    $userId = $request->get_param( 'userId' );

    $comments = hops_add_user_comment_to_product_response($userId, $postId);

    if( empty($comments) ) return $response;

    $postScores = hm_post_scores($postId);

    $response->data['user_comment'] = $comments;
    $response->data['scores'] = Array(
      'opinionCount' => $postScores["opinionCount"],
      'opinionScore' => $postScores["opinionScore"]
    );

    return $response;
}

/// get product score (count + avg score)
function hm_post_scores($postId){

  $comments = get_comments( Array(
    "post_id" => $postId
  ) );
  $opinionCount = 0;
  $opinionScore = 0.0;
  foreach ( $comments as $comment ) {
      $commentScore = get_field("score", $comment);
      if ($commentScore){
        $opinionScore += $commentScore;
      }

      $opinionCount++;
  }
  $scoreFactor = $opinionScore;
  if ($opinionScore > 0) $scoreFactor = $opinionScore / $opinionCount;

  return Array(
    'opinionCount' => $opinionCount,
    'opinionScore' => round($scoreFactor , 2),

  );

}

function hm_add_edit_user_comment($data){

  $ret = Array();
  $data = $data->get_params();
  //if (!isset($data["updateType"])) return new WP_Error( 'no_update_type', 'Please provide an updateType to specify the preference type to update', array( 'status' => 404 ) );
  if (!isset($data["userId"])) return new WP_Error( 'no_user_id', 'Please provide a userId param', array( 'status' => 404 ) );
  if (!isset($data["postId"])) return new WP_Error( 'no_post_id', 'Please provide a postId param', array( 'status' => 404 ) );

  // check if user exists
  $user = get_user_by("id", $data["userId"]);
  if (!$user) return new WP_Error( 'user_not_founded', 'userId not founded in database', array( 'status' => 404 ) );

  $agent = $_SERVER['HTTP_USER_AGENT'];
  $userComment = $data["userComment"];

  if (
    isset($user->data->display_name)
    && isset($user->data->user_email)
  ){
    $commentData = array(
        'user_id' => $data["userId"],
        'comment_post_ID' => $data["postId"],
        'comment_author' => $user->data->display_name,
        'comment_author_email' => $user->data->user_email,
        'comment_content' => $userComment,
        'comment_author_IP' => hops_get_the_user_ip(),
        'comment_agent' => $agent,
        'comment_date' => date('Y-m-d H:i:s'),
        'comment_date_gmt' => date('Y-m-d H:i:s'),
        'comment_approved' => 1,
    );

    if ( !isset($data["commentId"]) || $data["commentId"] == "0" ){

      //$comment_id = wp_insert_comment($data);
      $comment_id = wp_new_comment($commentData);
      if ($comment_id){
        // update comment score
        if ($data["rating"] && $data["rating"] != 0) update_field( 'score', $data["rating"], get_comment($comment_id) );
        // update comment status
        wp_set_comment_status($comment_id, 'approve');

        // get post score
        $postScores = hm_post_scores($data["postId"]);
        // generate return values
        $ret = Array();
        $ret["result"] = true;
        $ret["data"] = Array(
          "comment" => get_comment($comment_id),
          "add_or_edit" => "add"
        );
        $ret["opinionCount"] = $postScores["opinionCount"];
        $ret["opinionScore"] = $postScores["opinionScore"];

        // update post custom field to hold those values
        update_field( 'score', $postScores["opinionScore"], $data["postId"]);
        update_field( 'score_count', $postScores["opinionCount"], $data["postId"]);

        hm_refreshCache($data["postId"]);
        return new WP_REST_Response($ret, 200);

      }else{
        return new WP_Error( 'error_inserting_comment', 'Error after trying to insert comment.', array( 'status' => 404 ) );
      }


    }else{
      // check if we are sending the propper id
      if (!isset($data["commentId"])) return new WP_Error( 'no_comment_id', 'Please provide a commentId param', array( 'status' => 404 ) );
      $comment_id = $data["commentId"];

      // add to the above comment data the id in order to edit
      $commentData['comment_ID'] = $comment_id;

      wp_update_comment( $commentData );
      // update comment score
      if ($data["rating"] && $data["rating"] != 0) update_field( 'score', $data["rating"], get_comment($comment_id) );

      // get post score
      $postScores = hm_post_scores($data["postId"]);

      // generate return values
      $ret = Array();
      $ret["result"] = true;
      $ret["data"] = Array(
        "comment" => get_comment($comment_id),
        "add_or_edit" => "edit"
      );
      $ret["opinionCount"] = $postScores["opinionCount"];
      $ret["opinionScore"] = $postScores["opinionScore"];

      // update post custom field to hold those values
      update_field( 'score', $postScores["opinionScore"], $data["postId"]);
      update_field( 'score_count', $postScores["opinionCount"], $data["postId"]);

      hm_refreshCache($data["postId"]);
      return new WP_REST_Response($ret, 200);

    }



  }else{

    return new WP_Error( 'no_user_data', 'Unknown user data', array( 'status' => 404 ) );

  }


}


function hops_get_the_user_ip() {

  if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
  //check ip from share internet
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  }elseif( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
    //to check ip is pass from proxy
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  }else{
    $ip = $_SERVER['REMOTE_ADDR'];
  }
  return apply_filters( 'wpb_get_ip', $ip );

}
function hm_define_displayname( $user_id )
{
    $data = get_userdata( $user_id );
    $firstName = get_user_meta( $user_id, 'first_name', true );
    $lastName = get_user_meta( $user_id, 'last_name', true );

    if (!empty($firstName) && !empty($lastName)) {
      $firstName = ucfirst(strtolower($firstName));
      $lastName = ucfirst(strtolower($lastName));

      $newName = $firstName." ".$lastName;

      if ($newName != $data->display_name) {
          wp_update_user( array ('ID' => $user_id, 'display_name' =>  $newName));
      }
    }


}

function hm_modify_default_avatar ($avatar_defaults) {
    $myavatar = 'https://hops.uy/wp-content/uploads/2021/06/avatar_1.png';
    // OR --> $myavatar = "https://cdn.crunchify.com/Crunchify.png";
    $avatar_defaults[$myavatar] = "Hops";
    return $avatar_defaults;
}

function hm_build_request_uri($req_uri) {
  // No filter_input, see https://stackoverflow.com/questions/25232975/php-filter-inputinput-server-request-method-returns-null/36205923.
  $request_uri = filter_var( $req_uri, FILTER_SANITIZE_URL );
  // Remove home_url from request_uri for uri's with WordPress in a subdir (like /wp).
  $request_uri = str_replace( get_home_url(), '', $request_uri );
  if ( '//' === substr( $request_uri, 0, 2 ) ) {
    $request_uri = substr( $request_uri, 1 );
  }
  $uri_parts    = wp_parse_url( $request_uri );
  $request_path = rtrim( $uri_parts['path'], '/' );

  if ( isset( $uri_parts['query'] ) && ! empty( $uri_parts['query'] ) ) {
    parse_str( $uri_parts['query'], $params );
    ksort( $params );
    $uncached_parameters = get_option( 'wp_rest_cache_uncached_parameters', [] );
    if ( $uncached_parameters ) {
      foreach ( $uncached_parameters as $uncached_parameter ) {
        if ( isset( $params[ $uncached_parameter ] ) ) {
          unset( $params[ $uncached_parameter ] );
        }
      }
    }
    $request_path .= '?' . http_build_query( $params );
  }

  //$this->request_uri = $request_path;

  return $request_path;
}


function hm_getPostCacheKeys($postID){
  global $wpdb;
  $sql = "SELECT  `cache_id`
              FROM    .`{$wpdb->prefix}wrc_relations`
              WHERE   `object_id` = %s
              LIMIT   %d";
  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
  $caches = $wpdb->get_results( $wpdb->prepare( $sql, $postID, 1 ) );

  if (!isset($caches) || empty($caches)) return false;

  $keys = Array();
  foreach ( $caches as $cache ) {

    $sql = "SELECT  `cache_key`
                FROM    `{$wpdb->prefix}wrc_caches`
                WHERE   `cache_id` = %s
                ";
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $cachesKeys = $wpdb->get_results( $wpdb->prepare( $sql, $cache->cache_id) );
    if ($cachesKeys){
      foreach ( $cachesKeys as $key ) {
        $keys[] = $key->cache_key;
      }
    }

  }

  return $keys;
}
function hm_refreshAllCache(){
  $caching = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance();

  return $caching->clear_caches(); // works but deletes EVERYTHING

}
// clear cache after a save/edit action (for WP REST Cache plugin)
function hm_refreshCache($postID){
  // If this is just a post revision, do nothing.
  if (wp_is_post_revision($postID) || wp_is_post_autosave($postID)) {
      return;
  }
  // Verify if this isn't an auto-save routine.
  // If it is that our form has not been submitted then we dont want to do anything.
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
  }


  $caching = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance();

  //$clearCacheResult = $caching->clear_caches(); // works but deletes EVERYTHING

  // //$post_type = get_post_type($postID);
  // $postCacheType = "post";
  // if ($post_type === 'page') {
  //   //$caching->delete_cache_by_endpoint("/wp-json/wp/v2/pages/"); // not working
  //   $postCacheType = "page";
  // }
  // else if ($post_type === 'product') {
  //   $postCacheType = "simple";
  //   //$caching->delete_cache_by_endpoint("wc/v3"); // not working
  //   //$caching->delete_cache_by_endpoint("hops/v1"); // not working
  // }
  $ret = false;
  $a_cacheKeys = hm_getPostCacheKeys($postID, $postCacheType);
  foreach ( $a_cacheKeys as $key ) {
    $ret = $caching->delete_cache($key);
  }
  if (!$ret) $ret = hm_refreshAllCache();
  return $ret;
}

function hm_beer_count_sync(){
  // increase beer count on product save
  if (class_exists('ACF')) add_action("acf/save_post", "hm_update_beerCount", 5);
  if (class_exists('ACF')) add_action("transition_post_status", "hm_post_unpublished", 10, 3 );

}
// increase beer count on acf product save
//function hm_update_beerCount($post_ID, $postObjectAfter, $postObjectBefore){
function hm_update_beerCount($post_ID){
  global $post;

  if (!class_exists('ACF')) return; // only if ACF plugin exists
  if (!class_exists( 'woocommerce')) return; // if no woocommerce then exit
  if ($post->post_type != 'product') return; // if not a product then avoid


  // Get previous values.
  $prev_values = get_fields( $post_ID );
  $breweryID_previous = @$prev_values["brewery"]->ID;
  $breweryID_new = $_POST['acf']['field_606f337ba3f74'];

  $current_status = get_post_status ( $postID );
  //if ($current_status != "publish" && $current_status != "auto-draft") return;
  //if ($current_status == "auto-draft") return;
  //if ($current_status == "draft") return;



  // if the previous value is empty then it is a new product creation
    // so we only need to increase in the new one
  // if on update the two values for brewery exit and are different,
    // then the user has changed the beer from one brewery to the other.
    // so we need to increase in one and decrease in the other brewery
  if ($breweryID_previous == $breweryID_new) return; // no update needed on beer count
  if (isset($_POST["post_status"]) && $_POST["post_status"] == "draft") return;

  // when we do need to do something
  if (empty($breweryID_previous)){

    // it is a new beer
    // increase beer_counts de esa brewery
    $breweryBeerCount = (get_field("beers_count", $breweryID_new) * 1) + 1;
    update_field("beers_count", $breweryBeerCount, $breweryID_new);

  }else if($breweryID_previous != $breweryID_new){

    // increase brewery count in the new one
    $breweryBeerCount = (get_field("beers_count", $breweryID_new) * 1) + 1;
    update_field("beers_count", $breweryBeerCount, $breweryID_new);
    // decrease brewery count in the previous one
    $breweryBeerCount = (get_field("beers_count", $breweryID_previous) * 1) - 1;
    update_field("beers_count", $breweryBeerCount, $breweryID_previous);
  }

}


function hm_post_unpublished($new_status, $old_status, $post){
  global $post;

  if ($old_status == "new") return; // new posts are handled above
  if ($old_status == "auto-draft") return; // new posts are handled above
  if ($old_status == "publish" && $new_status == "publish") return; // publish == publish es update y no debería tratarse acá


  if (!class_exists('ACF')) return; // only if ACF plugin exists
  if (!class_exists( 'woocommerce')) return; // if no woocommerce then exit
  if ($post->post_type != 'product') return; // if not a product then avoid

  $postCustomFields = get_fields($post->ID);
  if (!isset($postCustomFields["brewery"])) return; // no brewery configured
  $breweryID = $postCustomFields["brewery"]->ID;

    // var_dump($old_status);
    // var_dump($new_status);
    // die();
  if ( $old_status == 'publish'  &&  $new_status != 'publish' ) {
      // goto to not-publish so decrement beerCount
      $breweryBeerCount = (get_field("beers_count", $breweryID) * 1) - 1;
      update_field("beers_count", $breweryBeerCount, $breweryID);
  }
  if ( $old_status != 'publish'  &&  $new_status == 'publish' ) {
      // goto to publish so increment beerCount
      $breweryBeerCount = (get_field("beers_count", $breweryID) * 1) + 1;
      update_field("beers_count", $breweryBeerCount, $breweryID);


  }
}


function hm_woocommerce_short_description($the_excerpt) {
  $the_excerpt = strip_tags($the_excerpt);
  return wp_strip_all_tags($the_excerpt);
}
// add_action('woocommerce_single_product_summary', 'hm_woocommerce_short_description',10, 1);


/**
 * Register the endpoint so it will be cached.
 */
function hm_add_app_manifest_endpoint( $allowed_endpoints ) {
  // beers from custom end-point
	if ( ! isset( $allowed_endpoints[ 'hops/v1' ] ) || ! in_array( 'product', $allowed_endpoints[ 'hops/v1' ] ) ) {
		$allowed_endpoints[ 'hops/v1' ][] = 'beers';
	}
  // products from wc
  if ( ! isset( $allowed_endpoints[ 'wc/v3' ] ) || ! in_array( 'products', $allowed_endpoints[ 'wc/v3' ] ) ) {
      $allowed_endpoints[ 'wc/v3' ][] = 'products';
  }
	return $allowed_endpoints;
}



function hm_get_image_src( $imageID ) {
  $feat_img_array = wp_get_attachment_image_src(
    $imageID, // Image attachment ID
    'thumbnail',  // Size.  Ex. "thumbnail", "large", "full", etc..
    true // Whether the image should be treated as an icon.
  );
  return $feat_img_array[0];
}

function incrementDecrementBreweryFollowers($incrementOrDecrement = "increment", $breweryID){
  if (!$breweryID) return false;
  $followesCount = get_field("followers", $breweryID);
  if (!$followesCount) $followesCount = 0;
  if ($incrementOrDecrement == "increment") $followesCount = $followesCount + 1;
  if ($incrementOrDecrement == "decrement") $followesCount = $followesCount - 1;

  $ret = update_field("followers", $followesCount, $breweryID);
  if ($ret){
    $retCache = hm_refreshCache($breweryID);
  }
  return $ret;
}

function incrementDecrementBeerFollowers($incrementOrDecrement = "increment", $beerID){
  if (!$beerID) return false;
  $followesCount = get_field("followers", $beerID);
  if (!$followesCount) $followesCount = 0;
  if ($incrementOrDecrement == "increment") $followesCount = $followesCount + 1;
  if ($incrementOrDecrement == "decrement") $followesCount = $followesCount - 1;

  $ret = update_field("followers", $followesCount, $beerID);
  if ($ret){
    $retCache = hm_refreshCache($beerID);
  }
  return $ret;
}

function hm_set_user_preferences_by_user_id($data){
  $ret = Array();
  $data = $data->get_params();

  if (!isset($data["updateType"])) return new WP_Error( 'no_update_type', 'Please provide an updateType to specify the preference type to update', array( 'status' => 404 ) );
  if (!isset($data["userId"])) return new WP_Error( 'no_user_id', 'Please provide a userId param', array( 'status' => 404 ) );

  // check if user exists
  $user = get_user_by("id", $data["userId"]);
  if (!$user) return new WP_Error( 'user_not_founded', 'userId not founded in database', array( 'status' => 404 ) );

  /// Update beer types and news preferences
  if ($data["updateType"] == "preferences"){

    // update beer type preferences
    $beerTypes = $data["beerTypesPrefs"];
    update_field('beers_preferences', $beerTypes, 'user_'.$data["userId"]);

    // update news preferences
    $newsPrefs = $data["newsPrefs"];
    update_field('news_preferences', $newsPrefs, 'user_'.$data["userId"]);

    $ret["result"] = true;
    $ret["data"] = Array("beerTypesPrefs" => $beerTypes, "newsPrefs" => $newsPrefs);


    return new WP_REST_Response($ret, 200);
  }



  /// Update follow brewery preference
  if ($data["updateType"] == "breweriesPreferences"){

    $ret = Array('result' => false, 'http'=> 404, 'data' => 'no_action');
    $breweryID = $data["breweryId"];


    // get saved breweries string pref
    if (!isset($data["addOrRemove"])) return new WP_REST_Response( Array('result' => true, 'http'=> 404, 'data'=> 'specify add or remove'), 404);

    $breweriesPrefs = get_field('breweries_preferences', 'user_'.$data["userId"]);
    $a_breweriesPrefs = explode("|", $breweriesPrefs);

    if ($data["addOrRemove"] == "add"){
      if (empty($breweriesPrefs)) $breweriesPrefs = "";

        // if already NOT configured then add it
      if (!in_array($breweryID, $a_breweriesPrefs)){

        if ($breweriesPrefs != "") $breweriesPrefs .= "|";
        $breweriesPrefs .= $breweryID;


        $res = update_field('breweries_preferences', $breweriesPrefs, 'user_'.$data["userId"]);

        if ($res) {
          incrementDecrementBreweryFollowers("increment", $breweryID);

          $ret = Array('result' => true, 'http'=> 200, 'data'=> 'brewery_added');
        }

      }else{
         $ret = Array('result' => false, 'http'=> 404, 'data'=> 'brewery_already_exists');
      }
    }else if($data["addOrRemove"] == "remove"){

      foreach ($a_breweriesPrefs as $breweryIndex => $brewery){
        if ($brewery == $breweryID){
          array_splice($a_breweriesPrefs, $breweryIndex, 1);
          break;
        }

      }

      $newBreweriesString = implode("|", $a_breweriesPrefs);
      if ($newBreweriesString == $breweriesPrefs){
        // was not removed because there was no id configured
        $ret = Array('result' => false, 'http'=> 404, 'data'=> 'brewery_to_remove_does_not_exists');
      }else{
        $res = update_field('breweries_preferences', $newBreweriesString, 'user_'.$data["userId"]);
        if ($res){
          incrementDecrementBreweryFollowers("decrement", $breweryID);

          $ret = Array('result' => true, 'http'=> 200, 'data'=> 'brewery_removed');
        }
      }



    }




    return new WP_REST_Response($ret, $ret["http"]);
  }

  /// Update follow brewery preference
  if ($data["updateType"] == "beersFavoritesPreference"){

    $ret = Array('result' => false, 'http'=> 404, 'data' => 'no_action');
    $beerID = $data["beerId"];


    // get saved breweries string pref
    if (!isset($data["addOrRemove"])) return new WP_REST_Response( Array('result' => true, 'http'=> 404, 'data'=> 'specify add or remove'), 404);

    $beersPrefs = get_field('beers_favorites_preference', 'user_'.$data["userId"]);
    $a_beersPrefs = explode("|", $beersPrefs);

    if ($data["addOrRemove"] == "add"){
      if (empty($a_beersPrefs)) $a_beersPrefs = "";

        // if already NOT configured then add it
      if (!in_array($beerID, $a_beersPrefs)){

        if ($beersPrefs != "") $beersPrefs .= "|";
        $beersPrefs .= $beerID;


        $res = update_field('beers_favorites_preference', $beersPrefs, 'user_'.$data["userId"]);

        if ($res) {
          incrementDecrementBeerFollowers("increment", $beerID);

          $ret = Array('result' => true, 'http'=> 200, 'data'=> 'beer_added');
        }

      }else{
         $ret = Array('result' => false, 'http'=> 404, 'data'=> 'beer_already_exists');
      }
    }else if($data["addOrRemove"] == "remove"){

      foreach ($a_beersPrefs as $beerIndex => $beer){
        if ($beer == $beerID){
          array_splice($a_beersPrefs, $beerIndex, 1);
          break;
        }

      }

      $newBeersString = implode("|", $a_beersPrefs);
      if ($newBeersString == $beersPrefs){
        // was not removed because there was no id configured
        $ret = Array('result' => false, 'http'=> 404, 'data'=> 'beer_to_remove_does_not_exists');
      }else{
        $res = update_field('beers_favorites_preference', $newBeersString, 'user_'.$data["userId"]);
        if ($res){
          incrementDecrementBeerFollowers("decrement", $beerID);

          $ret = Array('result' => true, 'http'=> 200, 'data'=> 'beer_removed');
        }
      }



    }
    return new WP_REST_Response($ret, $ret["http"]);
  }
}

function hm_get_user_preferences_from_id($data){
  $userID = $data["userId"];
  $prefType = $data["type"];
  $ret = Array('result' => false, 'data' => 'no_action');

  if (!$userID) return new WP_REST_Response(Array('result' =>false, 'data' => 'no_user_id'), 404);
  if (!$prefType) return new WP_REST_Response(Array('result' =>false, 'data' => 'no_preference_type'), 404);

  if ($prefType == "breweries_preferences"){

    $outputData = get_field('breweries_preferences', 'user_'.$userID);


  }else if($prefType == "news_preferences"){

    $outputData = get_field('news_preferences', 'user_'.$userID);

  }else if($prefType == "beers_favorites_preference"){

    $outputData = get_field('beers_favorites_preference', 'user_'.$userID);

  }else{

    return new WP_REST_Response(Array('result' =>false, 'data' => 'invalid_preference_type'), 404);

  }

  if (!$outputData) $outputData = "";
  return new WP_REST_Response(Array('result' => $outputData, 'data' => 'ok_results'), 200);

  // default error
  //return new WP_REST_Response($ret, 200);

}

function hm_get_beers_premium($data){
  $args = wp_parse_args( $args, array(
      'limit' => 10,
      'page' => 1,
      'status' => array( 'publish' ),
      'meta_key' => 'is_premium',
      'meta_value' => true, //'meta_value' => array('yes'),
      'meta_compare' => '=' //'meta_compare' => 'NOT IN'  or = or IN
   ) );
   return hm_get_beers_base_iteration($args, $data);
   /*
   $p = wc_get_products($args);
   $products = array();
   $userId = (isset($data["userId"]) ? $data["userId"] : "0" );

   foreach ($p as $product) {
       $a_product = $product->get_data();
       $comments = hops_add_user_comment_to_product_response($userId, $a_product["id"]);


       $a_product["featured_image"] = hm_get_image_src($a_product["image_id"]);
       $a_product['user_comment'] = $comments; //try to add user comment for product if any

       if (class_exists('ACF')) $a_product["bg_color"] = get_field("bg_color", $a_product["id"]);

       $products[] = $a_product;


   }
   return new WP_REST_Response($products, 200);
   */


}

function hm_get_beers_by_brewery_id( $data ) {

  $args = wp_parse_args( $args, array(
      'limit' => -1,
      'status' => array( 'publish' ),
      'meta_key' => 'brewery',
      'meta_value' => $data['breweryID'], //'meta_value' => array('yes'),
      'meta_compare' => '=' //'meta_compare' => 'NOT IN'  or = or IN
   ) );

   return hm_get_beers_base_iteration($args, $data);
  /*
 $p = wc_get_products($args);
  $products = array();
  $userId = (isset($data["userId"]) ? $data["userId"] : "0" );


  foreach ($p as $product) {
      $a_product = $product->get_data();
      $comments = hops_add_user_comment_to_product_response($userId, $a_product["id"]);


      $a_product["featured_image"] = hm_get_image_src($a_product["image_id"]);
      $a_product['user_comment'] = $comments; //try to add user comment for product if any

      if (class_exists('ACF')) $a_product["bg_color"] = get_field("bg_color", $a_product["id"]);

      $products[] = $a_product;


  }

  return new WP_REST_Response($products, 200);
  */
}


function uncode_language_setup()
{
	load_child_theme_textdomain('uncode', get_stylesheet_directory() . '/languages');
}

function theme_enqueue_styles()
{
	$production_mode = ot_get_option('_uncode_production');
	$resources_version = ($production_mode === 'on') ? null : rand();
	if ( function_exists('get_rocket_option') && ( get_rocket_option( 'remove_query_strings' ) || get_rocket_option( 'minify_css' ) || get_rocket_option( 'minify_js' ) ) ) {
		$resources_version = null;
	}
	$parent_style = 'uncode-style';
	$child_style = array('uncode-style');
	wp_enqueue_style($parent_style, get_template_directory_uri() . '/library/css/style.css', array(), $resources_version);
	wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $child_style, $resources_version);
}
add_action('wp_enqueue_scripts', 'theme_enqueue_styles', 100);


function hm_shortcode_mailchimp(){
	echo do_shortcode( '[mc4wp_form id="88696"]' );
}
