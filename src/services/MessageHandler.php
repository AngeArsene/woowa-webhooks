<?php

declare(strict_types=1);

namespace WoowaWebhooks\Services;

interface MessageHandler
{
    /**
     * Sends a message.
     *
     * @param string $message The message to send.
     * @param string $to The recipient's phone number.
     * @return bool|string True on success, or the error message on failure.
     */
    public function send_message (string $message, string $to): bool | string;
}