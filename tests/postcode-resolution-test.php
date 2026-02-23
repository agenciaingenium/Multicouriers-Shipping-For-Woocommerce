<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!function_exists('remove_accents')) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Test bootstrap shim for WordPress core helper.
    function remove_accents($text)
    {
        $map = array(
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Ñ' => 'N', 'ñ' => 'n',
        );
        return strtr((string) $text, $map);
    }
}

require_once dirname(__DIR__) . '/includes/class-mcws-chile-address.php';

function mcws_assert_same($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI test output.
        fwrite(STDERR, "FAIL: {$message}. Esperado '{$expected}', obtenido '{$actual}'." . PHP_EOL);
        exit(1);
    }
}

$mcws_reflection = new ReflectionClass('MCWS_Chile_Address');
$mcws_property = $mcws_reflection->getProperty('postal_codes');
$mcws_property->setAccessible(true);
$mcws_property->setValue(null, array(
    'CL' => array(
        'CL-RM' => array(
            'SANTIAGO' => '8320000',
            'SAN BERNARDO' => '8050000',
        ),
        'CL-VS' => array(
            'VINA DEL MAR' => '2520000',
        ),
    ),
));

mcws_assert_same(
    '8320000',
    MCWS_Chile_Address::resolve_postal_code('CL-RM', 'Santiago', 'CL'),
    'Debe resolver match exacto de comuna'
);

mcws_assert_same(
    '2520000',
    MCWS_Chile_Address::resolve_postal_code('CL-VS', 'Viña', 'CL'),
    'Debe resolver match parcial (normalizado)'
);

mcws_assert_same(
    '',
    MCWS_Chile_Address::resolve_postal_code('CL-RM', 'Comuna Inexistente', 'CL'),
    'Debe devolver vacio si no existe comuna'
);

mcws_assert_same(
    '',
    MCWS_Chile_Address::resolve_postal_code('CL-RM', 'Santiago', 'US'),
    'Debe devolver vacio para pais no CL'
);

// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI test output.
fwrite(STDOUT, "OK: postcode-resolution-test.php" . PHP_EOL);
