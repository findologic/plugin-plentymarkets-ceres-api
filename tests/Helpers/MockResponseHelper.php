<?php

namespace Findologic\Tests\Helpers;

trait MockResponseHelper
{
    public function getMockResponse($file = ''): string
    {
        return file_get_contents(__DIR__ . '/../MockResponses/' . $file);
    }
}
