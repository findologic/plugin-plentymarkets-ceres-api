<?php

namespace Findologic\Api\Services;

use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\Log\LoggerFactory;
use Findologic\Constants\Plugin;

/**
 * Class ColorImage
 * @package Findologic\Api
 */
class Image
{
    /**
     * @var LibraryCallContract
     */
    protected $libraryCallContract;

    /**
     * @var LoggerContract
     */
    protected $logger;

    /**
     * ColorImageClient constructor.
     * @param LibraryCallContract $libraryCallContract
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(LibraryCallContract $libraryCallContract, LoggerFactory $loggerFactory)
    {
        $this->libraryCallContract = $libraryCallContract;
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
    }

    /**
     * @param string $imageUrl
     * @return bool
     */
    public function isImageAccessible(string $imageUrl)
    {
        try {
            $status = (string)$this->libraryCallContract->call(
                'Findologic::http_library_image',
                ['imageUrl' => $imageUrl]
            );
        } catch (\Exception $e) {
            $this->logger->error('Exception while accessing image.');
            $this->logger->logException($e);

            return false;
        }

        return $this->isStatusCodeSuccess($status);
    }

    /**
     * @param string $statusCode
     * @return bool
     */
    private function isStatusCodeSuccess($statusCode)
    {
        return !($statusCode[0] === '4' || $statusCode[0] === '5');
    }
}