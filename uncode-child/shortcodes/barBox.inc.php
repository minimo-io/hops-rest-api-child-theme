
<?php
add_shortcode( 'barbox', 'hops_bar_box' );

// brewery followers
function hops_bar_box( $atts, $content = "" ) {
    $ret = "";
  
    $atts = shortcode_atts( array(
          'align' => 'left',
      ), $atts, 'short_alert' );
  
  
    if (class_exists('ACF')) {
      $postID = get_the_ID();
      $mapUrl = get_field("map_url", $postID);
      $address = get_field("address_name", $brewery);
      $instagram = get_field("instagram", $brewery);

      $barFollowers = 0;
  
      $ret .= '<div class="storeMetaBox followButtonMetaBox" style="margin:auto;">';
        $ret .= '<div class="vc_acf vc_txt_align_center breweryFollowersUnique" >
                    '.$barFollowers.' seguidor'.($barFollowers != 1 ? "es" : "").'
                 </div>';
        if ($mapUrl){
            $ret .= do_shortcode('
            [vc_button rel="nofollow noreferrer noopener" link="url:'.urlencode($mapUrl).'||target:_blank|" size="btn-lg" radius="btn-circle" hover_fx="full-colored" custom_typo="yes" font_family="font-156269" font_weight="600" text_transform="uppercase" border_width="0" display="inline" inline_mobile="yes" el_class="hm-beer-buy-button storeButtonWhatsapp btn-outline"]
                <i class="fa fa-map-marker" aria-hidden="true"></i>Mapa
            [/vc_button]
            ');
        }

      $ret .= '</div>';
  
        // $ret .= '<div class="vc_acf vc_txt_align_center breweryFollowers" style="margin:auto;">
        //             '.$breweryFollowers.' seguidor'.($breweryFollowers != 1 ? "es" : "").'
        //          </div>';
  
  
  
  
      //var_dump($brewery);
    }
  
  
  
      return $ret;
  }
  

?>