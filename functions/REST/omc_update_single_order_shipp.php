<?php

// custom endpoint to update shipping data for single order
function sbwc_omc_update_single_order($data)
{

    // retrieve shipping company data
    $shipping_co_data = maybe_unserialize(get_option('sbbg_shipping_cos'));

    // setup logging vars
    $log_time_date = date('j F Y h:i:s', strtotime('now'));

    // setup vars
    $order_num  = $data['order_no'];
    $status     = 'wc-completed';
    $company_id = $data['ship_co_id'];
    $track_no   = $data['track_no'];

    // define shipping company
    if (substr($track_no, 0, 2) == '1Z') :
        $shipping_co_name = 'UPS';
    elseif (isset($shipping_co_data[$company_id]['name'])) :
        $shipping_co_name = $shipping_co_data[$company_id]['name'];
    else :
        $shipping_co_name = $shipping_co_data[1]['name'];
    endif;

    // retrieve order id from order number
    $order_id = wc_seq_order_number_pro()->find_order_by_order_number($order_num);

    // if shipping data already exists, continue
    if (get_post_meta($order_id, '_wc_shipment_tracking_items', true)) :
        return "Shipment/tracking data for Order Number $order_num already present. Please update order manually if you need to change tracking details.";
    endif;

    // add tracking number
    wc_st_add_tracking_number($order_id, $track_no, $shipping_co_name);

    // update order status
    $order_data = new WC_Order($order_id);
    $order_data->update_status($status, __('Order shipping info added - Tracking No ' . $track_no . '.'));
    $order_data->save_meta_data();

    // append update result to log
    $log_msg = "$log_time_date - Order ID $order_id updated with shipment tracking number $track_no";
    file_put_contents(SBOMC_PATH . 'log/order_update_log.txt', $log_msg . PHP_EOL, FILE_APPEND);

    // success/error messages
    if (get_post_meta($order_id, '_wc_shipment_tracking_items', true)) :
        return "Shipment/tracking data successfully updated for Order Number $order_num";
    else :
        return "Shipment/tracking data could not be updated for Order Number $order_num. Please try again or insert tracking data manually.";
    endif;
}

// register REST route
add_action('rest_api_init', function () {
    register_rest_route(
        'wc/v3',
        '/update_single_order',
        [
            'methods'  => 'POST',
            'callback' => 'sbwc_omc_update_single_order',
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
        ]
    );
});
