<?php

declare(strict_types=1);

namespace WoowaWebhooks;

use WoowaWebhooks\Services\Spreadsheets;
use WoowaWebhooks\Services\WhatsAppMessenger;

/**
 * Class ExcelWhatsAppVerifier
 *
 * Handles verification of WhatsApp numbers using data from Excel files.
 *
 * This class provides methods to process Excel spreadsheets and verify
 * WhatsApp numbers, facilitating integration with messaging workflows.
 *
 * @package WoowaWebhooks
 */
class ExcelWhatsAppVerifier
{
    /**
     * Instance of the Spreadsheets class used to manage and interact with spreadsheet data.
     *
     * @var Spreadsheets
     */
    private Spreadsheets $spreadsheet;

    /**
     * Constructor for ExcelWhatsAppVerifier.
     *
     * @param string $file The path to the Excel file to be verified.
     */
    public function __construct(private string $file)
    {
        Application::init_env();

        $this->spreadsheet = new Spreadsheets(
            Application::HOME_DIR . "/files/$file"
        );
    }

    /**
     * Verifies phone numbers extracted from an Excel file.
     *
     * This method processes the phone numbers and performs verification logic,
     * such as checking their validity or formatting.
     *
     * @return void
     */
    public function verifyPhoneNumbers(): void
    {
        for ($i = 2; $i <= $this->spreadsheet->last_row_num(); $i++) {
            $row = $this->spreadsheet->read_row($i);
            $phone_number = str_replace(" ", "", $row[1]);

            $quote = WhatsAppMessenger::check_number_exists($phone_number) ?
                "Valid" :
                "Invalid";
            
            $this->spreadsheet->edit_row($i, [$row[0], $row[1], $quote]);
            error_log(debug([$row[0], $row[1], $quote]));
        }
    }

    public function splitRowsByWhatsAppValidity(string $validOutputFile, string $invalidOutputFile): void
    {

    }
}