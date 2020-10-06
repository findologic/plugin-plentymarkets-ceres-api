<?php

namespace Findologic\Components;

use IO\Services\SessionStorageService;
use Plenty\Plugin\ConfigRepository;
use Findologic\Constants\Plugin;

/**
 * Class PluginConfig
 * @package Findologic\Components
 */
class PluginConfig
{
    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var SessionStorageService
     */
    private $sessionStorageService;

    /**
     * @var array
     */
    private $shopkeys = [];

    public function __construct(ConfigRepository $configRepository, SessionStorageService $sessionStorageService)
    {
        $this->configRepository = $configRepository;
        $this->sessionStorageService = $sessionStorageService;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key)
    {
        return $this->configRepository->has($key);
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->configRepository->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed|null $value
     * @return mixed
     */
    public function set(string $key, $value = null)
    {
        return $this->configRepository->set($key, $value);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function prepend(string $key, $value)
    {
        return $this->configRepository->prepend($key, $value);
    }

    /**
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function push(string $key, $value)
    {
        return $this->configRepository->push($key, $value);
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->configRepository->getPrefix();
    }

    /**
     * @return string|null
     */
    public function getShopKey()
    {
        if (empty($this->shopkeys)) {
            $this->parseShopkeys();

            if (empty($this->shopkeys)) {
                return null;
            }
        }

        $currentLanguage = $this->getCurrentSessionLanguageCode();
        if (!$currentLanguage) {
            $currentLanguage = 'de';
        }

        if (array_key_exists($currentLanguage, $this->shopkeys)) {
            return $this->shopkeys[$currentLanguage];
        } else {
            return null;
        }
    }

    private function parseShopKeys()
    {
        $configShopKeys = $this->configRepository->get(
            Plugin::CONFIG_SHOPKEY,
            ''
        );

        if (!$configShopKeys) {
            return;
        }

        $configShopKeysArray = array_filter(explode(PHP_EOL, $configShopKeys));

        foreach ($configShopKeysArray as $item) {
            $item = array_map('trim', explode(':', $item));

            if (count($item) > 1) {
                $this->shopkeys[strtolower($item[0])] = $item[1];
            } else {
                array_push($this->shopkeys, $item[0]);
            }
        }
    }

    /**
     * @return array
     */
    public function getShopkeys()
    {
        return $this->shopkeys;
    }

    /**
     * @return string|null
     */
    protected function getCurrentSessionLanguageCode()
    {
        return $this->sessionStorageService->getLang();
    }
}
