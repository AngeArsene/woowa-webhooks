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
    protected static array $data;

    protected static function bootstrap(array $payload): void
    {
        self::$data = $payload;
    }
        
    /**
     * Filter the collection data.
     * 
     * @return array The filtered data.
     */
    abstract public static function filter(array $data): array;
}
