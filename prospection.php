<?php

// Enable strict type checking for better type safety
declare(strict_types=1);

// Autoload dependencies using Composer's autoloader
require 'vendor/autoload.php';

// Import the Prospector class from the WoowaWebhooks namespace for use
use WoowaWebhooks\Prospector;

// Instantiate the Prospector class and execute the prospect_prospects method
(new Prospector())->prospect_prospects();