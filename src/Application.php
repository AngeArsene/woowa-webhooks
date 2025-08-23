<?php

declare(strict_types=1);

namespace WoowaWebhooks;

use Exception;
use Dotenv\Dotenv;
use Cocur\Slugify\Slugify;
use WoowaWebhooks\Services\Spreadsheets;
use WoowaWebhooks\Services\GoogleSheets;
use WoowaWebhooks\Services\WooCommerceApi;
use WoowaWebhooks\Services\WhatsAppMessenger;
use WoowaWebhooks\Collections\NewOrderCollection;

/**
 * The main application class for handling webhooks.
 */
final class Application
{
    /**
     * The home directory of the application.
     */
    public const HOME_DIR = __DIR__.'/../';

    /**
     * Instance of WhatsAppMessenger for sending messages.
     *
     * @var WhatsAppMessenger
     */
    public WhatsAppMessenger $whatsapp;

    /**
     * @var GoogleSheets $google_sheet An instance of the GoogleSheets class used for interacting with Google Sheets.
     */
    public GoogleSheets $google_sheet;

    /**
     * @var Spreadsheets $spreadsheet An instance of the Spreadsheets class.
     */
    public Spreadsheets $spreadsheet;

    /**
     * @var string $status The current status of the application.
     */
    private string $status;

    /**
     * @var WooCommerceApi $woocommerce An instance of the WooCommerceApi class used to interact with the WooCommerce API.
     */
    private WooCommerceApi $woocommerce;

    /**
     * Application constructor.
     * Initializes the environment, retrieves the payload, handles it, and sends a message.
     */
    public function __construct()
    {
        // Initialize environment variables
        self::init_env();

        // Initialize WhatsAppMessenger service
        $this->whatsapp = new WhatsAppMessenger();

        // Retrieve the payload from the specified file and handle it
        $this->handle($this->get_payload('ac_payload.json'));
    }

    /**
     * Initializes environment variables using Dotenv.
     *
     * @return void
     */
    public static function init_env(): void
    {
        // Create a Dotenv instance and load environment variables
        $dotenv = Dotenv::createImmutable(self::HOME_DIR);
        $dotenv->load();
    }

    /**
     * Retrieves the payload from the request.
     * 
     * @param string|null $file Optional path to a file.
     * 
     * @return array|null The payload as an associative array or null if decoding fails.
     */
    private function get_payload(?string $file = null): ?array
    {
        // Check if the $_POST array is not empty and return its contents if true.
        if (!empty($_POST)) return $_POST;

        // Set the content type to application/json
        header('Content-Type: application/json');

        // Decode the JSON payload from the request body
        // If a file path is provided, read from that file; otherwise, read from php://input.
        return json_decode(file_get_contents(empty($file) ? 'php://input' : self::HOME_DIR."/$file"), true);
    }

    /**
     * Handles the payload by either aborting or processing it.
     *
     * @param ?array $payload The payload to handle.
     * @return void
     */
    private function handle(?array $payload): void
    {
        try {
            // If the payload is empty, abort; otherwise, process the payload
            empty($payload) ? $this->abort() : $this->process($payload);
        } catch (Exception $error) {
            // Log the error message
            error_log($error->getMessage());
        }
    }

    /**
     * Aborts the request processing.
     *
     * @return void
     */
    private function abort(): void
    {
        // Send a WhatsApp message to the developer contact about the invalid payload
        $this->whatsapp->send_message('Invalid payload received.', env()->dev_contact);
    }

    /**
     * Processes the payload.
     *
     * @param array $payload The payload to process.
     * @return void
     */
    private function process(array $payload): void
    {
        // Determine the status from the payload, using 'status' if available, otherwise fallback to 'order_status'.
        $this->status = $payload['status'] ?? $payload['order_status'];

        // Switch based on the status of the payload
        switch ($this->status) {
            case 'abandoned':
                // If the status is 'abandoned', process the abandoned cart
                $this->process_abandoned_cart($payload);
                break;
            
            case 'processing':
                // If the status is 'processing', process the order
                $this->process_order($payload);
                break;
            
            default:
                // For any other status, abort the processing
                $this->abort();
                break;
        }
    }

    /**
     * Processes an order payload.
     *
     * @param array $payload The order payload to process.
     * 
     * @return void
     */
    private function process_order(array $payload): void
    {
        // Filter the payload to only include required fields
        $payload = NewOrderCollection::filter($payload);

        // Retrieve the customer's phone number.
        $customer_phone = $payload['phone_number'] = get_phone_number($payload);

        // Render the admin message template with the payload data
        $admin_message = render('admin_order_message', $payload);

        // Check if the customer's phone number is registered on WhatsApp
        if (WhatsAppMessenger::check_number($customer_phone)) {
            // Send a WhatsApp message to the customer about the new order
            $this->whatsapp->send_message(render('customer_order_message', $payload), $customer_phone);
        } else {
            // Append a message to the admin message indicating the customer's phone number is not on WhatsApp
            $admin_message .= "\n\n*_PS: Le numéro du client n'a pas WhatsApp ; vous feriez mieux de l'appeler._*";
        }

        // Send a WhatsApp message to the admins about the new order
        $this->whatsapp->send_message($admin_message, explode(",", env()->admins));

        // Store the payload in the Google Sheets spreadsheet
        $this->store($payload);
    }

    /**
     * Processes an abandoned cart payload.
     *
     * @param array $payload The abandoned cart payload to process.
     * 
     * @return void
     */
    private function process_abandoned_cart(array $payload): void
    {
        // Retrieves the customer's phone number.
        $payload['phone_number'] = get_phone_number($payload);
        $customer_phone = WhatsAppMessenger::check_number($payload['phone_number']);

        // Format the product names and add them to the payload
        $payload['product_names'] = formate(product_names($payload['product_names']));

        // Render the admin messages
        $admin_message = render('admin_cart_message', $payload);

        if ($customer_phone) {
            $user_message = render('customer_cart_message', $payload);
    
            // Send a WhatsApp message to the customer about the abandoned cart
            $this->whatsapp->send_message($user_message, $customer_phone);
            // Send a scheduled message to the customer about the abandoned cart
            $this->whatsapp->send_schaduler($user_message, $customer_phone, intervals());
        } else {
            // Append a message to the admin message indicating the customer's phone number is either not on WhatsApp or is invalid.
            $admin_message .= 
                "\n\n*_PS: Le numéro du client n'a pas WhatsApp ou est invalide._*";
        }
        
        // Send a WhatsApp message to the admins about the abandoned cart
        $this->whatsapp->send_message($admin_message, explode(",", env()->admins));
        
        // Store the payload in the Google Sheets spreadsheet
        $this->store($payload);
    }

    /**
     * Stores the given payload.
     *
     * @param array $payload The data to be stored.
     *
     * @return void
     */
    private function store (array $payload): void
    {
        // Log the stored payload
        error_log(debug($payload));

        // Initialize WooCommerceApi, GoogleSheets and Spreadsheets instance 
        $this->woocommerce  = new WooCommerceApi();
        $this->google_sheet = new GoogleSheets();
        $this->spreadsheet  = new Spreadsheets();

        switch ($this->status) {
            case 'abandoned':
                $this->cart_prospection($payload);
                break;
            
            case 'processing':
                $this->new_order_prospection($payload);
                break;
        }
        
        // Store the payload in the Google Sheets spreadsheet
        $this->google_sheet->append([$payload['first_name'], $payload['last_name'], get_phone_number($payload)]);
    }

    /**
     * Handles the cart prospection process.
     *
     * @param array $payload The data payload for the cart prospection.
     *
     * @return void
     */
    private function cart_prospection(array $payload): void
    {
        $product_name  = preg_split('/-{5,}/', $payload['product_names'])[0];
        $product       = $this->woocommerce->get_product_data($product_name);
        $product_image = get_image_links_from($payload['product_table'])[0];

        $this->spreadsheet->append_row([
            $product_name,
            $product['price'] ?? $payload['cart_total'], 
            $product['link'] ?? 'https://allready.cm/product/'.((new Slugify())->slugify($product_name)), 
            $product_image
        ]);
    }

    /**
     * Handles the creation of a new order prospection.
     *
     * @param array $payload The data associated with the new order.
     *
     * @return void
     */
    private function new_order_prospection (array $payload): void
    {
        $product_name  = trim(explode(' - ', preg_split('/-{5,}/', $payload['product_names'])[0])[0]);
        $product       = $this->woocommerce->get_product_data($product_name);
        $product_image = $payload['product_image'];


        $this->spreadsheet->append_row([
            $product_name, 
            $payload['product_price'] ?? $product['price'], 
            $payload['product_link'] ?? $product['link'], 
            $product_image
        ]);
    }
}