<?php



add_shortcode( 'storeBeersAndBreweries', 'hops_store_beers_and_breweries' );

// brewery followers
function hops_store_beers_and_breweries( $atts, $content = "" ) {
  $ret = "";

  $atts = shortcode_atts( array(
	), $atts, 'short_alert' );


  if (class_exists('ACF')) {


    $beers = get_posts(array(
      'post_type' => 'product',
      'meta_query' => array(
        array(
          'key' => 'stores', // name of custom field
          'value' => '"' . get_the_ID() . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
          'compare' => 'LIKE'
        )
      )
    ));
    $beersIDs = "";
    foreach($beers as $beer){
      if ($beersIDs != "") $beersIDs .= ",";
      $beersIDs .= $beer->ID;
    }

    // echo do_shortcode('[uncode_index el_id="index-121153" isotope_mode="fitRows" loop="size:All|order_by:date|post_type:product|by_id:'.$beersIDs.'|taxonomy_count:All" index_back_color="accent" filtering="yes" filtering_position="center" filtering_uppercase="yes" filter_scroll="yes" gutter_size="2" inner_padding="yes" product_items="title,category|nobg|relative|display-icon,price|default,media|featured|onpost|original|hide-sale|inherit-atc|inherit-w-atc|atc-typo-default|hide-atc,wishlist-button" screen_lg="1000" screen_md="600" screen_sm="180" single_width="3" single_overlay_color="accent" single_overlay_coloration="top_gradient" single_overlay_opacity="1" single_h_align="center" single_padding="0" single_text_reduced="yes" single_border="yes" no_double_tap="yes" uncode_shortcode_id="147055" index_back_color_type="uncode-palette" filter_all_text="Todas" index_back_color_solid="#ff0000" filter_back_color_type="uncode-palette" filter_back_color_solid="#ff0000" footer_back_color_type="uncode-palette" footer_back_color_solid="#ff0000"]');

    echo do_shortcode('[vc_row row_height_percent="0" overlay_alpha="50" gutter_size="3" column_width_percent="100" shift_y="0" z_index="0" uncode_shortcode_id="0"]
                          [vc_column width="1/1"]
                            [uncode_index
                              el_id="index-121153" isotope_mode="fitRows" loop="size:All|order_by:date|post_type:product|by_id:'.$beersIDs.'|taxonomy_count:All"
                              index_back_color="accent"
                              filtering="yes"
                              filtering_position="center"
                              filtering_uppercase="yes"
                              filter_scroll="yes"
                              gutter_size="2"
                              inner_padding="yes"
                              product_items="title,category|nobg|relative|display-icon,price|default,media|featured|onpost|original|hide-sale|inherit-atc|inherit-w-atc|atc-typo-default|hide-atc,wishlist-button"
                              screen_lg="1000"
                              screen_md="600"
                              screen_sm="180"
                              single_width="3"
                              single_overlay_color="accent"
                              single_overlay_coloration="top_gradient"
                              single_overlay_opacity="1"
                              single_h_align="center"
                              single_padding="0"
                              single_text_reduced="yes"
                              single_border="yes"
                              no_double_tap="yes"
                              uncode_shortcode_id="0"
                              index_back_color_type="uncode-palette"
                              filter_all_text="Todas"]
                            [/vc_column]
                          [/vc_row]');

    // var_dump( $beers );

    // echo do_shortcode('[vc_gallery el_id="gallery-17359" random="yes" medias="103570,88990,89434,89521,89436,89430,88989,103799,103698,89108,103711" gutter_size="2" media_items="media|custom_link|original,icon" screen_lg="600" screen_md="250" screen_sm="300" single_width="2" images_size="one-one" single_overlay_color="accent" single_overlay_opacity="50" single_text_anim="no" single_image_color_anim="yes" single_image_anim="no" single_h_align="center" single_padding="2" single_title_dimension="h1" single_icon="fa fa-search3" single_border="yes" no_double_tap="yes" carousel_rtl="" single_title_uppercase="" uncode_shortcode_id="690948"]');

    // $postID = get_the_ID();
    // $beerFollowers = get_field("followers", $postID);
    // $beerABV = get_field("abv", $postID);
    // $beerIBU = get_field("ibu", $postID);
    // if (!$beerIBU) $beerIBU = "N/A";
    //
    // $ret .= '<div class="beersPillBox">';
    //   $ret .= '<div class="vc_acf vc_txt_align_center beerPill">
    //               '.$beerFollowers.' seguidor'.($beerFollowers != 1 ? "es" : "").'
    //            </div>';
    //   $ret .= '<div class="vc_acf vc_txt_align_center beerPill">
    //              '.$beerABV.' ABV
    //           </div>';
    //   $ret .= '<div class="vc_acf vc_txt_align_center beerPill">
    //               '.$beerIBU.' IBU
    //            </div>';
    // $ret .= '</div>';

  }



	return $ret;
}



 ?>