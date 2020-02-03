<?php

namespace Wearejust\KirbySheets\Uniform;

use Uniform\Actions\Action;
use Uniform\Form;
use Wearejust\KirbySheets\Connector;

class SaveToGoogleSheetsAction extends Action
{
    /**
     * @var \Wearejust\KirbySheets\Connector
     */
    private $connector;

    public function __construct(Form $form, array $options = [])
    {
        $this->connector = new Connector();

        parent::__construct($form, $options);
    }

    public function perform()
    {
        $spreadsheetId = $this->option('spreadsheetId');
        $sheetName = $this->option('name');

        if (! $this->connector->append($spreadsheetId, $sheetName, $this->getData())) {
            $this->fail();
        }
    }

    private function getData() : array
    {
        $exclude = $this->option('exclude', []);
        $exclude[] = '_form';

        return array_filter($this->form->data(), function($key) use ($exclude) {
            return ! in_array($key, $exclude, true);
        }, ARRAY_FILTER_USE_KEY);
    }
}