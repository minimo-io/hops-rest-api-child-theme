<?php




add_action( 'rest_api_init', function () {

    // get premium beers
    register_rest_route( 'hops/v1', '/beers/abv', array(
       'methods' => 'GET',
       'callback' => 'hm_get_beers_by_abv',
     ) );


});

function hm_get_beers_by_abv($data){
  $data = $data->get_params();

  $args = wp_parse_args( $args, array(
      'limit' => 10,
      'page' => $data["page"],
      'status' => array( 'publish' ),
      'orderby' => 'meta_value_num',
      'meta_key' => 'abv',
      'order' => $data['extraParam1'] // asc or desc
      /*
      'meta_value' => true, //'meta_value' => array('yes'),
      'meta_compare' => '=' //'meta_compare' => 'NOT IN'  or = or IN
      */
   ) );
   // var_dump($args);
   // die();

   return hm_get_beers_base_iteration($args, $data);


}

?>
