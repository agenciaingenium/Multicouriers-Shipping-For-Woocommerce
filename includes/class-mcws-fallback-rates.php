<?php

if (!defined('ABSPATH')) {
    exit;
}

class MCWS_Fallback_Rates
{
    public static function resolve_cost(array $destination, float $default = 0.0): float
    {
        $region = isset($destination['state']) ? self::normalize((string) $destination['state']) : '';
        $city = isset($destination['city']) ? self::normalize((string) $destination['city']) : '';

        $rows = class_exists('MCWS_Admin') ? MCWS_Admin::get_fixed_rates_table() : array();
        if (!is_array($rows)) {
            return max(0, $default);
        }

        $regionMap = array();
        $communeByRegion = array();
        $communeGlobal = array();
        $excludeRules = array();

        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['scope'], $row['cost'])) {
                continue;
            }

            if (!is_numeric((string) $row['cost'])) {
                continue;
            }

            $cost = (float) $row['cost'];
            $scope = (string) $row['scope'];
            if ($scope === 'region') {
                $regionCode = isset($row['region']) ? self::normalize((string) $row['region']) : '';
                if ($regionCode !== '') {
                    $regionMap[$regionCode] = $cost;
                }
                continue;
            }

            if ($scope !== 'commune') {
                continue;
            }

            $regionCode = isset($row['region']) ? self::normalize((string) $row['region']) : '';
            $mode = isset($row['commune_mode']) ? (string) $row['commune_mode'] : 'only';
            if (!in_array($mode, array('all', 'only', 'exclude'), true)) {
                $mode = 'only';
            }
            $communes = self::normalize_communes($row);

            if ($mode === 'all') {
                if ($regionCode !== '') {
                    $regionMap[$regionCode] = $cost;
                }
                continue;
            }

            if ($mode === 'exclude') {
                if ($regionCode === '' || empty($communes)) {
                    continue;
                }
                $excludeRules[] = array(
                    'region' => $regionCode,
                    'excluded' => array_fill_keys($communes, true),
                    'cost' => $cost,
                );
                continue;
            }

            if (empty($communes)) {
                continue;
            }
            foreach ($communes as $commune) {
                if ($regionCode !== '') {
                    if (!isset($communeByRegion[$regionCode])) {
                        $communeByRegion[$regionCode] = array();
                    }
                    $communeByRegion[$regionCode][$commune] = $cost;
                } else {
                    $communeGlobal[$commune] = $cost;
                }
            }
        }

        if ($city !== '' && $region !== '' && isset($communeByRegion[$region], $communeByRegion[$region][$city])) {
            return max(0, $communeByRegion[$region][$city]);
        }

        if ($city !== '' && isset($communeGlobal[$city])) {
            return max(0, $communeGlobal[$city]);
        }

        if ($region !== '' && $city !== '' && !empty($excludeRules)) {
            for ($i = count($excludeRules) - 1; $i >= 0; $i--) {
                $rule = $excludeRules[$i];
                if (!is_array($rule) || (string) ($rule['region'] ?? '') !== $region) {
                    continue;
                }
                $excluded = isset($rule['excluded']) && is_array($rule['excluded']) ? $rule['excluded'] : array();
                if (!isset($excluded[$city])) {
                    return max(0, (float) ($rule['cost'] ?? 0.0));
                }
            }
        }

        if ($region !== '' && isset($regionMap[$region])) {
            return max(0, $regionMap[$region]);
        }

        return max(0, $default);
    }

    private static function normalize(string $value): string
    {
        return strtoupper(remove_accents(trim($value)));
    }

    private static function normalize_communes(array $row): array
    {
        $communes = array();
        if (isset($row['communes']) && is_array($row['communes'])) {
            foreach ($row['communes'] as $commune) {
                $normalized = self::normalize((string) $commune);
                if ($normalized !== '') {
                    $communes[] = $normalized;
                }
            }
        } else if (isset($row['commune'])) {
            $normalized = self::normalize((string) $row['commune']);
            if ($normalized !== '') {
                $communes[] = $normalized;
            }
        }

        return array_values(array_unique($communes));
    }
}
