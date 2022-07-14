<?php


add_shortcode( 'beerdetails', 'hops_beer_details' );

// brewery followers
function hops_beer_details( $atts, $content = "" ) {
  $ret = "";

  $atts = shortcode_atts( array(
		'align' => 'left',
	), $atts, 'short_alert' );


  if (class_exists('ACF')) {
    $postID = get_the_ID();
    $beerLaunch = get_field("launch", $postID);
    $beerFollowers = get_field("followers", $postID);
    $beerType = "";
    $beerIBU = get_field("ibu", $postID);
    $beerABV = get_field("abv", $postID);
    $beerContainers = get_field("container", $postID);

    $terms = get_the_terms( $postID, 'product_cat' );

    foreach ($terms as $term) {
        $beerType = $term->name;
    }

    $ret .= '
    <table class="table table-borderless table-beer-details">
      <tbody>
        <tr>
          <th scope="row">#</th>
          <td><div class="text-pill">'.$postID.'</div></td>
        </tr>
        <tr>
          <th scope="row">Lanzamiento</th>
          <td><div class="text-pill">'.(empty($beerLaunch) ? "?" : $beerLaunch ).'</div></td>
        </tr>
        <tr>
          <th scope="row">Estilo</th>
          <td><div class="text-pill">'.$beerType.'</div></td>
        </tr>
        <tr>
          <th scope="row">IBU</th>
          <td><div class="text-pill">'.$beerIBU.'</div></td>
        </tr>
        <tr>
          <th scope="row">ABV</th>
          <td><div class="text-pill">'.$beerABV.'%</div></td>
        </tr>
        <tr>
          <th scope="row">Envases</th>
          <td><div class="text-pill">'.$beerContainers.'</div></td>
        </tr>
      </tbody>

      </table>
    ';
    //var_dump($brewery);
  }



	return $ret;
}


 ?>