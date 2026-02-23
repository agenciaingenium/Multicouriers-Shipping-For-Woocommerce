<?php
/**
 * Listado Regiones de Chile
 * @link       https://marketingrapel.cl
 * @since      1.0.0
 * @package    wc-ciudades-y-regiones-de-chile
 * @subpackage wc-ciudades-y-regiones-de-chile/states
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WooCommerce uses this global structure for country states.
global $states;

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WooCommerce uses this global structure for country states.
$states['CL'] = array(
    'CL-AI' => 'Aisén del General Carlos Ibañez del Campo',
	'CL-AN' => 'Antofagasta',
	'CL-AP' => 'Arica y Parinacota',
	'CL-AR' => 'La Araucanía',
	'CL-AT' => 'Atacama',
	'CL-BI' => 'Biobío',
	'CL-CO' => 'Región de Coquimbo',
	'CL-LI' => 'Libertador General Bernardo O\'Higgins',
	'CL-LL' => 'Los Lagos',
	'CL-LR' => 'Los Ríos',
	'CL-MA' => 'Magallanes',
	'CL-ML' => 'Maule',
	'CL-NB' => 'Ñuble',
	'CL-RM' => 'Región Metropolitana de Santiago',
	'CL-TA' => 'Tarapacá',
	'CL-VS' => 'Valparaíso',
);
