<?php

if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('MCWS_Abstract_Blocks_Support')) {
    class MCWS_Fixed_Rates_Blocks_Support extends MCWS_Abstract_Blocks_Support
    {
        protected $name = 'mcws_fixed_rates';
    }
}
