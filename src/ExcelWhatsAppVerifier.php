<?php

declare(strict_types=1);

namespace WoowaWebhooks;

use WoowaWebhooks\Services\Spreadsheets;
use WoowaWebhooks\Services\WhatsAppMessenger;

class ExcelWhatsAppVerifier
{
    private Spreadsheets $spreadsheet;

    public function __construct(private string $file)
    {
        Application::init_env();

        $this->spreadsheet = new Spreadsheets(
            Application::HOME_DIR . "/files/$file"
        );
    }

    public function verifyPhoneNumbers(): void
    {
        for ($i = 2; $i < $this->spreadsheet->last_row_num(); $i++) {
            $row = $this->spreadsheet->read_row($i);
            $phone_number = str_replace(" ", "", $row[1]);

            $quote = WhatsAppMessenger::check_number_exists($phone_number) ?
                "Valid" :
                "Invalid";
            
            $this->spreadsheet->edit_row($i, [$row[0], $row[1], $quote]);
            error_log(debug([$row[0], $row[1], $quote]));
        }
    }
}