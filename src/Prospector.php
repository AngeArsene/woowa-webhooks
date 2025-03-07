<?php

declare(strict_types=1);

namespace WoowaWebhooks;

use WoowaWebhooks\Services\GoogleSheets;

/**
 * Class Prospector
 *
 * This is a final class that represents a Prospector.
 * 
 * @package woowa-webhooks
 */
final class Prospector
{
    /**
     * @var GoogleSheets $google_sheet Instance of GoogleSheets class.
     */
    public GoogleSheets $google_sheet;

    /**
     * Prospector constructor.
     * Initializes the GoogleSheets service and runs the prospecting process.
     */
    public function __construct()
    {
        $this->google_sheet = new GoogleSheets(); // Initialize GoogleSheets service

        $this->run(); // Start the prospecting process
    }

    /**
     * Get the bounds for prospecting.
     *
     * @return array An array containing lower_bound, customer_count, and upper_bound.
     */
    public function get_bounds(): array
    {
        return [
            'lower_bound'    => $this->google_sheet->last_row_num(), // Get the last row number from Google Sheets
            'customer_count' => random_int(3, 6), // Generate a random customer count between 3 and 6
            'upper_bound'    => random_int(0, 50), // Generate a random upper bound between 0 and 50
        ];
    }

    /**
     * Run the prospecting process.
     */
    public function run(): void
    {
        $this->prospect(); // Call the prospect method
    }

    /**
     * Get prospects from the specified ranges.
     *
     * @param array $ranges An array of ranges to read from Google Sheets.
     * @return array An array of prospects.
     */
    private function get_prospects(array $ranges): array
    {
        $prospects = [];

        foreach ($ranges as $range) {
            $prospects[] = $this->google_sheet->read("A$range:C$range"); // Read data from Google Sheets for the given range
        }

        return $prospects;
    }

    /**
     * Generate ranges for prospecting.
     *
     * @return array An array of ranges.
     */
    private function prospects_ranges(): array
    {
        $ranges = [];
        $bounds = $this->get_bounds(); // Get the bounds for prospecting

        for ($i = 0; $i < $bounds['customer_count']; $i++) {
            $ranges[] = $this->google_sheet->last_row_num() - random_int(0, $bounds['upper_bound']); // Generate a range based on the bounds
        }

        return $ranges;
    }

    /**
     * Perform the prospecting process.
     */
    private function prospect(): void
    {
        $this->get_prospects($this->prospects_ranges()); // Output the prospects
    }
}