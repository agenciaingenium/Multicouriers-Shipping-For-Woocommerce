<?php

if (!defined('ABSPATH')) {
    exit;
}

class MCWS_Logger
{
    private const OPTION_KEY = 'mcws_recent_events';
    private const LIMIT = 200;

    public static function info(string $message, array $context = array()): void
    {
        self::write('info', $message, $context);
    }

    public static function warning(string $message, array $context = array()): void
    {
        self::write('warning', $message, $context);
    }

    public static function error(string $message, array $context = array()): void
    {
        self::write('error', $message, $context);
    }

    public static function get_recent(int $limit = 50): array
    {
        $events = get_option(self::OPTION_KEY, array());
        if (!is_array($events)) {
            return array();
        }

        return array_slice($events, 0, max(1, $limit));
    }

    private static function write(string $level, string $message, array $context): void
    {
        $events = get_option(self::OPTION_KEY, array());
        if (!is_array($events)) {
            $events = array();
        }

        array_unshift($events, array(
            'time' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ));

        if (count($events) > self::LIMIT) {
            $events = array_slice($events, 0, self::LIMIT);
        }

        update_option(self::OPTION_KEY, $events, false);

        if (function_exists('wc_get_logger')) {
            wc_get_logger()->log($level, $message . ' ' . wp_json_encode($context), array('source' => 'mcws'));
        }
    }
}
