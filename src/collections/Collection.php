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
    protected array $data;

    /**
     * Filter the collection data.
     * 
     * @return array The filtered data.
     */
    abstract public function filter(): array;
}
