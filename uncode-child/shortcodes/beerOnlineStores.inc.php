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


  $product = wc_get_product( $post->ID );


  if (class_exists('ACF')) {
    $postID = get_the_ID();

    $stores = get_field("stores", $postID);
    $storesProductsUrls = get_field("stores_products_urls", $postID);
    $storesProductsPrinces = get_field("store_products_prices", $postID);
    $storesPricesLastUpdate = get_field("stores_prices_last_update", $postID);

    echo do_shortcode('[vc_row_inner el_id="compra"]
                          [vc_column_inner column_width_use_pixel="yes" align_horizontal="align_center" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="0" mobile_width="0" width="1/1" column_width_pixel="700"]
                            [vc_custom_heading heading_semantic="h3" text_font="font-762333" text_size="h1" sub_lead="no"]
                              Compra online
                              <p class="text-center"><i class="fa fa-bell" aria-hidden="true"></i> Recordá que comprando a través de la app de HOPS ganás puntos canjeables por descuentos en los bares asociados.</p>
                            [/vc_custom_heading]

                          [/vc_column_inner]
                        [/vc_row_inner]
                        ');

    //echo do_shortcode('[vc_empty_space empty_h="2"][vc_separator sep_color=",Default"]');

    $addToCartButton = do_shortcode('[vc_button dynamic="add-to-cart" quantity="variation" size="btn-lg" radius="btn-round" border_animation="btn-ripple-out" hover_fx="full-colored" shadow="yes" border_width="0" custom_typo="yes" font_family="font-156269" font_weight="600" text_transform="uppercase" wide="yes" border_width="0" button_color="color-742106" display="inline" uncode_shortcode_id="'.get_the_ID().'" el_class="Xhm-beer-buy-button"]Text on the button[/vc_button]');

    if (isset($product) && $product->is_in_stock()){
      $ret .= do_shortcode('
              [vc_separator sep_color=",Default"][vc_empty_space empty_h="1"]

              [vc_row_inner el_class="onlineStoreItem hopsProductAddToCartOption" row_inner_height_percent="0" overlay_alpha="50" equal_height="yes" gutter_size="3" shift_y="0" z_index="0"]
                  [vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="2" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"]

                    <table class="no-border" border="0" style="border:0;">
                      <tr>
                        <td style="padding-right:10px;">
                          <img src="https://hops.uy/wp-content/uploads/2022/05/hops-logo-border.png" style="width:50px;min-width:50px;">
                        </td>
                        <td>
                          [vc_custom_heading text_font="font-762333" heading_semantic="h6" text_size="h2"]
                              HOPS
                          [/vc_custom_heading]
                        </td>
                      </tr>
                    </table>



                  [/vc_column_inner]
                [vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"]
                  [vc_custom_heading heading_semantic="h6" text_font="font-762333" text_size="h2"]
                    '.($product ? '$'.$product->get_price() : "-").'
                  [/vc_custom_heading]
                [/vc_column_inner]


                [vc_column_inner el_class="storeBuyButtonColumn" column_width_percent="100" position_vertical="middle" align_horizontal="align_right" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_left_mobile" mobile_width="3" width="2/4"]

                '.$addToCartButton.'

              [/vc_column_inner]
            [/vc_row_inner]


            [vc_empty_space empty_h="1"][vc_separator sep_color=",Default"][vc_empty_space empty_h="1"]');
    }


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

      $storeMessage = '<i class="fa '.($storeIsVerified ? 'fa-check-circle' : 'fa-question-circle').'" '.($storeIsVerified ? 'style="color:#3BA2F2;' : '' ).'" aria-hidden="true"></i> '.($storeIsVerified ? "Verificada" : "No verificada" );
      if ($storeIsOffline) $storeMessage = '<i class="fa fa-times-circle" aria-hidden="true"></i> Offline';

      $storeButtonBuy = '[vc_button rel="nofollow noreferrer noopener" button_color="color-742106" size="btn-lg" radius="btn-round" border_animation="btn-ripple-out" hover_fx="full-colored" shadow="yes" border_width="0" display="block" link="url:'.urlencode($storeItemUrl).'||target:_blank|" width="140" el_class="hero-download-button hero-message-download-button"]Comprar[/vc_button]';
      if ($storeIsOffline) $storeButtonBuy = "";


      $lastUpdateTooltip = ($storesPricesLastUpdate ? '<a class="btn-tooltip" href="#" data-toggle="tooltip" data-placement="top" data-original-title="Actualización: '.$storesPricesLastUpdate.'" title=""><i class="fa fa-info-circle" aria-hidden="true"></i></a>' : '');


      $ret .= do_shortcode('

              [vc_row_inner el_class="onlineStoreItem" row_inner_height_percent="0" overlay_alpha="50" equal_height="yes" gutter_size="3" shift_y="0" z_index="0"]
                  [vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="2" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"]

                    <table class="no-border" border="0" style="border:0;">
                      <tr>
                        <td style="padding-right:10px;">
                          <img src="'.$storeImage.'" style="width:50px;min-width:50px;">
                        </td>
                        <td>
                          [vc_custom_heading text_font="font-762333" heading_semantic="h6" text_size="h4"]
                              '.hops_offline_text($storeName, $storeIsOffline).'
                          [/vc_custom_heading]
                        </td>
                      </tr>
                    </table>



                  [/vc_column_inner]
                [vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"]
                  [vc_custom_heading heading_semantic="h6" text_font="font-762333" text_size="h4"]
                    '.hops_offline_text('$'.$storeItemPrice.$lastUpdateTooltip, $storeIsOffline).'
                  [/vc_custom_heading]
                [/vc_column_inner]

                [vc_column_inner mobile_visibility="yes" mobile_width="0" column_width_percent="100" position_vertical="middle" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" width="1/4"]
                  [vc_custom_heading text_font="font-156269" el_class="storeBuyOnlineStatus" heading_semantic="h6" text_size="h4"]

                    '.$storeMessage.'

                  [/vc_custom_heading]
                [/vc_column_inner]

                [vc_column_inner el_class="storeBuyButtonColumn" column_width_percent="100" position_vertical="middle" align_horizontal="align_right" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_left_mobile" mobile_width="3" width="1/4"]

                '.$storeButtonBuy.'

              [/vc_column_inner]
            [/vc_row_inner]



            [vc_empty_space empty_h="1"][vc_separator sep_color=",Default"][vc_empty_space empty_h="1"]');




      //$ret .= "".$storeName." - \$".$storeItemPrice." - <a href='".$storeItemUrl."' target='_blank'>Comprar</a><br>";


      $itemKey++;
    }

    // $ret .= ($storesPricesLastUpdate ? 'Última actualización: '.$storesPricesLastUpdate : '');

    // no stores, then hide the block
    if ($itemKey == 0){
      $ret .= "<div style='text-align:center;'>
                ".__("No existen otras tiendas online con este producto")."
                <br>
                Si sos una cervecería con <a href='/tiendas/'>tienda o un delivery online</a> integrate a HOPS
                <br>
                ". __("y ampliá tus visibilidad ¡Es gratis!")."
              </div>";
    }



  }



	return $ret;
}



 ?>
