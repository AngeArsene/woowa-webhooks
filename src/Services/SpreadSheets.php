<?php

declare(strict_types=1);

namespace WoowaWebhooks\Services;

use Exception;
use WoowaWebhooks\Application;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Class Spreadsheets
 * 
 * This class is responsible for handling operations related to spreadsheets.
 * 
 * @package Services
 */
final class Spreadsheets
{
    private string      $file_path;
    private Worksheet   $sheet;
    private Spreadsheet $spreadsheet;

    /**
     * SpreadSheets constructor.
     *
     * @param string|null $file_path The path to the Excel file. Defaults to Application::HOME_DIR . "/files/data.xlsx".
     */
    public function __construct (?string $file_path = Application::HOME_DIR . "/files/data.xlsx")
    {
        $this->file_path = $file_path;

        if (file_exists($file_path)) {
            $this->spreadsheet = IOFactory::load($file_path);
        } else {
            $this->spreadsheet = new Spreadsheet();
            $this->spreadsheet->getActiveSheet()->setTitle("Sheet1");
        }

        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    /**
     * Adds a new row to the spreadsheet.
     *
     * @param array $data An associative array containing the data for the new row.
     *
     * @return void
     *
     * @throws Exception If the row number is out of range.
     */
    public function edit_row (int $row_num, array $data): void
    {
        if (!($row_num <= 2 || $row_num > $this->sheet->getHighestRow())) {
            $col = 'A';
            foreach ($data as $cell) {
                $this->sheet->setCellValue($col . $row_num, $cell);
                $col++;
            }
    
            $this->save();
        } else {
            throw new Exception(
                "Row number must be greater than 2 and less than or equal to the highest row."
            );
        }
    }

    /**
     * Deletes a row from the spreadsheet.
     *
     * @param int $row_num The number of the row to delete.
     *
     * @return void
     */
    public function delete_row (int $row_num): void
    {
        if (!($row_num < 1 || $row_num > $this->sheet->getHighestRow())) {
            $this->sheet->removeRow($row_num);
            $this->save();
        } else {
            throw new Exception(
                "Row number must be greater than 1 and less than or equal to the highest row."
            );
        }
    }

    /**
     * Saves the current state of the spreadsheet.
     *
     * This method is responsible for persisting any changes made to the spreadsheet.
     *
     * @return void
     */
    private function save (): void
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($this->file_path);
    }
}