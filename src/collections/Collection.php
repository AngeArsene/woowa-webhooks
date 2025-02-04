<?php

declare(strict_types=1);

namespace WoowaWebhooks\Collections;

/**
 * Class Collection
 * 
 * An abstract base class for collections.
 */
abstract class Collection
{
    /**
     * @var array $data The data stored in the collection.
     */
    protected static array $data;

    /**
     * Initialize the collection with the given payload.
     * 
     * @param array $payload The data to initialize the collection.
     */
    protected static function bootstrap(array $payload): void
    {
        // Set the data for the collection
        self::$data = $payload;
    }
        
    /**
     * Filter the collection data.
     * 
     * @param array $data The data to filter.
     * @return array The filtered data.
     */
    abstract public static function filter(array $data): array;
}
