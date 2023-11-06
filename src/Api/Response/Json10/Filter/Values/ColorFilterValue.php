<?php

declare(strict_types=1);

namespace Findologic\Api\Response\Json10\Filter\Values;

class ColorFilterValue extends ColorImageFilterValue
{
    public string $displayType = 'color';

    public ?string $colorHexCode;

    public function getColorHexCode(): ?string
    {
        return $this->colorHexCode;
    }

    public function setColorHexCode(?string $colorHexCode): self
    {
        $this->colorHexCode = $colorHexCode;

        return $this;
    }
}
