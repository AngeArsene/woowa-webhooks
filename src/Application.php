<?php

declare(strict_types=1);

namespace WoowaWebhooks;

use Exception;
use Dotenv\Dotenv;
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
     * Application constructor.
     * Initializes the environment, retrieves the payload, handles it, and sends a message.
     */
    public function __construct()
    {
        // Initialize environment variables
        self::init_env();

        // Initialize WhatsAppMessenger
        $this->whatsapp = new WhatsAppMessenger();
        
        // Handle the payload
        $this->handle($this->get_payload());
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
        $status = $payload['status'] ?? $payload['order_status'];

        // Switch based on the status of the payload
        switch ($status) {
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
     * @return void
     */
    private function process_order(array $payload): void
    {
        error_log(debug($payload));
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
        // Retrieves the customer's phone number.
        $customer_phone = $payload['phone_number'] = get_phone_number($payload);

        // Format the product names and add them to the payload
        $payload['product_names'] = formate(product_names($payload['product_names']));

        // Render the admin messages
        $admin_message = render('admin_cart_message', $payload);

        if (WhatsAppMessenger::check_number($customer_phone)) {
            $user_message = render('customer_cart_message', $payload);
    
            // Send a WhatsApp message to the customer about the abandoned cart
            $this->whatsapp->send_message($user_message, $customer_phone);
            // Send a scheduled message to the customer about the abandoned cart
            $this->whatsapp->send_schaduler($user_message, $customer_phone, intervals());
        } else {
            // Append a message to the admin message indicating the customer's phone number is not on WhatsApp
            $admin_message .= 
                "\n\n*_PS: Le numéro du client ". 
                (strlen($customer_phone) === 13 ? "n'a pas WhatsApp ; vous feriez mieux de l'appeler" : "est invalide") . "._*";
        }
        
        // Send a WhatsApp message to the admins about the abandoned cart
        $this->whatsapp->send_message($admin_message, explode(",", env()->admins));
    }
}