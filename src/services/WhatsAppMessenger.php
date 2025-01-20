<?php

declare(strict_types=1);

namespace WoowaWebhooks\Services;

use WoowaWebhooks\Request;
use WoowaWebhooks\Services\Exceptions\MessagingException;

/**
 * Handles sending messages via WhatsApp.
 */
final class WhatsAppMessenger implements MessageHandler
{
    /**
     * The HTTP client used for sending requests.
     *
     * @var Request
     */
    private Request $request;

    /**
     * Constructor initializes the HTTP client.
     */
    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Sends a message via WhatsApp.
     *
     * @param string $message The message to send.
     * @param string $to The recipient's phone number.
     * @return void
     */
    public function send_message(string $message, string $to): void
    {
        $this->send_request('send_message', $message, $to);
    }

    /**
     * Sends a request to the given URL with the provided message and recipient.
     *
     * @param string $url The URL to send the request to.
     * @param string $message The message to send.
     * @param string $to The recipient's phone number.
     * @param array|null $data Additional data to include in the request.
     * @return void
     * @throws MessagingException If the request fails.
     */
    private function send_request(string $url, string $message, string $to, ?array $data = null): void
    {
        // Combine base parameters with additional data
        $body = $this->base_params($message, $to) + (array) $data;

        // Send the request using the HTTP client
        $request = $this->request->send('post', $url, $body);

        // Throw an exception if the request fails
        if ($request !== true) throw new MessagingException();
    }

    /**
     * Generates the base parameters for the request.
     *
     * @param string $message The message to send.
     * @param string $to The recipient's phone number.
     * @return array The base parameters for the request.
     */
    private function base_params(string $message, string $to): array
    {
        // Return the base parameters as an associative array
        return [
            'phone_no' => $to, 'key' => env()->api_key, 'message' => $message
        ];
    }
}