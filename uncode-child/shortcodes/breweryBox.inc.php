<?php

function hops_brewery_box( $atts, $content = "" ) {
  $ret = "";

  $atts = shortcode_atts( array(
		'type' => 'info',
	), $atts, 'short_alert' );

  /*
  if (empty(trim($content)) && !empty($atts["preset"])){
    switch ($atts["preset"]){
      case "no-rtp":
        $content = __("It is advisable to exercise caution with providers that are not transparent with games basic values. If you are going to play for real money keep in mind that there is no complete official information for this game.", "aipim");
        if (empty($atts["title"])) $atts["title"] = __("ATTENTION!", "aipim");
        break;
    }
  }
  $ret .= '<div class="alert alert-'.$atts["type"].' mb-3" role="alert">';
  if ($atts["title"]) $ret .= '<strong class="text-uppercase">'.$atts["title"].'</strong> ';
  $ret .= $content;
  $ret .= '</div>';
  */
  if (class_exists('ACF')) {
    $postID = get_the_ID();
    $brewery = get_field("brewery", $postID);
    $breweryImage = get_field("brewery_image", $postID);

    $breweryLink = get_permalink($brewery);

    $ret .= "<a href='".$breweryLink."' class='breweryBox'>";
      $ret .= "<img src='".$breweryImage."' />";
      $ret .= "<span>".$brewery->post_title."</span>";
    $ret .= "</a>";

    $ret .= "<div style='clear:both'></div>";

    //$ret .= '<div class="vc_acf vc_txt_align_center field_606dde23e36b0">1</div>';
    //var_dump($brewery);
  }



	return $ret;
}
add_shortcode( 'brewerybox', 'hops_brewery_box' );

?>