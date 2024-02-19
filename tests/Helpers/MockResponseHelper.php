<?php

namespace Findologic\Tests\Helpers;

trait MockResponseHelper
{
    public function getMockResponse($file = 'JSONResponse/demo.json'): string
    {
        return file_get_contents(__DIR__ . '/../MockResponses/' . $file);
    }
}
