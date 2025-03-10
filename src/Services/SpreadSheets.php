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
     * @param string|null $file_path The path to the Excel file.
     *                               Defaults to Application::HOME_DIR . "/files/data.xlsx".
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
     * Appends a new row at the end of the spreadsheet.
     * Each element of the data array is placed in consecutive columns starting from column A.
     *
     * @param array $data The data to add.
     *
     * @return void
     */
    public function append_row (array $data): void
    {
        $lastRow = $this->sheet->getHighestRow() + 1;
        $col = 'A';

        foreach ($data as $cell) {
            $this->sheet->setCellValue($col . $lastRow, $cell);
            $col++;
        }

        $this->save();
    }

    /**
     * Reads all data from the spreadsheet.
     *
     * Retrieves all rows and columns starting from the second row.
     * Each row is an array of cell values.
     *
     * @return array An array of rows.
     */
    public function read_all (): array
    {
        $highestRow = $this->sheet->getHighestRow();
        $highestColumn = $this->sheet->getHighestColumn();
        $data = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $rowData[] = $this->sheet->getCell($col . $row)->getValue();
            }
            $data[] = $rowData;
        }

        return $data;
    }

    /**
     * Edit a specific row in the spreadsheet with new data.
     *
     * @param int $row_num The row number to be edited.
     * @param array $newData The new data to be inserted into the row.
     *
     * @return void
     *
     * @throws Exception If the row number is out of range.
     */
    public function edit_row (int $row_num, array $newData): void
    {
        if (!($row_num <= 2 || $row_num > $this->sheet->getHighestRow())) {
            $col = 'A';
            foreach ($newData as $cell) {
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
     * Removes the specified row and saves changes.
     * Throws exception if row number is invalid.
     *
     * @param int $row_num The row number to delete.
     *
     * @return void 
     *
     * @throws Exception If the row number is invalid.
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
     * Save the current state of the spreadsheet.
     *
     * This method handles the saving process for the spreadsheet data.
     *
     * @return void
     */
    private function save (): void
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($this->file_path);
    }
}