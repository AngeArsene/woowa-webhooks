<?php

declare(strict_types=1);

namespace WoowaWebhooks;

use WoowaWebhooks\Services\Spreadsheets;
use WoowaWebhooks\Services\GoogleSheets;
use WoowaWebhooks\Services\WhatsAppMessenger;

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
     * @var Spreadsheets The spreadsheet instance used in the Prospector class.
     */
    public Spreadsheets $spreadsheet;

    /**
     * @var WhatsAppMessenger Instance of WhatsAppMessenger used for sending messages.
     */
    public WhatsAppMessenger $whatsapp;

    /**
     * Prospector constructor.
     * Initializes the GoogleSheets service and runs the prospecting process.
     */
    public function __construct()
    {
        Application::init_env(); // Initialize environment variables
        
        $this->google_sheet = new GoogleSheets(); // Initialize GoogleSheets service
        $this->whatsapp     = new WhatsAppMessenger(); // Initialize WhatsAppMessenger service
        $this->spreadsheet  = new Spreadsheets(); // Get the spreadsheet from Local

        // $this->prospect_prospects(); // Perform the prospecting process

        var_dump($this->get_prospects($this->prospects_ranges()));
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
            'customer_count' => 1, // Generate a random customer count between 3 and 6
            'upper_bound'    => random_int(0, 50), // Generate a random upper bound between 0 and 50
        ];
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

        return [flatten_array($prospects)];
        // return [['Ange', 'Arsene', '+237699512438']];
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
            $ranges[] = $bounds['lower_bound'] - random_int(0, $bounds['upper_bound']); // Generate a range based on the bounds
        }

        return $ranges;
    }

    /**
     * Perform the prospecting process.
     *
     * This method retrieves prospect information from Google Sheets,
     * generates messages, and sends them via WhatsApp.
     */
    private function prospect_prospects(): void
    {
        $prospects_info = $this->get_prospects($this->prospects_ranges()); // Output the prospects
        $message_info   = $this->spreadsheet->get_random_row(); // Get a random row from Local Sheet

        foreach ($prospects_info as $prospect_info) {
            $phone_number = $prospect_info[2]; // Get the phone number from the prospect info

            $this->whatsapp->send_message(
                replace_placeholders($message_info[0], ['first_name' => $prospect_info[0]]
            ), $phone_number, $message_info[2]); // Send a message to the prospect

            $this->whatsapp->send_message(
                replace_placeholders($message_info[1], ['first_name' => $prospect_info[0]]
            ), $phone_number, $message_info[2]); // Send a message to the prospect
        }
    }
}