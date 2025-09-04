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

    /**
     * Splits the rows of the current spreadsheet into two separate spreadsheets
     * based on the validity of WhatsApp numbers.
     *
     * @param string $validOutputFile   The filename for the spreadsheet containing valid WhatsApp numbers.
     * @param string $invalidOutputFile The filename for the spreadsheet containing invalid WhatsApp numbers.
     *
     * @return void
     */
    public function splitRowsByWhatsAppValidity(string $validOutputFile, string $invalidOutputFile): void
    {
        $validSpreadsheet   = new Spreadsheets(Application::HOME_DIR . "/files/$validOutputFile");
        $invalidSpreadsheet = new Spreadsheets(Application::HOME_DIR . "/files/$invalidOutputFile");
        $headers = ['Owner', 'Phone', 'Status'];

        foreach ([$validSpreadsheet, $invalidSpreadsheet] as $id => $sheet) {
            $sheet->append_row($headers, true);
            $sheet->center_row(1);
            $sheet->bold_row(1);
            $sheet->set_row_background(1, ($id === 0 ? '00AF50' : 'FE0000'));
        }

        for ($i = 2; $i <= $this->spreadsheet->last_row_num(); $i++) {
            $row = $this->spreadsheet->read_row($i);
            $phone_number = str_replace(" ", "", $row[1]);
            $status = strtolower($row[2] ?? '');

            if ($status === 'valid') {
                $validSpreadsheet->append_row([$row[0], $row[1], 'Valid']);
            } else if ($status === 'invalid') {
                $invalidSpreadsheet->append_row([$row[0], $row[1], 'Invalid']);
            } else {
                $spreadsheet = WhatsAppMessenger::check_number_exists($phone_number) ?
                    'validSpreadsheet' : 'invalidSpreadsheet';

                $status = $spreadsheet === 'validSpreadsheet' ? 'Valid' : 'Invalid';
                $$spreadsheet->append_row([$row[0], $row[1], $status]);
            }

            error_log(debug([$row[0], $row[1], $status]));
        }
    }
}
