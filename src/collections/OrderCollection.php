<?php

declare(strict_types=1);

namespace WoowaWebhooks\Collections;

final class OrderCollection extends Collection
{
    public function __construct(protected array $data) {}

    public function get(): array
    {
        return $this->data;
    }
}
