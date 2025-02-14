<?php

declare(strict_types=1);

namespace WoowaWebhooks\Collections;

/**
 * Class NewOrderCollection
 * 
 * A collection class for handling new orders.
 */
final class NewOrderCollection extends Collection
{
    /**
     * @var array $billing The billing information.
     */
    private static array $billing;

    /**
     * Initialize the collection with the given payload.
     * 
     * @param array $payload The data to initialize the collection.
     */
    protected static function bootstrap(array $payload): void
    {
        // Call the parent bootstrap method
        parent::bootstrap($payload);
        // Set the billing information
        self::$billing = $payload['billing'];
    }

    /**
     * Filter the collection data.
     * 
     * @param array $payload The data to filter.
     * @return array The filtered data.
     */
    public static function filter(array $payload): array
    {
        // Initialize the collection with the given payload
        self::bootstrap($payload);

        // Return the filtered data
        return [
            'id'              => self::$data['id'],
            'city'            => self::$billing['city'],
            'email'           => self::$billing['email'],
            'total'           => self::$data['total'],
            'last_name'       => self::$billing['last_name'],
            'first_name'      => self::$billing['first_name'],
            'shipping_total'  => self::$data['shipping_lines'][0]['total'],
            'shipping_method' => self::$data['shipping_lines'][0]['method_title'],
            'payment_method'  => self::$data['payment_method_title'],
            'product_names'   => self::product_names(self::$data['line_items']),
            'neighborhood'    => self::$billing['address_1'],
            'phone_number'    => self::$billing['phone'],
        ];
    }

    /**
     * Get the product names from the given products.
     * 
     * @param array $products The products to get the names from.
     * @return string The formatted product names.
     */
    private static function product_names(array $products): string
    {
        // Map the product names with their price and quantity
        $product_names = array_map(
            fn ($item) => 
                $item['name']." - ".$item['price']."CFA x ".$item['quantity'], $products
        );

        // Return the formatted product names
        return formate($product_names);
    }
}
