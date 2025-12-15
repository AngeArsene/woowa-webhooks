<?php

declare(strict_types=1);

namespace WoowaWebhooks\Services;

use Google\Client;
use Google\Service\Sheets;
use WoowaWebhooks\Application;
use Google\Service\Sheets\ValueRange;

/**
 * Class GoogleSheets
 * 
 * @method void update(array $values, string|null $range = null) Update a specific range in the Google Sheets spreadsheet.
 * @method void append(array $values, string|null $range = null) Append values to the Google Sheets spreadsheet.
 * 
 * This class provides functionality to interact with Google Sheets API.
 */
final class GoogleSheets 
{
    /**
     * @var Client $google_client Google API client
     */
    private Client $google_client;

    /**
     * @var Sheets $google_sheets Google Sheets service
     */
    private Sheets $google_sheets;

    /**
     * @const string SPREADSHEET_ID The ID of the Google Spreadsheet
     */
    private const SPREADSHEET_ID = '1W5Uzf6R0s58oMCVcUlFJfnw0dA6Bza2L_qD5Q2ZTERE';

    /**
     * GoogleSheets constructor.
     * Initializes the Google Sheets service.
     */
    public function __construct ()
    {
        $this->bootstrap(); // Initialize the Google client

        $this->google_sheets = new Sheets($this->google_client); // Create a new Sheets service instance
    }

    /**
     * Initialize the Google client with necessary configurations.
     */
    private function bootstrap (): void
    {
        $this->google_client = new Client(); // Create a new Google client
        $this->google_client->setApplicationName("Google Sheets API PHP"); // Set the application name
        $this->google_client->setScopes([Sheets::SPREADSHEETS]); // Set the required scopes

        $file_path = (realpath(Application::ENV_DIR) ? Application::ENV_DIR : Application::HOME_DIR) . '/credentials.json';

        $this->google_client->setAuthConfig($file_path); // Set the authentication configuration
        $this->google_client->setAccessType('online'); // Set the access type
    }

    /**
     * Read data from a specified range in the Google Spreadsheet.
     * 
     * @param string $range The range of cells to read
     * @return array The values read from the specified range
     */
    public function read (string $range): array
    {
        $response = $this->google_sheets->spreadsheets_values->get(self::SPREADSHEET_ID, 'Sheet1!'.$range); // Get the values from the specified range
        return empty($response->getValues()) ? [] : $response->getValues(); // Return the values
    }

    /**
     * Magic method to handle dynamic method calls for updating or appending values to a Google Sheets spreadsheet.
     *
     * @param string $name The name of the method being called ('update' or 'append').
     * @param array $arguments The arguments passed to the method. The first argument should be the values to update/append,
     *                         and the second argument (optional) should be the range or cell to update/append to.
     *
     * @throws \BadMethodCallException If the method name is not 'update' or 'append'.
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'update' || $name === 'append') {
            $body = new ValueRange(['values' => [$arguments[0]]]);
            $params = ['valueInputOption' => 'RAW'];
            
            $this->google_sheets->spreadsheets_values->{$name}(
                self::SPREADSHEET_ID, "Sheet1".($name === 'update' ? '!' : '').($arguments[1] ?? null), $body, $params
            );
        } else {
            // Throw an exception if the method is not recognized
            throw new \BadMethodCallException("Method $name does not exist on " . __CLASS__);
        }
    }

    /**
     * Retrieves the last row from the Google Sheets.
     *
     * @return array The last row data as an associative array.
     */
    public function last_row(): array
    {
        $values = $this->read('A:C');
        
        return end($values); // Get the last row correctly
    }

    /**
     * Get the number of the last row in the Google Sheets.
     *
     * @return int The number of the last row.
     */
    public function last_row_num(): int
    {
        return count($this->read('A:C')); // Get the last row correctly
    }
}