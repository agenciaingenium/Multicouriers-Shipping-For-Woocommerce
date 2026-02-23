<?php

if (!defined('ABSPATH')) {
    exit;
}

class MCWS_Fixed_Rates_Method extends WC_Shipping_Method
{
    public function __construct($instance_id = 0)
    {
        $this->id = 'mcws_fixed_rates';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Multicouriers Tarifa Fija', 'multicouriers-shipping-for-woocommerce');
        $this->method_description = __('Tarifas fijas por comuna o region para Chile.', 'multicouriers-shipping-for-woocommerce');
        $this->supports = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
        );

        $this->init();

        $this->enabled = $this->get_option('enabled', 'yes');
        $this->title = $this->get_option('title', __('Envio por region/comuna', 'multicouriers-shipping-for-woocommerce'));
    }

    public function init(): void
    {
        $this->init_form_fields();
        $this->init_settings();

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields(): void
    {
        $this->instance_form_fields = array(
            'enabled' => array(
                'title' => __('Activo', 'multicouriers-shipping-for-woocommerce'),
                'type' => 'checkbox',
                'default' => 'yes',
            ),
            'title' => array(
                'title' => __('Titulo', 'multicouriers-shipping-for-woocommerce'),
                'type' => 'text',
                'default' => __('Envio por region/comuna', 'multicouriers-shipping-for-woocommerce'),
            ),
            'default_cost' => array(
                'title' => __('Tarifa por defecto (CLP)', 'multicouriers-shipping-for-woocommerce'),
                'type' => 'price',
                'default' => '0',
                'description' => __('Se usa solo cuando no hay coincidencia en las reglas definidas en WooCommerce > Multicouriers Tarifas.', 'multicouriers-shipping-for-woocommerce'),
            ),
        );
    }

    public function calculate_shipping($package = array()): void
    {
        $destination = isset($package['destination']) && is_array($package['destination']) ? $package['destination'] : array();

        $region = isset($destination['state']) ? strtoupper(trim((string) $destination['state'])) : '';
        $commune = isset($destination['city']) ? $this->normalize_key($destination['city']) : '';
        $default_cost = (float) $this->get_option('default_cost', '0');
        $global_rows = $this->get_global_rates_rows();

        // If the new global fixed-rates table has rows, use it as the source of truth.
        // This preserves advanced modes like "exclude" that legacy instance textareas cannot represent.
        if (!empty($global_rows) && class_exists('MCWS_Fallback_Rates')) {
            $cost = MCWS_Fallback_Rates::resolve_cost($destination, $default_cost);
        } else {
            $commune_rates = $this->parse_rates_map((string) $this->get_option('commune_rates', ''));
            $region_rates = $this->parse_rates_map((string) $this->get_option('region_rates', ''));
            $cost = null;

            if ($commune !== '' && isset($commune_rates[$commune])) {
                $cost = $commune_rates[$commune];
            }

            if ($cost === null && $region !== '' && isset($region_rates[$region])) {
                $cost = $region_rates[$region];
            }

            if ($cost === null) {
                $cost = max(0, $default_cost);
            }
        }

        $this->add_rate(array(
            'id' => $this->id . ':' . $this->instance_id,
            'label' => $this->title,
            'cost' => $cost,
            'calc_tax' => 'per_order',
        ));
    }

    private function parse_rates_map(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $rates = array();

        if (!is_array($lines)) {
            return $rates;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '=') === false) {
                continue;
            }

            list($key, $value) = array_map('trim', explode('=', $line, 2));
            if ($key === '' || $value === '' || !is_numeric($value)) {
                continue;
            }

            $normalized_key = $this->normalize_key($key);
            $rates[$normalized_key] = (float) $value;
        }

        return $rates;
    }

    private function normalize_key(string $value): string
    {
        $value = strtoupper(trim($value));

        return remove_accents($value);
    }

    private function get_global_rates_maps(): array
    {
        $rows = class_exists('MCWS_Admin') ? MCWS_Admin::get_fixed_rates_table() : array();
        $communes = array();
        $regions = array();

        if (!is_array($rows)) {
            return array($communes, $regions);
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $scope = isset($row['scope']) ? (string) $row['scope'] : '';
            $cost = isset($row['cost']) ? (float) $row['cost'] : null;
            if ($cost === null || $cost < 0) {
                continue;
            }

            if ($scope === 'commune') {
                $key = isset($row['commune']) ? $this->normalize_key((string) $row['commune']) : '';
                if ($key !== '') {
                    $communes[$key] = $cost;
                }
                continue;
            }

            if ($scope === 'region') {
                $key = isset($row['region']) ? $this->normalize_key((string) $row['region']) : '';
                if ($key !== '') {
                    $regions[$key] = $cost;
                }
            }
        }

        return array($communes, $regions);
    }

    private function get_global_rates_rows(): array
    {
        $rows = class_exists('MCWS_Admin') ? MCWS_Admin::get_fixed_rates_table() : array();

        return is_array($rows) ? $rows : array();
    }
}
