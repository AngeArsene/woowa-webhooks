<?php

declare(strict_types=1);

namespace WoowaWebhooks;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;

/**
 * Class Request
 * Handles sending HTTP requests using GuzzleHttp.
 */
final class Request
{   
    /**
    * The HTTP client used for sending requests.
    * @var Client
    */
    private Client $http_client;
    
    /**
    * Default headers for the HTTP requests.
    */
    private const HEADERS = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];
    
    /**
    * Constructor to initialize the HTTP client.
    */
    public function __construct ()
    {
        // Initialize the HTTP client with SSL verification disabled
        $this->http_client = new Client();
    }

    /**
    * Sends an HTTP request.
    *
    * @param string $method The HTTP method to use (e.g., 'GET', 'POST').
    * @param string $to The endpoint to send the request to.
    * @param array $body The body of the request.
    * @return bool|string Returns true on success, or an error message on failure.
    */
    public function send (string $method, string $to, array $body): bool | string
    {
        // Convert method to uppercase
        $method = strtoupper($method);
        
        // Format the body as JSON
        $body = json_encode($body);

        // Create a new HTTP request
        $request = new Psr7Request($method, env()->base_url.$to, self::HEADERS, $body);

        try {
            // Send the HTTP request
            $this->http_client->sendAsync($request)->wait();
            return true;

        } catch (\Throwable $error) {
            // Return the error message if the request fails
            return $error->getMessage();
        }
    }
}
