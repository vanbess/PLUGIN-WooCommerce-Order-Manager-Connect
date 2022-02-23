<?php

// custom endpoint to return order id by order number
function sbwc_omc_schedule_shipping($data)
{

    // if background shipping plugin not installed, return error
    if (!class_exists('SBBG_Shipping')) :
        return 'SBWC Background Shipping plugin not installed on this site. This plugin must be installed and activated, and shipping companies and associated tracking info set up for Order Manager to work.';
    endif;
    
    // if sequential order numbers pro plugin not installed, return error
    if (!class_exists('WC_Seq_Order_Number_Pro')) :
        return 'WooCommerce Sequential Order Numbers Pro plugin not installed on this site. This plugin must be installed and activated for Order Manager to work correctly.';
    endif;
    
    // if shipment tracking plugin is not installed, return error
    if (!class_exists('WC_Shipment_Tracking')) :
        return 'WooCommerce Shipment Tracking plugin not installed on this site. This plugin must be installed and activated for Order Manager to work correctly.';
    endif;

    // grab submitted shipping data arr
    $shipping_csv = $data['shipping_data'];

    // save $shipping_csv (schedules AS processing update as well)
    $csv_saved = update_option('sbwc_omc_ship_data', maybe_serialize($shipping_csv));

    if ($csv_saved) :
        return 'Shipping update scheduled.';
    else :
        return 'Shipping update not scheduled. Likely reason: identical shipping data submitted for processing.';
    endif;

}

// register REST route
add_action('rest_api_init', function () {
    register_rest_route(
        'wc/v3',
        '/schedule_shipping',
        [
            'methods'  => 'POST',
            'callback' => 'sbwc_omc_schedule_shipping',
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
        ]
    );
});
