<?php

declare(strict_types=1);

/**
 * Renders a template with the given variables.
 *
 * @param string $template The name of the template file.
 * @param array|null $variables The variables to replace in the template.
 * @return string The rendered template.
 */
function render (string $template, ?array &$variables = []): string
{
    ob_start();
    require_once __DIR__."/../templates/{$template}.txt";
    return replace_placeholders(ob_get_clean(), $variables);
}

/**
 * Replaces placeholders in a template with the given variables.
 *
 * @param string $template The template content.
 * @param array|null $variables The variables to replace in the template.
 * @return string The template with placeholders replaced.
 */
function replace_placeholders(string $template, ?array &$variables = [])
{
    foreach ($variables as $key => $value) {
        if (is_array($value)) {
            $value = count($value) > 1 ? $value = implode(" / ", $value) : $value[0];
            $variables[$key] = $value;
        }
        $template = str_replace("[$key]", "$value", $template);
    }
    return $template;
}