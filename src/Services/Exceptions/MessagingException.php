<?php

declare(strict_types=1);

namespace WoowaWebhooks\Services\Exceptions;

use Exception;

/**
 * Class MessagingException
 * Custom exception for handling messaging errors.
 */
final class MessagingException extends Exception
{
    /**
    * The error code.
    * @var int
    */
    protected $code = 1000;
    
    /**
    * The error message.
    * @var string
    */
    protected $message;

    /**
     * Default error message for the exception.
     */
    private const DEFAULT_MESSAGE = "An error occurred while handling the message.";

    /**
     * MessagingException constructor.
     *
     * @param string $message Custom error message.
     */
    public function __construct(string $message = self::DEFAULT_MESSAGE)
    {
        $this->message = $message;
        parent::__construct($message, $this->code);
    }
}