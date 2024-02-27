<?php
namespace Andrey\Symfony\Messenger\Mongo\Types;

final readonly class Helper
{
    public static function camelToSnake(string $string): string
    {
        $pattern = '/(?<=\\w)(?=[A-Z])|(?<=[a-z])(?=[0-9])/';
        return strtolower(preg_replace($pattern, '_', $string));
    }

    public static function buildUri(array $parts): string
    {
        $user = $parts['user'] ?? '';
        $pass = $parts['pass'] ?? '';
        $host = $parts['host'] ?? '';
        $port = $parts['port'] ?? '';
        $path = $parts['path'] ?? '';

        return
            $user .
            ($pass ? ':' : '') .
            $pass .
            (($pass || $user) ? '@' : '') .
            $host .
            ($port ? ':' : '') .
            $port .
            $path;
    }
}
