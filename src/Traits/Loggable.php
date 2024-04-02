<?php

namespace Findologic\Traits;

trait Loggable
{
    public function getLogger(string $identifier): Logger
    {
        return pluginApp(Logger::class, [$identifier]);
    }
}
