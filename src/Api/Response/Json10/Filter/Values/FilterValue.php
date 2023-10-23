<?php

declare(strict_types=1);

namespace Findologic\Api\Response\Json10\Filter\Values;

use Findologic\Api\Response\Json10\Filter\TranslatedName;

class FilterValue
{
    public const DELIMITER = '>';

    protected ?string $uuid;

    private TranslatedName $translated;

    /**
     * @param string|null $filterName
     * This can be null because we do not want to set this for all filter values.
     * For e.g the category filter does not need to have a unique ID as its value is already unique.
     * The uuid is generated only for the values in which we need a unique ID for selection in storefront
     */
    public function __construct(
        private string $id,
        private string $name,
        ?string $filterName = null
    ) {
        $this->translated = new TranslatedName($name);
        if ($filterName !== null) {
            $this->uuid = sprintf('%s%s%s', $filterName, self::DELIMITER, $id);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTranslated(): TranslatedName
    {
        return $this->translated;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }
}
