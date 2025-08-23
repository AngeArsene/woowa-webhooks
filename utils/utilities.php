<?php declare(strict_types=1);

use GuzzleHttp\Client;
use WoowaWebhooks\Services\WhatsAppMessenger;

/**
 * Retrieves the phone number from the provided cart URL.
 *
 * @param string $cart_url The URL of the cart page
 * @return string|null The extracted phone number, or null if an error occurs
 */
function cart_phone_number(string $cart_url) : ?string
{
    $client = new Client();
    try {
        $response = $client->get($cart_url);
        $html = (string) $response->getBody();
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $formData = [];
        $inputs = $xpath->query('//input');
        foreach ($inputs as $input) {
            if ($input instanceof DOMElement) {
                $name = $input->getAttribute('name');
                $value = $input->getAttribute('value');
                if ($name) {
                    $formData[$name] = $value;
                }
            }
        }

        return $formData['billing_phone'];

    } catch (\Throwable $th) { return null; }
}

/**
 * Retrieves the phone number from the payload or cart URL.
 *
 * @param array $payload The payload containing the phone number or cart URL.
 * @return string The formatted phone number.
 */
function get_phone_number(array $payload): string
{
    // E.164 regex (max 15 digits)
    $regex = '/^\+([1-9]{1,3})(\d{4,14})$/';

    // Extract phone
    $number = $payload['phone_number']
        ?? ($payload['phone'] ?? cart_phone_number($payload['checkout_url']));

    // Clean unwanted chars
    $number = preg_replace('/[\s\-\(\)]/', '', $number);

    // Case 1: Already valid E.164
    if (preg_match($regex, $number)) {
        return $number;
    }

    // Case 2: Local Cameroon mobile (9 digits starting with 6)
    if (preg_match('/^6\d{8}$/', $number)) {
        $number = '+237' . $number;
        return $number;
    }

    // Case 3: International format without +
    if (preg_match('/^00\d{6,15}$/', $number)) {
        $number = '+' . substr($number, 2); // convert 00 to +
        return preg_match($regex, $number) ? $number : $number;
    }
    if (preg_match('/^[1-9]\d{5,14}$/', $number)) {
        $number = '+' . $number;
        return preg_match($regex, $number) ? $number : $number;
    }

    // Case 4: Default fallback Cameroon
    $number = '+237' . ltrim($number, '0');
    return preg_match($regex, $number) ? $number : $number;
}

/**
 * Sanitizes a phone number by removing non-digit characters and ensuring it includes the country code for Cameroon (+237).
 *
 * @param string $phone_number The phone number to sanitize.
 * @return string The sanitized phone number.
 */
function sanitize_phone_number(string $phone_number): string
{
    // Remove any non-digit characters from the phone number
    $phone_number = preg_replace('/[^\d+]/', '', $phone_number);
    
    $phone_number = strpos($phone_number, '+237') === false ? '+237'.$phone_number : $phone_number;

    return str_replace(' ', '', $phone_number);
}

/**
 * Splits a string of product names into an array.
 *
 * @param string $products A string of product names.
 * @return array An array of product names.
 */
function product_names(string $products): array
{
    $product_names = explode(', ', $products);
    $last = explode(' & ', array_pop($product_names));
    return array_merge($product_names, $last);
}

/**
 * Formats an array of product names into a string.
 *
 * @param array $product_names An array of product names to be formatted.
 * @return string A formatted string.
 */
function formate(array $product_names): string
{
    $products = '';
    $last_product = $product_names[array_key_last($product_names)];
    
    // Iterate through each product name
    foreach ($product_names as $product) {
        // Append the product name followed by a newline
        $products .= $product."\n";
        // Append a line of dashes equal to the length of the product name
        for ($i = 0; $i < strlen($product); $i++) {
            $products .= $last_product === $product ? '' : '-';
        }
        // Append a newline after the dashes
        $products .= $last_product === $product ? "" : "\n";
    }
    // Return the formatted product names string
    return $products;
}

/**
 * Retrieves image links from the provided HTML content.
 *
 * @param string $html The HTML content to parse.
 * @return array An array of image URLs.
 */
function get_image_links_from(string $html): array
{
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    $images = $dom->getElementsByTagName('img');
    $imageLinks = [];
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
 * @param string $time_offset The time offset to add.
 * @return string The future date and time.
 */
function jakarta_date(string $time_offset): string
{
    $date = new DateTime($time_offset, new DateTimeZone("Asia/Jakarta"));
    return $date->format('Y-m-d H:i');
}

/**
 * Retrieves the intervals for sending scheduled messages.
 *
 * @return array An array of formatted date and time strings.
 */
function intervals(): array
{
    return array_map(
        fn ($interval) => 
        jakarta_date($interval), explode(", ", env()->ca_intervals)
    );
}

/**
 * Generates a random phone number.
 *
 * @return string A randomly generated phone number.
 */
function random_phone_number(): string 
{
    // Start the phone number with the country code and a random second digit
    $phone_number = '+2376' . ['7', '9', '5'][random_int(0, 2)];

    // Append a random 7-digit number to the phone number
    $phone_number .= str_pad("".random_int(0, 9999999), 7, '0', STR_PAD_LEFT);

    // Check if the generated phone number is valid using WhatsAppMessenger, otherwise generate a new one
    return WhatsAppMessenger::check_number($phone_number) ? $phone_number : random_phone_number();
}

/**
 * Generates an array of random phone numbers.
 *
 * @param int $count The number of random phone numbers to generate. Default is 1.
 * @return array An array of random phone numbers.
 */
function random_phone_numbers(int $count = 1): array
{
    // Initialize an empty array to store generated phone numbers
    $phone_numbers = [];

    // Loop to generate the specified number of phone numbers
    for ($i = 0; $i < $count; $i++) {
        // Generate a random phone number and add it to the array
        $phone_numbers[] = random_phone_number();
    }

    // Return the array of generated phone numbers
    return $phone_numbers;
}

/**
 * Recursively flattens a deeply nested array.
 *
 * @param array $array The input nested array.
 * @return array A flat array containing only values.
 */
function flatten_array(array $array): array
{
    $result = [];

    array_walk_recursive($array, function ($value) use (&$result) {
        $result[] = $value;
    });
    
    return $result;
}

/**
 * Checks if a given date is 7 days in the past or more.
 *
 * @param string $date The date in d/m/y format.
 * @return bool True if the date is 7 days in the past or more, false otherwise.
 */
function is_seven_days_before(string $date = ''): bool
{
    $input_date = DateTime::createFromFormat('m/d/Y', str_replace("'", '', $date));

    if (!$input_date) return true; // Invalid date format

    $now = new DateTime('now', new DateTimeZone('UTC'));
    $seven_days_ago = $now->modify('-3 days');

    return $input_date <= $seven_days_ago;
}