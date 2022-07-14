<?php
add_shortcode( 'storemetabox', 'hops_store_metabox' );
add_shortcode( 'storestatus', 'hops_store_status' );

function hops_store_metabox( $atts, $content = "" ) {
  $ret = "";

  $atts = shortcode_atts( array(

	), $atts, 'short_alert' );


  if (class_exists('ACF')) {

    $postID = get_the_ID();
    $storeUrl = get_field("url", $postID);
    $storeWhatsapp = get_field("whatsapp", $postID);
    $storeInstagram = get_field("instagram", $postID);
    $storeIsVerified = get_field("is_verified", $postID);
    $storeIsOnline = get_field("is_offline", $postID);
    $storeIsOfficalStore = get_field("is_brewery_official_store", $postID);
    $storeOfficialBrewery = NULL;
    if ($storeIsOfficalStore) $storeOfficialBrewery = get_field("brewery", $postID);

    $whatsappLink = "";
    if ($storeWhatsapp) $whatsappLink = 'https://wa.me/'.str_replace("+", "", $storeWhatsapp);

    $ret .= '<div class="storeMetaBox">';
    $ret .= ' <div class="storeMetaBox-Buttons">';
            if (! $storeOfficialBrewery){
              $ret .= do_shortcode('
                [vc_button rel="nofollow noreferrer noopener" link="url:'.urlencode($storeUrl).'||target:_blank|" size="btn-lg" radius="btn-circle" hover_fx="full-colored" custom_typo="yes" font_family="font-156269" font_weight="600" text_transform="uppercase" border_width="0" display="inline" inline_mobile="yes" el_class="hm-beer-buy-button"]
                  Visitar
                [/vc_button]');

                if ($storeInstagram ){
                  $storeInstagram = "https://www.instagram.com/".$storeInstagram."/";
                  $ret .= do_shortcode('
                    [vc_button rel="nofollow noreferrer noopener" link="url:'.urlencode($storeInstagram).'||target:_blank|" size="btn-lg" radius="btn-circle" hover_fx="full-colored" custom_typo="yes" font_family="font-156269" font_weight="600" text_transform="uppercase" border_width="0" display="inline" inline_mobile="yes" el_class="hm-beer-buy-button storeButtonWhatsapp btn-outline"]
                      <i class="fa fa-instagram" aria-hidden="true" style="Xmargin-right:0;"></i>Seguir
                    [/vc_button]
                  ');
                }
                if ($storeWhatsapp && !$storeInstagram){
                  $ret .= do_shortcode('
                    [vc_button rel="nofollow noreferrer noopener" link="url:'.urlencode($whatsappLink).'||target:_blank|" size="btn-lg" radius="btn-circle" hover_fx="full-colored" custom_typo="yes" font_family="font-156269" outline="yes" font_weight="600" text_transform="uppercase" border_width="0" display="inline" inline_mobile="yes" el_class="hm-beer-buy-button storeButtonWhatsapp btn-outline"]
                      <i class="fa fa-whatsapp" aria-hidden="true" style="Xmargin-right:0;"></i>Contactar
                    [/vc_button]
                  ');
                }

            }else if ($storeOfficialBrewery){
              $breweryPermalink = get_permalink($storeOfficialBrewery->ID);
              $ret .= do_shortcode('
                [vc_button link="url:'.urlencode($breweryPermalink).'" size="btn-lg" radius="btn-circle" hover_fx="full-colored" custom_typo="yes" font_family="font-156269" font_weight="600" text_transform="uppercase" border_width="0" display="inline" inline_mobile="yes" el_class="hm-beer-buy-button"]
                  Cervecería
                [/vc_button]');

              $ret .= do_shortcode('
                [vc_button rel="nofollow noreferrer noopener" link="url:'.urlencode("https://hops.uy/app/").'||target:_blank|" size="btn-lg" radius="btn-circle" hover_fx="full-colored" custom_typo="yes" font_family="font-156269" outline="yes" font_weight="600" text_transform="uppercase" border_width="0" display="inline" inline_mobile="yes" el_class="hm-beer-buy-button btn-outline"]
                  <i class="fa fa-heart-o" aria-hidden="true" style="Xmargin-right:0;"></i>Seguir
                [/vc_button]
              ');
            }

    $ret .= "</div>";

            $ret .= do_shortcode('[vc_empty_space empty_h="1"]');
            $ret .= do_shortcode('[storestatus align="text-center"][/storestatus]');

    $ret .= "</div>";
    $ret .= "<div style='clear:both'></div>";

  }



	return $ret;
}




function hops_store_status( $atts, $content = "" ) {
  $ret = "";

  $atts = shortcode_atts( array(
    'type' => 'verified-box',
    'align' => 'text-right'
	), $atts, 'short_alert' );


  if (class_exists('ACF')) {
    $storeMessage = ""; // status message
    $postID = get_the_ID();

    if ($atts['type'] == 'verified-box'){
      $storeIsVerified = get_field("is_verified", $postID);
      $storeIsOffline = get_field("is_offline", $postID);


      $storeMessage = '<i class="fa '.($storeIsVerified ? 'fa-check-circle' : 'fa-times-circle' ).'" '.($storeIsVerified ? 'style="color:#3BA2F2;' : '' ).'" aria-hidden="true"></i> '.($storeIsVerified ? "Verificada" : "No verificada" );

      $storeMessage .= "&nbsp;&nbsp;";

      if ($storeIsOffline){
        $storeMessage .= '<i class="fa fa-times-circle" aria-hidden="true"></i> Offline';
      }else{
        $storeMessage .= '<i class="fa fa-check-circle" style="color:#43cb83;" aria-hidden="true"></i> Online';
      }
    }else if($atts['type'] == 'last-crawl'){
      $storeLastCrawl = get_field("last_crawl", $postID);
      $storeMessage = '<i class="fa fa-eye" aria-hidden="true"></i> '.$storeLastCrawl;
    }


    $ret .= '<div class="storeStatusBox '.$atts['align'].'" '.($atts['type'] == 'last-crawl' ? 'style="'.($atts["align"]=='text-right' ? 'float:right;' : '').' padding-right:10px; padding-left:10px; background-color:black;color:white;opacity:.4;border-radius:20px;width:fit-content;"' : '').'>';
            $ret .= $storeMessage;
    $ret .= "</div>";
    $ret .= "<div style='clear:both'></div>";

  }



	return $ret;
}


?>