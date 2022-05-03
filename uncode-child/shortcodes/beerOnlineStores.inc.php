<?php
// a shortcode to show how all online stores where this beer is active
add_shortcode( 'beeronlinestores', 'hops_beer_online_stores' );

function hops_offline_text($s, $boolOffline = false){
  $ret = $s;
  if ($boolOffline) $ret = '<i style="text-decoration:line-through;">'.$ret.'</i>';
  return $ret;
}

function hops_beer_online_stores( $atts, $content = "" ) {
  global $post;
  $ret = "";

  $atts = shortcode_atts( array(
		'beer_id' => 0,
	), $atts, 'short_alert' );


  if (class_exists('ACF')) {
    $postID = get_the_ID();

    $stores = get_field("stores", $postID);
    $storesProductsUrls = get_field("stores_products_urls", $postID);
    $storesProductsPrinces = get_field("store_products_prices", $postID);
    $storesPricesLastUpdate = get_field("stores_prices_last_update", $postID);

    echo do_shortcode('[vc_row_inner]
                          [vc_column_inner column_width_use_pixel="yes" align_horizontal="align_center" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="0" mobile_width="0" width="1/1" column_width_pixel="700"]
                            [vc_custom_heading '.($storesPricesLastUpdate ? 'subheading="Última actualización: '.$storesPricesLastUpdate.'" sub_lead="small" sub_reduced="yes"' : '').' heading_semantic="h3" text_font="font-762333" text_size="h1" sub_lead="yes"]
                              Tiendas online
                            [/vc_custom_heading]
                          [/vc_column_inner]
                        [/vc_row_inner]');

    //echo do_shortcode('[vc_empty_space empty_h="2"][vc_separator sep_color=",Default"]');

    $itemKey = 0;
    foreach($stores as $store){
      $storeName = $store->post_title;
      $storeImage = get_the_post_thumbnail_url($store->ID);
      $storeImageId = get_post_thumbnail_id($store->ID);
      $storeLink = get_permalink($store);
      $storeItemPrice = (isset($storesProductsPrinces[$itemKey]) ? $storesProductsPrinces[$itemKey]  : 0 );
      $storeItemUrl = (isset($storesProductsUrls[$itemKey]) ? $storesProductsUrls[$itemKey]  : 0 );
      $storeIsVerified = get_field("is_verified", $store->ID);
      $storeIsOffline = get_field("is_offline", $store->ID);
      //var_dump($store);

      $storeMessage = '<i class="fa fa-check-circle" '.($storeIsVerified ? 'style="color:#3BA2F2;' : '' ).'" aria-hidden="true"></i> '.($storeIsVerified ? "Verificada" : "No verificada" );
      if ($storeIsOffline) $storeMessage = '<i class="fa fa-times-circle" aria-hidden="true"></i> Offline';

      $storeButtonBuy = '[vc_button rel="nofollow noreferrer noopener" button_color="color-742106" size="btn-lg" radius="btn-round" border_animation="btn-ripple-out" hover_fx="full-colored" shadow="yes" border_width="0" display="block" link="url:'.urlencode($storeItemUrl).'||target:_blank|" width="140" el_class="hero-download-button hero-message-download-button"]Comprar[/vc_button]';
      if ($storeIsOffline) $storeButtonBuy = "";

      $ret .= do_shortcode('

              [vc_row_inner el_class="onlineStoreItem" row_inner_height_percent="0" overlay_alpha="50" equal_height="yes" gutter_size="3" shift_y="0" z_index="0"]
                  [vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="2" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"]

                    <table class="no-border" border="0" style="border:0;">
                      <tr>
                        <td style="padding-right:10px;">
                          <img src="'.$storeImage.'" style="width:50px;min-width:50px;">
                          <!--[vc_single_image media="'.$storeImageId.'" media_width_use_pixel="yes" shape="img-circle" media_width_pixel="50"]-->

                        </td>
                        <td>
                          [vc_custom_heading heading_semantic="h6" text_size="h4"]
                              '.hops_offline_text($storeName, $storeIsOffline).'
                          [/vc_custom_heading]
                        </td>
                      </tr>
                    </table>



                  [/vc_column_inner]
                [vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"]
                  [vc_custom_heading heading_semantic="h6" text_size="h4"]
                    '.hops_offline_text('$'.$storeItemPrice, $storeIsOffline).'
                  [/vc_custom_heading]
                [/vc_column_inner]

                [vc_column_inner mobile_visibility="yes" mobile_width="0" column_width_percent="100" position_vertical="middle" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" width="1/4"]
                  [vc_custom_heading heading_semantic="h6" text_size="h4"]

                    '.$storeMessage.'

                  [/vc_custom_heading]
                [/vc_column_inner]

                [vc_column_inner el_class="storeBuyButtonColumn" column_width_percent="100" position_vertical="middle" align_horizontal="align_right" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_left_mobile" mobile_width="3" width="1/4"]

                '.$storeButtonBuy.'

              [/vc_column_inner]
            [/vc_row_inner]

            [vc_separator sep_color=",Default"]');




      //$ret .= "".$storeName." - \$".$storeItemPrice." - <a href='".$storeItemUrl."' target='_blank'>Comprar</a><br>";


      $itemKey++;
    }


    // no stores, then hide the block
    if ($itemKey == 0){
      $ret .= "<div style='text-align:center;'>".__("No existen tiendas online con este producto")."</div>";
    }



  }



	return $ret;
}



 ?>
