<?php

namespace Wearejust\KirbySheets\Uniform;

use Uniform\Actions\Action;
use Wearejust\KirbySheets\Connector;

class SaveToGoogleSheetsAction extends Action
{
    public function perform()
    {
        $spreadsheetId = $this->option('spreadsheetId');
        $sheetName = $this->option('name');

        $connector = new Connector();

        if (! $connector->append($spreadsheetId, $sheetName, $this->form->data())) {
            $this->fail();
        }
    }
}
