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
     * Filter the collection data.
     * 
     * @return array The filtered data.
     */
    abstract public function filter(): array;
}
