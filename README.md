# A way to save records from Kirby3 to Google Sheets

Normally, Kirby doesn't provide a way to save data to a database. You can actually, with using the DB driver. 
The problem with this is that Kirby Panel doesn't provide an easy way to make this data viewable or exportable. 

This package focuses on saving to an external system, namely Google Sheets.

## Installation

You can install the package via composer:

```bash
composer require wearejust/kirby-sheets
```

## Usage

You can save data to Google Sheets using the following code.

```php
use Wearejust\KirbySheets\Connector;

$connector = new Connector();
$spreadsheetId = ID_OF_THE_GOOGLE_SPREADSHEET
$sheetName = NAME_OF_THE_TAB_IN_THE_GOOGLE_SPREADSHEET
$data = ['first_name' => 'Foo', 'last_name' => 'Bar'];

$connector->append($spreadsheetId, $sheetName, $data);

```

It is important that you first create a Google Secret auth file. This file is important because it sets some permissions stuff. You can create your auth file using this guide -> https://www.fillup.io/post/read-and-write-google-sheets-from-php/

In your Kirby `config.php` file, you can set the path to the file:

```php
...
    'wearejust/kirby-sheets' => [
        'authentication_file' => __DIR__ . '/../../google.prod.json',
    ],
...
```

### Usage with KirbyUniform

Most the time, you will probably use Kirby Uniform. We've created an custom action using the following code:

```php
...
    if ($kirby->request()->is('POST')) {
        $sheetName = 'TAB X';
        $spreadsheetId = 'SPREADSHEETID_OF_GOOGLE'
        
        $form
            ->action(new SaveToGoogleSheetsAction($form, [
                'spreadsheetId' => $spreadsheetId,
                'name' => $sheetName,
                'exclude' => ['gdpr'], // You can use the exclude option to exclude keys from the form data
            ]));

        return Header::redirect('/thanks');
    }
...

```

## License

The MIT License (MIT). 
