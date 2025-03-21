<?php

// Enable strict type checking to enforce type declarations
declare(strict_types=1);

// Autoload dependencies using Composer's autoloader
require 'vendor/autoload.php';

// Import the Prospector class from the WoowaWebhooks namespace
use WoowaWebhooks\Prospector;

// Create an instance of the Prospector class and call the fill_products_sheets method
$prospector = (new Prospector())->fill_products_sheets(172);