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

});
add_action("admin_init", "hm_beer_count_sync");
add_action('save_post', 'hm_refreshCache', 1); // clear REST cache after save
add_filter( 'avatar_defaults', 'hm_modify_default_avatar' ); // change default avatar

add_action( 'user_register', 'hm_define_displayname' );
add_action( 'profile_update', 'hm_define_displayname' );

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

  $a_cacheKeys = hm_getPostCacheKeys($postID, $postCacheType);
  foreach ( $a_cacheKeys as $key ) {
    $ret = $caching->delete_cache($key);
  }

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
        if ($res) $ret = Array('result' => true, 'http'=> 200, 'data'=> 'brewery_added');

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
        if ($res) $ret = Array('result' => true, 'http'=> 200, 'data'=> 'brewery_removed');
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

  }else{

    return new WP_REST_Response(Array('result' =>false, 'data' => 'invalid_preference_type'), 404);

  }

  if (!$outputData) $outputData = "";
  return new WP_REST_Response(Array('result' => $outputData, 'data' => 'ok_results'), 200);

  // default error
  //return new WP_REST_Response($ret, 200);

}

function hm_get_beers_by_brewery_id( $data ) {

  $args = wp_parse_args( $args, array(
          //'post_type' => 'product',
          'status' => array( 'publish' ),
          //'type' => array_merge( array_keys( wc_get_product_types() ) ),
          //'parent' => null,
          //'sku' => '',
          //'category' => array(),
          //'tag' => array(),
          //'limit' => get_option( 'posts_per_page' ),
          // 'offset' => null,
          // 'page' => 1,
          //'include' => array(),
          //'exclude' => array(),
          //'orderby' => 'date',
          //'order' => 'DESC',
          //'return' => 'objects',
          //'paginate' => false,
          //'shipping_class' => array(),
          'meta_key' => 'brewery',
          'meta_value' => $data['breweryID'], //'meta_value' => array('yes'),
          'meta_compare' => '=' //'meta_compare' => 'NOT IN'  or = or IN
   ) );

 //$p = wc_get_products(array('status' => 'publish', 'category' => $data['slug']));
 $p = wc_get_products($args);
    $products = array();

    foreach ($p as $product) {
        $a_product = $product->get_data();
        $a_product["featured_image"] = hm_get_image_src($a_product["image_id"]);
        if (class_exists('ACF')) $a_product["bg_color"] = get_field("bg_color", $a_product["id"]);
        $products[] = $a_product;


    }

    return new WP_REST_Response($products, 200);
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
