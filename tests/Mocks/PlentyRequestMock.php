<?php

namespace Findologic\Tests\Mocks;

use Plenty\Plugin\Http\Request;

class PlentyRequestMock extends Request
{
    /**
     * @inheritDoc
     */
    public function all(): array
    {
    }

    /**
     * @inheritDoc
     */
    public function merge(array $input)
    {
    }

    /**
     * @inheritDoc
     */
    public function replace(array $input)
    {
    }

    public function get(string $key, $default = null, bool $deep = false)
    {
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
    }

    /**
     * @inheritDoc
     */
    public function getRequestUri(): string
    {
    }

    /**
     * @inheritDoc
     */
    public function exists($key): bool
    {
    }

    /**
     * @inheritDoc
     */
    public function has($key): bool
    {
    }

    /**
     * @inheritDoc
     */
    public function input(string $key = null, $default = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function only($keys): array
    {
    }

    /**
     * @inheritDoc
     */
    public function except($keys): array
    {
    }

    /**
     * @inheritDoc
     */
    public function query(string $key = null, $default = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $key): bool
    {
    }

    /**
     * @inheritDoc
     */
    public function header(string $key = null, $default = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function isJson(): bool
    {
    }

    /**
     * @inheritDoc
     */
    public function wantsJson(): bool
    {
    }

    /**
     * @inheritDoc
     */
    public function accepts($contentTypes): bool
    {
    }

    /**
     * @inheritDoc
     */
    public function prefers($contentTypes): string
    {
    }

    /**
     * @inheritDoc
     */
    public function acceptsJson(): bool
    {
    }

    /**
     * @inheritDoc
     */
    public function acceptsHtml(): bool
    {
    }

    /**
     * @inheritDoc
     */
    public function format(string $default = "html"): string
    {
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo(): string
    {
    }

    /**
     * @inheritDoc
     */
    public function getHttpHost(): string
    {
    }

    /**
     * @inheritDoc
     */
    public function getSchemeAndHttpHost(): string
    {
    }

    /**
     * @inheritDoc
     */
    public function getUri(): string
    {
    }

    /**
     * @inheritDoc
     */
    public function getUriForPath(string $path): string
    {
    }

    /**
     * @inheritDoc
     */
    public function getQueryString(): string
    {
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
    }

    /**
     * @inheritDoc
     */
    public function getLocale(): string
    {
    }
}
