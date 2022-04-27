<?php

add_filter('onesignal_send_notification', 'hops_onesignal_send_notification_filter', 10, 4);


function hops_onesignal_send_notification_filter( $fields, $new_status, $old_status, $post ){
  $postType = get_post_type( $post->ID );

  $BREWERY_ID_PARENT_PAGE = "89109";
  // only for products and breweries
  if (
    $postType == 'product'
    || ( $postType == "page" && $post->post_parent == $BREWERY_ID_PARENT_PAGE) ) {

    if ($post->post_parent == $BREWERY_ID_PARENT_PAGE) $postType = "brewery";

    $fields["isAndroid"] = true;
    $fields["isIos"] = true;

    $fields["web_url"] = $fields["url"];

    unset($fields["url"]);

    $fields["data"] = Array(
      "post_id" => $post->ID,
      "post_type" => $postType
    ); // add post ID to data
  }


  return $fields;

}




 ?>
