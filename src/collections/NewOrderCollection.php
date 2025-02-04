<?php

declare(strict_types=1);

namespace WoowaWebhooks\Collections;

/**
 * Class OrderCollection
 * 
 * A collection class for handling orders.
 */
final class NewOrderCollection extends Collection
{
    private static array $billing;

    protected static function bootstrap(array $payload): void
    {
        parent::bootstrap($payload);
        self::$billing = $payload['billing'];
    }
    /**
     * Filter the collection data.
     * 
     * @return array The filtered data.
     */
    public static function filter(array $payload): array
    {
        self::bootstrap($payload);

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

    private static function product_names(array $products): string
    {
        $product_names = array_map(
            fn ($item) => 
                $item['name']." - ".$item['price']."CFA x ".$item['quantity'], $products
        );

        return formate($product_names);
    }
}
