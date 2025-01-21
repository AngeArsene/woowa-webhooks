<?php

declare(strict_types=1);

/**
 * Retrieves the environment variables as an object.
 *
 * @return object The environment variables.
 */
function env (): object
{
    // Cast the $_ENV superglobal array to an object and return it
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
    // Start output buffering
    ob_start();
    
    // Dump the payload
    var_dump($payload);

    // Get the contents of the output buffer and clean it
    return ob_get_clean();
}