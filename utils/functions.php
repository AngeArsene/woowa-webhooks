<?php

declare(strict_types=1);

// use DOMXPath;
// use DOMDocument;
use GuzzleHttp\Client;

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

/**
 * Retrieves the phone number from the provided cart URL.
 *
 * This function sends a GET request to the cart URL using a Guzzle client, then parses the response to extract the phone number from the billing form data.
 *
 * @param string $cart_url The URL of the cart page
 * @return string|null The extracted phone number, or null if an error occurs
 */
function cart_phone_number(string $cart_url) : ?string
{
    // Step 1: Create a Guzzle client
    $client = new Client();

    try {

        // Step 2: Send a GET request to the URL
        $response = $client->get($cart_url);

        // Step 3: Load the HTML response
        $html = (string) $response->getBody();
        libxml_use_internal_errors(true); // Suppress warnings from malformed HTML
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Step 4: Extract pre-filled form data
        $formData = [];

        // Get all input fields
        $inputs = $xpath->query('//input');
        foreach ($inputs as $input) {
            if ($input instanceof DOMElement) { // Check if it's a DOMElement
                $name = $input->getAttribute('name');
                $value = $input->getAttribute('value');
                if ($name) {
                    $formData[$name] = $value; // Store name-value pairs
                }
            }
        }

        return $formData['billing_phone'];

    } catch (\Throwable $th) { return null; }
}

/**
 * Retrieves the phone number from the payload or cart URL.
 *
 * This function extracts the phone number from the payload or cart URL,
 * ensures it has the country code prefix '+237', and removes any spaces.
 *
 * @param array $payload The payload containing the phone number or cart URL.
 * @return string The formatted phone number.
 */
function get_phone_number(array $payload): string
{
    // Retrieve the phone number from the payload or cart URL
    $phone_number =  $payload['phone_number'] ?? ($payload['phone'] ?? cart_phone_number($payload['checkout_url']));

    // Ensure the phone number has the country code prefix '+237'
    $phone_number = strpos($phone_number, '+237') === false ? '+237'.$phone_number : $phone_number;

    // Remove any spaces from the phone number
    return str_replace(' ', '', $phone_number);
}

/**
 * Splits a string of product names into an array.
 *
 * This function takes a string of product names separated by commas and spaces,
 * and splits it into an array. If the last product name contains an ampersand (&),
 * it will be split further into separate names.
 *
 * @param string $products A string of product names separated by commas and spaces.
 *                         The last product name can contain an ampersand (&) to separate names.
 * @return array An array of product names.
 */
function product_names(string $products): array
{
    $product_names = explode(', ', $products);

    $last = explode(' & ', array_pop($product_names));

    return array_merge($product_names, $last);
}

/**
 * Formats an array of product names into a string with each product name followed by a line of dashes.
 *
 * @param array $product_names An array of product names to be formatted.
 * @return string A formatted string where each product name is followed by a line of dashes.
 */
function formate(array $product_names): string
{
    $products = '';

    foreach ($product_names as $product) {
        $products .= $product."\n";
        for ($i = 0; $i < strlen($product); $i++) {
            $products .= '-';
        }
        $products .= "\n";
    }

    return $products;
}

/**
 * Retrieves image links from the provided HTML content.
 *
 * This function parses the HTML content to extract the 'src' attributes of all image elements.
 *
 * @param string $html The HTML content to parse.
 * @return array An array of image URLs.
 */
function get_image_links_from(string $html): array
{
    // Create a new DOMDocument instance
    $dom = new DOMDocument();

    // Suppress warnings due to malformed HTML
    libxml_use_internal_errors(true);
    
    // Load the HTML
    $dom->loadHTML($html);
    
    // Restore error handling
    libxml_clear_errors();

    // Get all image elements
    $images = $dom->getElementsByTagName('img');
    
    // Initialize an array to hold image links
    $imageLinks = [];

    // Loop through the images and extract the 'src' attribute
    foreach ($images as $img) {
        if ($img instanceof DOMElement) {
            $imageLinks[] = $img->getAttribute('src');
        }
    }

    return $imageLinks;
}

/**
 * Gets a future date and time in Jakarta timezone.
 *
 * @param string $time_offset The time offset to add (e.g., '+1 day', '+2 hours').
 * @return string The future date and time in 'Y-m-d H:i' format.
 */
function jakarta_date(string $time_offset): string
{
    // Create a new DateTime object with the specified time offset and Jakarta timezone
    $date = new DateTime($time_offset, new DateTimeZone("Asia/Jakarta"));

    // Display the updated date and time
    return $date->format('Y-m-d H:i');
}

/**
 * Retrieves the intervals for sending scheduled messages and converts them to Jakarta time.
 *
 * @return array An array of formatted date and time strings in Jakarta timezone.
 */
function intervals(): array
{
    return array_map(
        fn ($interval) => 
        jakarta_date($interval), explode(", ", env()->ca_intervals)
    );
}