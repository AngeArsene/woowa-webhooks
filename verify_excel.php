<?php

// Enable strict type checking to enforce type declarations
declare(strict_types=1);

// Import the ExcelWhatsAppVerifier class from the WoowaWebhooks namespace
use WoowaWebhooks\ExcelWhatsAppVerifier;

// Autoload dependencies using Composer's autoloader
require 'vendor/autoload.php';

// Create an instance of ExcelWhatsAppVerifier with the specified Excel file
$verifier = new ExcelWhatsAppVerifier('3K_UK_Travel_Agencies.xlsx');

// Start the phone number verification process
$verifier->verifyPhoneNumbers();