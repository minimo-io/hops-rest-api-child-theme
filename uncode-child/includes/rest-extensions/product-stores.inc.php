<?php
/// Add stores data to the product response

add_filter('woocommerce_rest_prepare_product_object', 'hops_extend_product_response_add_stores', 10, 3); 

function hops_extend_product_response_add_stores($response, $object, $request){
    if (empty($response->data)) return $response;

    $postId = $object->get_id(); //it will fetch product id
    $userId = $request->get_param( 'userId' );
    $postQueryId = $request->get_param( 'id' );

    // add stores
    
    $response->data['stores'] = Array();
    $productStores = get_field("stores", $postId);
    $productStoresPrices = get_field("store_products_prices", $postId);
    $productStoresUrls = get_field("stores_products_urls", $postId);
    $productStoresPricesLastUpdate = get_field("stores_prices_last_update", $postId);

    // $response->data['stores']['prices_last_update'] = $productStoresPricesLastUpdate;
    $storeIndex = 0;
    foreach ( $productStores as $store){
      $aStore = (array)$store;


      $finalStore["id"] = (string)$aStore["ID"];
      $finalStore["name"] = $aStore["post_title"];
      //$storeThumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $finalStore["id"] ));
      $storeThumbnail = get_the_post_thumbnail_url( $finalStore["id"], 'full');

      $finalStore["price"] = (isset($productStoresPrices[$storeIndex]) ? $productStoresPrices[$storeIndex] : "" );
      $finalStore["price_last_update"] = $productStoresPricesLastUpdate; // general now, might be store by store in the future
      $finalStore["url"] = (isset($productStoresUrls[$storeIndex]) ? $productStoresUrls[$storeIndex] : "" );

      $isStoreVerified = get_field("is_verified", $aStore["ID"]);
      $finalStore["is_verified"] = ($isStoreVerified == true ? "true" : "false");

      $finalStore["image"] = $storeThumbnail;

      $response->data['stores'][] = $finalStore;
      $storeIndex++;
    }

    return $response;
}



?>