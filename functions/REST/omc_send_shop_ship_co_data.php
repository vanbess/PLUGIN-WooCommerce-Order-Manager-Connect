<?php

// custom endpoint to return shipping company data as set up via SBWC Background Shipping plugin
function sbwc_omc_return_ship_co_data($data)
{

    /* get shipping company data */
    $shipping_co_data = maybe_unserialize(get_option('sbbg_shipping_cos'));

    if ($shipping_co_data) :
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
