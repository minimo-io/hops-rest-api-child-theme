<?php


add_action( 'rest_api_init', function () {

    // get premium beers
    register_rest_route( 'hops/v1', '/beers/price', array(
       'methods' => 'GET',
       'callback' => 'hm_get_beers_by_price',
     ) );


});



function hm_get_beers_by_price($data){
  $data = $data->get_params();

  $args = wp_parse_args( $args, array(
      'limit' => 10,
      'page' => $data["page"],
      'status' => array( 'publish' )
   ) );

   $extraOrder = Array(
     'field' => 'price',
     'order' => $data['extraParam1'] // asc or desc
   );


   return hm_get_beers_base_iteration($args, $data, $extraOrder );


}

?>