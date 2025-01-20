<?php

declare(strict_types=1);

namespace WoowaWebhooks\Services;

use WoowaWebhooks\Request;

/**
 * Handles sending messages via WhatsApp.
 */
final class WhatsAppMessenger implements MessageHandler
{
    /**
     * The HTTP client used for sending requests.
     */
    private Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Sends a message via WhatsApp.
     *
     * @param string $message The message to send.
     * @param string $to The recipient's phone number.
     * @return bool|string True on success, or the error message on failure.
     */
    public function send_message (string $message, string $to): bool | string
    {
        return $this->__('send_message', $message, $to);
    }

    private function __ (string $url, string $message, string $to, ?array $data = null): bool | string
    {
        $body  = $this->base_params($message, $to) + (array) $data;

        return $this->request->send('post', $url, $body);
    }

    /**
     * Generates the base parameters for the request.
     *
     * @param string $message The message to send.
     * @param string $to The recipient's phone number.
     * @return array The base parameters for the request.
     */
    private function base_params (string $message, string $to): array
    {
        // Return the base parameters as an associative array
        return [
            'phone_no' => $to, 'key' => env()->api_key, 'message' => $message
        ];
    }
}