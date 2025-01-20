<?php

declare(strict_types=1);

namespace WoowaWebhooks;

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

    public WhatsAppMessenger $whatsapp;

    /**
     * Application constructor.
     * Initializes the environment, retrieves the payload, handles it, and sends a message.
     */
    public function __construct ()
    {
        // Initialize environment variables
        self::init_env();
        
        // Retrieve the payload from the request
        $payload = $this->get_payload();

        // WhatsAppMessageHandler
        $this->whatsapp = new WhatsAppMessenger();

        // Handle the payload
        $this->handle($payload);
    }

    /**
     * Initializes environment variables using Dotenv.
     */
    private static function init_env (): void
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
    private function get_payload (): ?array
    {
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
    private function handle (?array $payload): void
    {
        // If the payload is empty, abort; otherwise, process the payload
        empty($payload) ? $this->abort() : $this->process($payload);
    }

    /**
     * Aborts the request processing.
     */
    private function abort (): void
    {
       $this->whatsapp->send_message('Empty payload received', env()->dev_contact);
    }

    /**
     * Processes the payload.
     *
     * @param array $payload The payload to process.
     */
    private function process (array $payload): void
    {
        $this->whatsapp->send_message(debug($payload), env()->dev_contact);
    }
}