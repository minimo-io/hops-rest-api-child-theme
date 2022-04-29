<?php
// a shortcode to show how all online stores where this beer is active
add_shortcode( 'beeronlinestores', 'hops_beer_online_stores' );


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

    echo do_shortcode('[vc_row_inner][vc_column_inner column_width_use_pixel="yes" align_horizontal="align_center" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="0" mobile_width="0" width="1/1" column_width_pixel="700"][vc_custom_heading heading_semantic="h3" text_font="font-762333" text_size="h1" sub_lead="yes"]Tiendas online[/vc_custom_heading][/vc_column_inner][/vc_row_inner]');

    $itemKey = 0;
    foreach($stores as $store){
      $storeName = $store->post_title;
      $storeImage = get_the_post_thumbnail_url($store->ID);
      $storeLink = get_permalink($store);
      $storeItemPrice = (isset($storesProductsPrinces[$itemKey]) ? $storesProductsPrinces[$itemKey]  : 0 );
      $storeItemUrl = (isset($storesProductsUrls[$itemKey]) ? $storesProductsUrls[$itemKey]  : 0 );
      //var_dump($store);

      /*
      echo [vc_empty_space empty_h="2"][vc_separator sep_color=",Default"][vc_row_inner row_inner_height_percent="0" overlay_alpha="50" equal_height="yes" gutter_size="3" shift_y="0" z_index="0"][vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="2" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"][vc_custom_heading heading_semantic="h6" text_size="h5"]Short headline[/vc_custom_heading][/vc_column_inner][vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"][vc_custom_heading heading_semantic="h6" text_size="h5"]Tagline[/vc_custom_heading][/vc_column_inner][vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"][vc_custom_heading heading_semantic="h6" text_size="h5"]May 24-26[/vc_custom_heading][/vc_column_inner][vc_column_inner column_width_percent="100" position_vertical="middle" align_horizontal="align_right" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="0" width="1/4"][vc_button button_color="accent" border_width="0" link="url:%23||target:%20_blank|"]Click the button[/vc_button][/vc_column_inner][/vc_row_inner][vc_separator sep_color=",Default"][vc_row_inner row_inner_height_percent="0" overlay_alpha="50" equal_height="yes" gutter_size="3" shift_y="0" z_index="0"][vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="2" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"][vc_custom_heading heading_semantic="h6" text_size="h5"]Short headline[/vc_custom_heading][/vc_column_inner][vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"][vc_custom_heading heading_semantic="h6" text_size="h5"]Tagline[/vc_custom_heading][/vc_column_inner][vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"][vc_custom_heading heading_semantic="h6" text_size="h5"]May 24-26[/vc_custom_heading][/vc_column_inner][vc_column_inner column_width_percent="100" position_vertical="middle" align_horizontal="align_right" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="0" width="1/4"][vc_button button_color="accent" border_width="0" link="url:%23||target:%20_blank|"]Click the button[/vc_button][/vc_column_inner][/vc_row_inner][vc_separator sep_color=",Default"][vc_row_inner row_inner_height_percent="0" overlay_alpha="50" equal_height="yes" gutter_size="3" shift_y="0" z_index="0"][vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="2" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"][vc_custom_heading heading_semantic="h6" text_size="h5"]Short headline[/vc_custom_heading][/vc_column_inner][vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"][vc_custom_heading heading_semantic="h6" text_size="h5"]Tagline[/vc_custom_heading][/vc_column_inner][vc_column_inner column_width_percent="100" position_vertical="middle" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="3" width="1/4"][vc_custom_heading heading_semantic="h6" text_size="h5"]May 24-26[/vc_custom_heading][/vc_column_inner][vc_column_inner column_width_percent="100" position_vertical="middle" align_horizontal="align_right" gutter_size="3" overlay_alpha="50" shift_x="0" shift_y="0" shift_y_down="0" z_index="0" medium_width="2" align_mobile="align_center_mobile" mobile_width="0" width="1/4"][vc_button button_color="accent" border_width="0" link="url:%23||target:%20_blank|"]Click the button[/vc_button][/vc_column_inner][/vc_row_inner][vc_separator sep_color=",Default"];
      */

      $ret .= "".$storeName." - \$".$storeItemPrice." - <a href='".$storeItemUrl."' target='_blank'>Comprar</a><br>";


      $itemKey++;
    }


    // no stores, then hide the block
    if ($itemKey == 0){
      $ret .= "<div style='text-center'>"._e("No existen tiendas online con este producto")."</div>";
    }



  }



	return $ret;
}



 ?>
