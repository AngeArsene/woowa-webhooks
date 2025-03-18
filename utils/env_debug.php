<?php

declare(strict_types=1);

/**
 * Retrieves the environment variables as an object.
 *
 * @return object The environment variables.
 */
function env (): object
{
    return (object) $_ENV;
}

/**
 * Debugs a payload by dumping its contents.
 *
 * @param mixed $payload The payload to debug.
 * @return string The debug output.
 */
function debug (mixed $payload): string
{
    ob_start();
    var_dump($payload);
    return ob_get_clean();
}