<?php
/// cron job for resetting views_count acf for (trends)

// unschedule event upon plugin deactivation
function cronstarter_deactivate() {
    // find out when the last event was scheduled
    $timestamp = wp_next_scheduled ('hops_views_count_reset');
    // unschedule previous event if any
    wp_unschedule_event ($timestamp, 'hops_views_count_reset');
}
// reset views_count custom field cronjob
function fn_hops_views_count_reset(){
  $postsToReset = get_posts( array(
      'post_status' => Array('publish'),
      'nopaging' => true,
      'showposts' => -1,
      'post_type' => Array('page', 'product'),
      'no_found_rows' => true,
      'suppress_filters' => false
  ) );

  foreach ($postsToReset as $post){
    // if (get_post_type( $casino->ID ) != "bonus") continue;
    if( class_exists('ACF') ){
      update_field("views_count", 0, $post);
    }
  }
  wp_reset_postdata();
  return true;
}

add_action ('hops_views_count_reset', 'fn_hops_views_count_reset');


?>