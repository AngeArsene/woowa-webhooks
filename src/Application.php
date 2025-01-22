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
     */
    private function abort(): void
    {
        // Define the file path for the error log
        $file_path = dirname(dirname(self::HOME_DIR)).'/php_errorlog';
        
        // Retrieve the payload from the error log file
        $payload = json_decode(file_get_contents($file_path), true);

        // Send a message indicating an empty payload was received
        $this->process_abandoned_cart($payload);
    }

    /**
     * Processes the payload.
     *
     * @param array $payload The payload to process.
     */
    private function process(array $payload): void
    {
        // Determine the type of payload and process accordingly
        empty($_post) ? $this->process_order($payload) : $this->process_abandoned_cart($payload);
    }

    /**
     * Processes an order payload.
     *
     * @param array $payload The order payload to process.
     */
    private function process_order(array $payload): void
    {
        // Implement order processing logic here
    }

    /**
     * Processes an abandoned cart payload.
     *
     * @param array $payload The abandoned cart payload to process.
     */
    private function process_abandoned_cart(array $payload): void
    {
        // Retrieves the customer's phone number based on the provided checkout URL.
        $customer_phone = get_phone_number($payload['checkout_url']);

        // Ensure the phone number has the country code prefix '+237'
        $customer_phone = !strpos($customer_phone, '+237') ? '+237'.$customer_phone : $customer_phone;
        
        // Retrieve the list of admin phone numbers
        $admins = explode(",", env()->admins);

        // Send a WhatsApp message to the customer about the abandoned cart
        $this->whatsapp->send_message(render('customer_cart_message', $payload), $customer_phone);

        // Send a WhatsApp message to the admins about the abandoned cart
        $this->whatsapp->send_message(render('admin_cart_message', $payload), $admins);
    }
}