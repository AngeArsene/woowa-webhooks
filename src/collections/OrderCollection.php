<?php

declare(strict_types=1);

namespace WoowaWebhooks\Collections;

/**
 * Class OrderCollection
 * 
 * A collection class for handling orders.
 */
final class OrderCollection extends Collection
{
    /**
     * Constructor for OrderCollection.
     * 
     * @param array $data The data to be stored in the collection.
     */
    public function __construct(protected array $data) {}

    /**
     * Filter the collection data.
     * 
     * @return array The filtered data.
     */
    public function filter(): array
    {
        // Return the data as is
        return $this->data;
    }
}
