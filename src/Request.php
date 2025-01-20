<?php

declare(strict_types=1);

namespace WoowaWebhooks;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;

final class Request
{   
    /**
    * The HTTP client used for sending requests.
    */
    private Client $http_client;
    
    private const HEADERS = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];
    
    public function __construct ()
    {
        $this->http_client = new Client(['verify' => false]);
    }

    public function send (string $method, string $to, array $body): bool | string
    {
        $method = strtoupper($method); $body = json_format($body);

        // Create a new HTTP request
        $request = new Psr7Request(
            $method, env()->base_url.$to, self::HEADERS, $body
        );

        try {
            // Send the HTTP request
            $this->http_client->send($request);
            return true;

        } catch (\Throwable $error) {
            // Return the error message if the request fails
            return $error->getMessage();
        }
    }
}
