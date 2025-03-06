<?php

declare(strict_types=1);

namespace WoowaWebhooks;

use WoowaWebhooks\Services\GoogleSheets;

final class Prospector
{
    public GoogleSheets $google_sheet;

    public function __construct()
    {
        $this->google_sheet = new GoogleSheets();

        $this->run();
    }

    public function get_bounds(): array
    {
        return [
            'lower_bound'    => $this->google_sheet->last_row_num(),
            'customer_count' => random_int(3, 6),
            'upper_bound'    => random_int(0, 50),
        ];
    }

    public function run(): void
    {
        $this->prospect();
    }

    private function get_prospects (array $ranges): array
    {
        $prospects = [];

        foreach ($ranges as $range) {
            $prospects[] = $this->google_sheet->read("A$range:C$range");
        }

        return $prospects;
    }

    private function prospects_ranges (): array
    {
        $ranges = [];
        $bounds = $this->get_bounds();

        for ($i = 0; $i < $bounds['customer_count']; $i++) {
            $ranges[] = $this->google_sheet->last_row_num() - random_int(0, $bounds['upper_bound']);
        }

        return $ranges;
    }

    private function prospect (): void
    {
        var_dump($this->get_prospects($this->prospects_ranges()));
    }
}