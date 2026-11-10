<?php
declare(strict_types=1);
namespace App\Services;

class Throttle
{
    /** @var array<string,float> */
    private static array $last = [];

    public static function wait(string $key, float $seconds): void
    {
        $now = microtime(true);
        if (isset(self::$last[$key]) && ($delta = $now - self::$last[$key]) < $seconds) {
            usleep((int)(($seconds - $delta) * 1_000_000));
            $now = microtime(true);
        }
        self::$last[$key] = $now;
    }
}
