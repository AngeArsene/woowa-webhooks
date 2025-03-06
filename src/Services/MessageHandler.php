<?php

declare(strict_types=1);

namespace WoowaWebhooks\Services;

/**
 * Interface for handling messages.
 */
interface MessageHandler
{
    /**
     * Sends a message or an image URL.
     *
     * @param string $message The message to send.
     * @param string $to The recipient's phone number.
     * @param string|array|null $url The URL(s) of the image(s) to send.
     * @return void
     */
    public function send_message(string $message, string $to, string|array|null $url = null): void;
}