<?php

// Enable strict type checking to enforce type declarations
declare(strict_types=1);

// Autoload dependencies using Composer's autoloader
require 'vendor/autoload.php';

// Import the Prospector class from the WoowaWebhooks namespace
use WoowaWebhooks\Prospector;

// Check if the category_id argument is provided
if (isset($argv[1])) {
    // Get the category_id from the command-line arguments
    $category_id = $argv[1];

    // Create an instance of the Prospector class and call the fill_products_sheets method
    (new Prospector())->fill_products_sheets($category_id);
} else {
    echo "Please provide a category_id as an argument.\n";
}
