<?php

namespace Findologic\Tests\Overrides;

use Plenty\Log\Contracts\LoggerContract;

class FakeLogger implements LoggerContract
{
    private $filename;

    public function __construct()
    {
        $this->filename = __DIR__ . '/../logs/tests-' . date('Y-m-d') . '.log';
    }

    private function log(string $message, $value = null)
    {
        $log = sprintf("[%s] %s: %s", date('Y-m-d H:i:s'), $message, json_encode($value, JSON_PRETTY_PRINT));
        file_put_contents($this->filename, $log, FILE_APPEND);
    }

    public function debug(string $message, $value = null)
    {
        $this->log($message, $value);
    }

    public function info(string $message, $value = null)
    {
        $this->log($message, $value);
    }

    public function error(string $message, $value = null)
    {
        $this->log($message, $value);
    }

    public function notice(string $message, $value = null)
    {
        $this->log($message, $value);
    }

    public function warning(string $message, $value = null)
    {
        $this->log($message, $value);
    }

    public function report(string $message, $value = null)
    {
        $this->log($message, $value);
    }

    public function critical(string $message, $value = null)
    {
        $this->log($message, $value);
    }

    public function alert(string $message, $value = null)
    {
        $this->log($message, $value);
    }

    public function emergency($message, $value = null)
    {
        $this->log($message, $value);
    }

    public function logException(\Exception $exception, int $traceDepth = 3)
    {
        $this->log($exception->getMessage(), $exception->getTrace());
    }

    public function setReferenceType(string $referenceType): LoggerContract
    {
        return $this;
    }

    public function setReferenceValue($referenceValue): LoggerContract
    {
        return $this;
    }

    public function addReference(string $referenceType, int $referenceValue): LoggerContract
    {
        return $this;
    }

    public function addPlaceholder(string $placeholderName, $placeholderValue): LoggerContract
    {
        return $this;
    }
}
