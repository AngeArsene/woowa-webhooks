<?php

declare(strict_types=1);

namespace WoowaWebhooks;

use Automattic\WooCommerce\Client;
use WoowaWebhooks\Services\Spreadsheets;
use WoowaWebhooks\Services\GoogleSheets;
use WoowaWebhooks\Services\WhatsAppMessenger;
use WoowaWebhooks\Services\WooCommerceApi;

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
     * @var WooCommerceApi $woocommerce The WooCommerce client instance used to interact with the WooCommerce API.
     */
    private WooCommerceApi $woocommerce;

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
        $this->woocommerce  = new WooCommerceApi(); // Initialize the WooCommerce API client
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
            'upper_bound'    => 300, // Generate a random upper bound between 0 and 300
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
            $prospects[] = $this->google_sheet->read("A$range:D$range") + ['range' => $range]; // Read data from Google Sheets for the given range
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
    public function prospect_prospects(): void
    {
        // Retrieve prospect information from Google Sheets based on generated ranges
        $prospects_info = $this->get_prospects($this->prospects_ranges()); 
        $message_info   = $this->spreadsheet->get_random_row(); 

        // Prepare the payload with product details from the random row
        $payload = [
            'product_name'  => preg_replace('/\R/', '', $message_info[0]), // Remove any line breaks from the product name
            'product_price' => $message_info[1], // Extract the product price
            'product_link'  => $message_info[2], // Extract the product link
        ];

        echo debug($payload);
        echo debug($prospects_info);
        
        foreach ($prospects_info as $prospect_info) {
            if (is_seven_days_before($prospect_info[3] ?? "")) {
                $phone_number = sanitize_phone_number($prospect_info[2]); // Extract the phone number from the prospect info

                $this->google_sheet->update([date('m/d/Y')], 'D'.$prospect_info[4]); // Update the Google Sheets with the prospect info
                
                // Send a French prospection message to the prospect
                $this->whatsapp->send_message(
                    render(
                        'fr_prospection_message', array_merge($payload, ['first_name' => $prospect_info[0]]) // Merge payload with the prospect's first name
                    ), $phone_number, $message_info[3]
                );
    
                // Send an English prospection message to the prospect
                $this->whatsapp->send_message(
                    render(
                        'en_prospection_message', array_merge($payload, ['first_name' => $prospect_info[0]]) // Merge payload with the prospect's first name
                    ), $phone_number, $message_info[3]
                );
            }
        }

        error_log(debug($payload));
    }

    /**
     * Sends prospecting messages to a randomly generated prospects.
     * 
     * This method retrieves random prospect information and a random message row
     * from a Spreadsheet. It prepares a payload containing product details 
     * and sends both French and English prospection messages to each prospect via WhatsApp.
     * 
     * @return void
     */
    public function prospect_random_prospects(): void
    {
        // Retrieve prospect information from Google Sheets based on generated ranges
        $prospects_info = [[0 => '', 2 => random_phone_number()]]; // Placeholder for prospect info
        $message_info   = $this->spreadsheet->get_random_row();

        // Prepare the payload with product details from the random row
        $payload = [
            'product_name'  => preg_replace('/\R/', '', $message_info[0]), // Remove any line breaks from the product name
            'product_price' => $message_info[1], // Extract the product price
            'product_link'  => $message_info[2], // Extract the product link
        ];

        echo debug($payload);
        echo debug($prospects_info);
        
        foreach ($prospects_info as $prospect_info) {
            $phone_number = sanitize_phone_number($prospect_info[2]); // Extract the phone number from the prospect info

            // Send a French prospection message to the prospect
            $this->whatsapp->send_message(
                render(
                    'fr_prospection_message', array_merge($payload, ['first_name' => $prospect_info[0]]) // Merge payload with the prospect's first name
                ), $phone_number, $message_info[3]
            );

            // Send an English prospection message to the prospect
            $this->whatsapp->send_message(
                render(
                    'en_prospection_message', array_merge($payload, ['first_name' => $prospect_info[0]]) // Merge payload with the prospect's first name
                ), $phone_number, $message_info[3]
            );
        }

        error_log(debug($payload));
    }

    /**
     * Fills a spreadsheet with product data from a specified WooCommerce category.
     *
     * @param ?int $category_id The ID of the product category to retrieve.
     *
     * @return void
     */
    public function fill_products_sheets(null|int|string $category_id): void
    {
        $page = 1;
        $per_page = 100;

        while ($products = $this->woocommerce->api_client->get('products', [
            'category' => $category_id,
            'per_page' => $per_page,
            'page' => $page++
        ])) {
            foreach ($products as $product) {
                $data = [$product->name, $product->price, $product->permalink, $product->images[0]->src];
                var_dump($data);
                $this->spreadsheet->append_row($data);
            }
        }
    }
}