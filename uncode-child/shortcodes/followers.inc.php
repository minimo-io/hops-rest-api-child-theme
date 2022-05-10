<?php


add_shortcode( 'breweryfollowers', 'hops_brewery_followers' );
add_shortcode( 'beerfollowers', 'hops_beer_followers' );

// brewery followers
function hops_beer_followers( $atts, $content = "" ) {
  $ret = "";

  $atts = shortcode_atts( array(
		'align' => 'left',
	), $atts, 'short_alert' );


  if (class_exists('ACF')) {
    $postID = get_the_ID();
    $beerFollowers = get_field("followers", $postID);
    $beerABV = get_field("abv", $postID);
    $beerIBU = get_field("ibu", $postID);
    if (!$beerIBU) $beerIBU = "N/A";

    $ret .= '<div class="beersPillBox">';
      $ret .= '<div class="vc_acf vc_txt_align_center XbreweryFollowers beerPill">
                  '.$beerFollowers.' seguidor'.($beerFollowers != 1 ? "es" : "").'
               </div>';
      $ret .= '<div class="vc_acf vc_txt_align_center XbreweryFollowers beerPill">
                 '.$beerABV.' ABV
              </div>';
      $ret .= '<div class="vc_acf vc_txt_align_center XbreweryFollowers beerPill">
                  '.$beerIBU.' IBU
               </div>';
    $ret .= '</div>';
    //var_dump($brewery);
  }



	return $ret;
}

// brewery followers
function hops_brewery_followers( $atts, $content = "" ) {
  $ret = "";

  $atts = shortcode_atts( array(
		'align' => 'left',
	), $atts, 'short_alert' );


  if (class_exists('ACF')) {
    $postID = get_the_ID();
    $brewery = get_field("brewery", $postID);
    $breweryFollowers = get_field("followers", $brewery);

    $ret .= '<div class="vc_acf vc_txt_align_center breweryFollowers" style="margin:auto;">
                '.$breweryFollowers.' seguidor'.($breweryFollowers != 1 ? "es" : "").'
             </div>';
    //var_dump($brewery);
  }



	return $ret;
}




?>
