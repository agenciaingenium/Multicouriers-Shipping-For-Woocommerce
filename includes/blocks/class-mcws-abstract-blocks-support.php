<?php

if (!defined('ABSPATH')) {
    exit;
}

use Automattic\WooCommerce\Blocks\Shipping\AbstractShippingMethodType;

if (!class_exists('MCWS_Abstract_Blocks_Support') && class_exists(AbstractShippingMethodType::class)) {
    abstract class MCWS_Abstract_Blocks_Support extends AbstractShippingMethodType
    {
        public function initialize()
        {
            // No frontend assets needed for registration.
        }

        public function is_active()
        {
            return true;
        }

        public function get_script_handles()
        {
            return array();
        }

        public function get_editor_script_handles()
        {
            return array();
        }

        public function get_script_data()
        {
            return array();
        }
    }
}
