<?php

/**
 * Plugin Name: SBWC Order Manager Connect
 * Description: Plugin to connect to Order Manager
 * Version: 1.0.0
 * Author: WC Bessinger
 */

//  prevent direct access
if (!defined('ABSPATH')) :
    exit();
endif;

// constants
define('SBOMC_PATH', plugin_dir_path(__FILE__));
define('SBOMC_URL', plugin_dir_url( __FILE__));

// REST
include SBOMC_PATH.'functions/REST/omc_insert_shipping_data.php';
include SBOMC_PATH.'functions/REST/omc_send_shop_ship_co_data.php';
include SBOMC_PATH.'functions/REST/omc_update_single_order_shipp.php';

// AS
include SBOMC_PATH.'functions/AS/omc_schedule_update.php';