<?php

declare(strict_types=1);

namespace WoowaWebhooks;

use Exception;
use Dotenv\Dotenv;
use WoowaWebhooks\Services\WhatsAppMessenger;

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
     * Application constructor.
     * Initializes the environment, retrieves the payload, handles it, and sends a message.
     */
    public function __construct()
    {
        // Initialize environment variables
        self::init_env();
        
        // Retrieve the payload from the request
        $payload = $this->get_payload();
        
        // Initialize WhatsAppMessenger
        $this->whatsapp = new WhatsAppMessenger();
        
        // Handle the payload
        $this->handle($payload);
    }

    /**
     * Initializes environment variables using Dotenv.
     *
     * @return void
     */
    private static function init_env(): void
    {
        // Create a Dotenv instance and load environment variables
        $dotenv = Dotenv::createImmutable(self::HOME_DIR);
        $dotenv->load();
    }

    /**
     * Retrieves the payload from the request.
     *
     * @return ?array The payload as an associative array or null if decoding fails.
     */
    private function get_payload(): ?array
    {
        // Check if the $_POST array is not empty and return its contents if true.
        if (!empty($_POST)) return $_POST;

        // Set the content type to application/json
        header('Content-Type: application/json');

        // Decode the JSON payload from the request body
        return json_decode(file_get_contents('php://input'), true);
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
        // Set the content type to application/json
        header('Content-Type: application/json');

        // Decode the JSON payload from the request body
        $payload = json_decode(file_get_contents(self::HOME_DIR.'/ac_payload_example'), true);

        // Retrieve image links from the product table in the payload
        $images = get_image_links_from($payload['product_table']);

        $payload['product_names'] = formate(product_names($payload['product_names']));

        // Send a WhatsApp message to the developer about the aborted request
        $this->whatsapp->send_message(render('customer_cart_message', $payload), env()->dev_contact, $images);
    }

    /**
     * Processes the payload.
     *
     * @param array $payload The payload to process.
     * @return void
     */
    private function process(array $payload): void
    {
        // Determine the type of payload and process accordingly
        if (empty($_post)) {
            $this->process_order($payload);
        } else {
            $this->process_abandoned_cart($payload);
        }
    }

    /**
     * Processes an order payload.
     *
     * @param array $payload The order payload to process.
     * @return void
     */
    private function process_order(array $payload): void
    {
        // Implement order processing logic here
    }

    /**
     * Processes an abandoned cart payload.
     *
     * @param array $payload The abandoned cart payload to process.
     * @return void
     */
    private function process_abandoned_cart(array $payload): void
    {
        error_log(debug($payload));
        // Retrieves the customer's phone number based on the provided checkout URL.
        $customer_phone =  get_phone_number($payload['checkout_url']) ?? $payload['phone'];

        // Ensure the phone number has the country code prefix '+237'
        $customer_phone = strpos($customer_phone, '+237') === false ? '+237'.$customer_phone : $customer_phone;
        
        // Retrieve the list of admin phone numbers
        $admins = explode(",", env()->admins);

        // Add the customer's phone number to the payload
        $payload['phone'] = $customer_phone = str_replace(' ', '', $customer_phone);

        // Format the product names and add them to the payload
        $payload['product_names'] = formate(product_names($payload['product_names']));

        // Retrieve image links from the product table in the payload
        $images = get_image_links_from($payload['product_table']);

        // Send a WhatsApp message to the customer about the abandoned cart
        $this->whatsapp->send_message(render('customer_cart_message', $payload), $customer_phone, $images);

        // Send a WhatsApp message to the admins about the abandoned cart
        $this->whatsapp->send_message(render('admin_cart_message', $payload), $admins);
    }
}