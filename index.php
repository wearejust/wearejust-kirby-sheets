<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('wearejust/kirby-sheets', [
    'options' => [
        'authentication_file' => null,
    ]
]);
