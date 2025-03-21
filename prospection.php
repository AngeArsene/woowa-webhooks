<?php

// Enable strict type checking
declare(strict_types=1);

// Autoload dependencies
require 'vendor/autoload.php';

// Import the Prospector class from the WoowaWebhooks namespace
use WoowaWebhooks\Prospector;

// Create a new instance of the Prospector class
while (true) { (new Prospector())->prospect_prospects(); sleep(3600); }