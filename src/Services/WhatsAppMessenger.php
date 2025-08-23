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
     * Sends a message or an image URL via WhatsApp.
     *
     * @param string $message The message to send.
     * @param array|string $to The recipient's phone number(s).
     * @param string|array|null $url The URL(s) of the image(s) to send.
     * @return void
     */
    public function send_message(string $message, array|string $to, string|array|null $url = null): void
    {
        if ($url === null) {
            $this->send_request('send_message', $message, $to);
        } else {
            $this->send_image_url($url, $to, $message);
        }
    }

    /**
     * Sends one or multiple image URLs via WhatsApp.
     *
     * @param string|array $url The URL(s) of the image(s) to send.
     * @param string|array $to The recipient's phone number.
     * @param string|null $message The message to send along with the image(s).
     * @return void
     */
    private function send_image_url(string|array $url, string | array $to, ?string $message = ''): void
    {
        if (is_array($url) && count($url) > 1) {
            $last = $url[array_key_last($url)];
            foreach ($url as $image) {
                if ($image === $last) {
                    $this->send_request('send_image_url', $message, $to, ['url' => $image]);
                    break;
                }
                $this->send_request('send_image_url', '', $to, ['url' => $image]);
            }
            return;
        }

        $url = is_array($url) ? $url[0] : $url;

        $this->send_request('send_image_url', $message, $to, ['url' => $url]);
    }

    /**
     * Sends a scheduled message via WhatsApp.
     *
     * @param string $message The message to send.
     * @param array|string $to The recipient's phone number(s).
     * @param string|array $sch_date The scheduled date(s) for sending the message.
     * @return void
     */
    public function send_schaduler(string $message, array|string $to, string|array $sch_date): void
    {
        // Implement sending a scheduled message using WhatsApp
        if (is_string($sch_date)) {
            $this->send_request('scheduler', $message, $to, ["api_type" => "text", 'sch_date' => $sch_date]);
        } else if (is_array($sch_date)) {
            foreach ($sch_date as $date) {
                $this->send_request('scheduler', $message, $to, ["api_type" => "text", 'sch_date' => $date]);
            }
        }
    }

    /**
     * Sends a request to the given URL with the provided message and recipient.
     *
     * @param string $url The URL to send the request to.
     * @param string $message The message to send.
     * @param string|array $to The recipient's phone number.
     * @param array|null $data Additional data to include in the request.
     * @return void
     * @throws MessagingException If the request fails.
     */
    private function send_request(string $url, string $message, string | array $to, ?array $data = null): void
    {
        $to = is_string($to) ? [$to] : $to;

        foreach ($to as $recipient) {
            // Combine base parameters with additional data
            $body = $this->base_params($message, $recipient) + (array) $data;

            // Send the request using the HTTP client
            $request = $this->request->send('post', $url, $body);

            // Throw an exception if the request fails
            if ($request !== true && $url !== 'scheduler') throw new MessagingException($request);
        }
    }

    /**
     * Checks if the given phone number is a whatsapp number.
     *
     * @param string|string[] $phone_number The phone number to check.
     * @return string|string[] Returns true if the phone number is valid, false otherwise.
     */
    public static function check_number(string|array $phone_number): string | array
    {
        if (is_array($phone_number)) {
            return array_filter($phone_number, function ($number) {
                return self::check_number_exists($number);
            });
        }
        // Implement checking if a phone number is valid using WhatsApp
        return self::check_number_exists($phone_number) ? $phone_number : '';
    }

    /**
     * Checks if a phone number exists on WhatsApp.
     *
     * @param string $phone_number The phone number to check.
     * @return bool True if the phone number exists on WhatsApp, false otherwise.
     */
    private static function check_number_exists(string $phone_number): bool
    {
        return (new self())->request->send(
            'post', "check_number", ['phone_no' => $phone_number, 'key' => env()->api_key]
        ) === true;
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