<?php

declare(strict_types=1);

use GuzzleHttp\Client;

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
    $phone_number =  $payload['phone_number'] ?? ($payload['phone'] ?? cart_phone_number($payload['checkout_url']));
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