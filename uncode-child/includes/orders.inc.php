<?php
/// This file is in charge of handling orders
/// Eg. Do actions when order is created (send notifications, emails, etc)
/// Eg. Do actions when order is updated (adding points to the user & notifying him)

add_action( 'woocommerce_new_order', 'hops_on_new_order',  1, 1  ); 
add_action('woocommerce_order_status_changed', 'hops_order_status_paid', 10, 3);

function hops_on_new_order($order_id) {
    $order = new WC_Order( $order_id );
    $userId = $order->get_customer_id();
  
    // get the brewery ID from Order
    $brewery_id = 0; // get from first product ACF > brewery object
    $brewerty_user_id = 0; // user associated to the brewery when partners, via ACF

    // create local notification for the brewery user and the actual user
      // to the first, telling about the new order (which is pending payment)
      // to the actual user telling about the order was placed ok (and that he must pay if payment type is bacs),
    // add notification to brewery
    hops_add_notification($brewerty_user_id, "¡El cliente ".$userId." pagó su pedido! '¡A prepararlo!", "info");
    
    // log the results
    hops_log("Order creada con éxito. OrderID: ".$order_id." / Customer: ".$userId." / Payment: ".$order->get_payment_method_title()." (".$order->get_payment_method().")");
  }




function hops_order_status_paid($order_id, $old_status, $new_status)
{

    $order = wc_get_order($order_id);
    $userId = $order->get_customer_id();
    // this should mean that the payment was done and we change the status to
    // processing (a bank transfer)
    if ($old_status == "pending" && $new_status == "processing" && $order->get_payment_method() == "bacs"){


        // add order score to the user
        $scoreRes = hm_update_user_score($userId, "add", HM_ADD_ORDER_POINTS);

        // get the brewery ID from Order
        $brewery_id = 0; // get from first product ACF > brewery object
        $brewerty_user_id = 0; // user associated to the brewery when partners, via ACF


        // send email to brewery telling that the payment was done
        // if config constant is true & if brewery have an email like that
        hops_send_brewery_email_with_order($brewery_id, $brewerty_user_id, $order_id);

        
        // add notification to brewery
        hops_add_notification($brewerty_user_id, "¡El cliente pagó su pedido! '¡A prepararlo!", "info");


        // send and notification/email to the user telling that the order is being processed.
        hops_add_notification($userId, "¡Pago procesando! La cervecería está preprando tu pedido.", "info");
        // log stuff for backup
        hops_log("Orden pagada. 
                    OrderID: ".$order_id." / 
                    Customer: ".$userId." / 
                    Payment: ".$order->get_payment_method_title(). " (".$order->get_payment_method().")
                    Scores: ".($scoreRes ? HM_ADD_ORDER_POINTS : "false" ));
  
    }

    // //$order_total = $order->get_formatted_order_total();
    // $order_total = $order->get_total();

    // error_log(print_r('order total: ' . $order_total, true));
}

?>