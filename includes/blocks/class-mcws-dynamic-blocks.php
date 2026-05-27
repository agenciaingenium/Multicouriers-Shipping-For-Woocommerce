<?php

if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('MCWS_Abstract_Blocks_Support')) {
    class MCWS_Dynamic_Rates_Blocks_Support extends MCWS_Abstract_Blocks_Support
    {
        protected $name = 'mcws_dynamic_rates';
    }
}
