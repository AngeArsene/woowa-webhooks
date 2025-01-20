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
    protected $message = "An error occurred while handling the message.";
}