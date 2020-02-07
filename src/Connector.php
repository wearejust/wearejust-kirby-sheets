<?php

namespace Wearejust\KirbySheets;

use Exception;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_ValueRange;

class Connector
{
    /**
     * @var \Google_Service_Sheets
     */
    private $sheets;

    public function __construct()
    {
        $client = new Google_Client();
        $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
        $client->setAccessType('offline');

        $file = option('wearejust/kirby-sheets.authentication_file');
        if (! file_exists($file)) {
            throw new \RuntimeException(sprintf('File [%s] doesn\'t exist', $file));
        }

        $key = json_decode(file_get_contents($file), true);

        $client->setAuthConfig($key);

        $this->sheets = new Google_Service_Sheets($client);
    }

    public function append($spreadsheetId, string $sheetName, array $data)
    {
        $range = "{$sheetName}!A1:Z1";

        $this->createTabIfNonExistent($spreadsheetId, $sheetName);

        $rows = $this->sheets->spreadsheets_values->get($spreadsheetId, $range, ['majorDimension' => 'ROWS']);

        $this->createHeadings($spreadsheetId, $sheetName, array_keys($data), $rows, $range);
        $this->createRow($spreadsheetId, $sheetName, $rows, $data);

        return true;
    }

    private function createTabIfNonExistent($spreadsheetId, $sheetName)
    {
        try {
            $body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
                'requests' => [
                    'addSheet' => [
                        'properties' => [
                            'title' => $sheetName
                        ]
                    ],
                ]
            ]);
            $this->sheets->spreadsheets->batchUpdate($spreadsheetId ,$body);
        } catch(Exception $ignore) {}
    }

    private function createHeadings($spreadsheetId, $sheetName, array $headings, $rows, $range)
    {
        if ($rows->values === null || count($rows->values) === 0) {
            $requestBody = new Google_Service_Sheets_ValueRange();
            $requestBody->setValues(['values' => $headings]);
            $this->sheets->spreadsheets_values->append($spreadsheetId, $range, $requestBody, ['valueInputOption' => 'RAW']);

            $this->protectHeadings($spreadsheetId, $sheetName);
        }
    }

    private function protectHeadings($spreadsheetId, $sheetName)
    {
        foreach($this->sheets->spreadsheets->get($spreadsheetId)->getSheets() as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheetName) {

                try {
                    $body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
                        'requests' => [
                            'addProtectedRange' => [
                                'protectedRange' => [
                                    'range' => [
                                        'sheetId' => $sheet->getProperties()->getSheetId(),
                                        'startRowIndex' => 0,
                                        'endRowIndex' => 1,
                                    ],
                                    'description' => 'Protecting headers',
                                    'warningOnly' => false,
                                    'editors' => [
                                        // 'users' => (array) $key['client_email'],
                                        'domainUsersCanEdit' => false,
                                    ]
                                ]
                            ],
                        ]
                    ]);
                    $this->sheets->spreadsheets->batchUpdate(env('GOOGLE_SPREADSHEET_ID'),$body);
                } catch(Exception $ignore) {}
            }
        }
    }

    private function createRow($spreadsheetId, $sheetName, $rows, $data)
    {
        if (! $rows['values'][0]) {
            $keys = array_combine(array_keys($data), array_keys($data));
        } else {
            $keys = array_combine($rows['values'][0], $rows['values'][0]);
        }

        foreach($keys as $key) {
            $keys[$key] = $data[$key] ?? '';
        }

        $range = "{$sheetName}!A2";
        $requestBody = new Google_Service_Sheets_ValueRange();
        $requestBody->setValues(['values' => array_values($keys)]);

        $this->sheets->spreadsheets_values->append($spreadsheetId, $range, $requestBody, ['valueInputOption' => 'RAW']);
    }
}
