<?php

declare(strict_types=1);

namespace WoowaWebhooks\Services;

use Automattic\WooCommerce\Client;

final class WooCommerce
{
    private const STORE_URL   = "https://allready.cm";
    private const API_KEY     = "ck_5b9d6df7ea31f82bc40ed3d80b5288af9088aab5";
    private const API_SECRETE = "cs_556cbc913c5f0ae8b2d0a2becfc06fa964290505";

    public Client $api;

    public function __construct()
    {
        $this->api = new Client(
            self::STORE_URL, self::API_KEY, self::API_SECRETE, ['version' => 'wc/v3']
        );
    }

    public function get_product_data (string $product_name): ?array
    {
        // Search for the product by name
        $products = $this->api->get('products', ['search' => $product_name]);

        if (!empty($products)) {
            $product = $products[0]; // Get the first matching product

            return [
                'link'  => $product->permalink,
                'price' => $product->price
            ];
        }

        return null;
    }
}
