<?php

/**
 * Schedules order update based on remote shipping data received from Order Manager
 * 
 * Schedule orders shipping update
 *
 * @return void
 */
function omc_schedule_update_process()
{
    if (false === as_has_scheduled_action('omc_update_orders_shipping') && get_option('sbwc_omc_ship_data')) :
        as_schedule_single_action(strtotime('now'), 'omc_update_orders_shipping');
    endif;
}

add_action('init', 'omc_schedule_update_process');

/**
 * Process orders shipping update
 *
 * @return void
 */
function omc_process_orders_shipping_update()
{

    // retrieve shipping data
    $shipping_data = maybe_unserialize(get_option('sbwc_omc_ship_data'));

    // check we actually have data and process if true
    if (!empty($shipping_data) && is_array($shipping_data)) :

        // retrieve shipping company data
        $shipping_co_data = maybe_unserialize(get_option('sbbg_shipping_cos'));

        // loop
        foreach ($shipping_data as $index => $ship_data) :

            // setup logging vars
            $log_time_date = date('j F Y h:i:s', strtotime('now'));

            // skip first array (contains column legends)
            if ($index === 0) :
                continue;
            endif;

            // setup vars
            $order_num  = $ship_data[0];
            $status     = $ship_data[1];
            $company_id = $ship_data[2];
            $track_no   = $ship_data[3];

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
                continue;
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

        endforeach;

        // delete shipping data after processing to avoid additional AS runs
        delete_option('sbwc_omc_ship_data');

    endif;
}

add_action('omc_update_orders_shipping', 'omc_process_orders_shipping_update');
