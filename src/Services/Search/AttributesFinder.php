<?php

namespace Findologic\Services\Search;

use Findologic\Constants\Plugin;
use Plenty\Modules\Property\Contracts\PropertyRepositoryContract;
use Plenty\Modules\Property\Contracts\PropertyOptionRepositoryContract;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\Log\LoggerFactory;

/**
 * Class AttributesFinder
 * @package Findologic\Services\Search
 */
class AttributesFinder
{
    /**
     * @var PropertyRepositoryContract
     */
    protected $propertyRepository;

    /**
     * @var PropertyOptionRepositoryContract
     */
    protected $propertyOptionRepository;

    /**
     * @var LoggerContract
     */
    protected $logger;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $options = [];

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
    }

    /**
     * @param string $attributeName
     * @return int
     */
    public function getAttributeId($attributeName)
    {
        if (in_array($attributeName, $this->attributes)) {
            return $this->attributes[$attributeName];
        }

        $this->logger->error('Search for attribute');

        $result = $this->getPropertyRepository()
            ->clearFilters()
            ->setFilters([
                'name' => $attributeName
            ])
            ->applyCriteriaFromFilters();

        foreach($result->getResult() as $attribute) {
            $this->logger->error($attribute);
        }

        return 1;
    }

    /**
     * @param int $attributeId
     * @param string $optionName
     * @return int
     */
    public function getAttributeOptionId($attributeId, $optionName)
    {
        if (isset($this->options[$attributeId][$optionName])) {
            return $this->options[$attributeId][$optionName];
        }

        return 1;
    }

    /**
     * @return PropertyRepositoryContract|null
     */
    public function getPropertyRepository()
    {
        if (!$this->propertyRepository) {
            $this->propertyRepository = pluginApp(PropertyRepositoryContract::class);
        }

        return $this->propertyRepository;
    }

    /**
     * @return PropertyOptionRepositoryContract|null
     */
    public function getPropertyOptionRepository()
    {
        if (!$this->propertyOptionRepository) {
            $this->propertyOptionRepository = pluginApp(PropertyOptionRepositoryContract::class);
        }

        return $this->propertyOptionRepository;
    }
}