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
 * Renders a template with the given variables.
 *
 * @param string $template The name of the template file.
 * @param array|null $variables The variables to replace in the template.
 * @return string The rendered template.
 */
function render (string $template, ?array $variables = []): string
{
    // Start output buffering
    ob_start();

    // Include the template file
    require_once __DIR__."/../templates/{$template}.txt";

    // Get the contents of the output buffer and clean it
    return replace_placeholders(ob_get_clean(), $variables);
}

/**
 * Replaces placeholders in a template with the given variables.
 *
 * @param string $template The template content.
 * @param array|null $variables The variables to replace in the template.
 * @return string The template with placeholders replaced.
 */
function replace_placeholders(string $template, ?array $variables = [])
{
    // Iterate over the variables array and replace placeholders
    foreach ($variables as $key => $value) {
        // Replace the placeholder with the actual value
        $template = str_replace("[$key]", $value, $template);
    }
    // Return the template with placeholders replaced
    return $template;
}