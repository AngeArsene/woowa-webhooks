<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use WoowaWebhooks\ExcelWhatsAppVerifier;

// Replace with your actual input and output filenames
$verifier = new ExcelWhatsAppVerifier('3K_UK_Travel_Agencies.xlsx');
$verifier->splitRowsByWhatsAppValidity('valid_whatsapp.xlsx', 'invalid_whatsapp.xlsx');