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

/**
 * Formats an associative array into a JSON string.
 *
 * @param array $body The associative array to format.
 * @return string The formatted JSON string.
 */
function json_format (array $body): string
{
    // Define the ending part of the JSON string
    $end = '
        }';

    // Define the indentation space for the JSON string
    $space = '            ';

    // Define the starting part of the JSON string
    $start = '{
            ';

    // Iterate over each key-value pair in the array
    foreach ($body as $key => $value) {
        // Determine if a comma is needed after the current key-value pair
        $comma = array_key_last($body) === $key ? '': ',';
        
        // Determine if additional space is needed before the current key-value pair
        $space_in = array_key_first($body) === $key ? '': $space;
        
        // Append the formatted key-value pair to the JSON string
        $start .= $space_in . '"'.$key.'": "'.$value.'"'.$comma . "\n";
    }

    // Return the complete JSON string
    return $start.$end;
}