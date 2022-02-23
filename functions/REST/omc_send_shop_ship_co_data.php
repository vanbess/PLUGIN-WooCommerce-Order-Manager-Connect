<?php

// custom endpoint to return shipping company data as set up via SBWC Background Shipping plugin
function sbwc_omc_return_ship_co_data()
{

    // return 'blah';

    // if background shipping plugin not installed, return error
    if (!class_exists('SBBG_Shipping')) :
        return ['message' => 'SBWC Background Shipping plugin not installed on this site. This plugin must be installed and activated, and shipping companies and associated tracking info set up for Order Manager to work.'];
    endif;
    
    // if sequential order numbers pro plugin not installed, return error
    if (!class_exists('WC_Seq_Order_Number_Pro')) :
        return ['message' => 'WooCommerce Sequential Order Numbers Pro plugin not installed on this site. This plugin must be installed and activated for Order Manager to work correctly.'];
    endif;
    
    // if shipment tracking plugin is not installed, return error
    if (!class_exists('WC_Shipment_Tracking')) :
        return ['message' => 'WooCommerce Shipment Tracking plugin not installed on this site. This plugin must be installed and activated for Order Manager to work correctly.'];
    endif;

    /* get shipping company data */
    $shipping_co_data = maybe_unserialize(get_option('sbbg_shipping_cos'));

    if ($shipping_co_data && !empty($shipping_co_data)) :
        return $shipping_co_data;
    else :
        return ['message' => 'Shipping company data not defined for this store. Please ensure that you have defined shipping company data on the admin page of SBWC Background shipping plugin.'];
    endif;
}

// register REST route
add_action('rest_api_init', function () {
    register_rest_route(
        'wc/v3',
        '/retrieve_ship_cos',
        [
            'methods'  => 'GET',
            'callback' => 'sbwc_omc_return_ship_co_data',
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
        ]
    );
});
