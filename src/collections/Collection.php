<?php

declare(strict_types=1);

namespace WoowaWebhooks\Collections;

abstract class Collection
{
    protected array $data;
    
    abstract public function get(): array;
}
