<?php

declare(strict_types=1);

namespace WoowaWebhooks\Services;

use Automattic\WooCommerce\Client;

/**
 * Class WooCommerceApi
 *
 * This class provides methods to interact with the WooCommerce API.
 * It allows retrieving product data based on the product name.
 *
 * @package Services
 */
final class WooCommerceApi
{
    /**
     * The base URL of the WooCommerce store.
     */
    private const STORE_URL = "https://allready.cm";

    /**
     * The API key for accessing the WooCommerce API.
     */
    private const API_KEY = "ck_5b9d6df7ea31f82bc40ed3d80b5288af9088aab5";

    /**
     * The API secret for accessing the WooCommerce API.
     */
    private const API_SECRETE = "cs_556cbc913c5f0ae8b2d0a2becfc06fa964290505";

    /**
     * @var Client $api_client The API client used for making requests to the WooCommerce API.
     */
    public Client $api_client;

    /**
     * WooCommerceApi constructor.
     *
     * Initializes the WooCommerce API client with the store URL, API key, and API secret.
     */
    public function __construct()
    {
        $this->api_client = new Client(
            self::STORE_URL, self::API_KEY, self::API_SECRETE, ['version' => 'wc/v3']
        );
    }

    /**
     * Retrieves product data based on the provided product name.
     *
     * @param string $product_name The name of the product to retrieve data for.
     * @return array|null An associative array containing product data, or null if the product is not found.
     */
    public function get_product_data (string $product_name): ?array
    {
        // Search for the product by name
        $products = $this->api_client->get('products', ['search' => $product_name]);

        if (!empty($products)) {
            foreach ($products as $product) {
                if (strcasecmp($product->name, $product_name) === 0) {
                    return [
                        'link'  => $product->permalink,
                        'price' => $product->price
                    ];
                }
            }
        }

        return null;
    }
}
