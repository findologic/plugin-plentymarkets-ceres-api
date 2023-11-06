<?php

declare(strict_types=1);

namespace Findologic\Api\Response\Json10\Filter\Values;

use Findologic\Api\Response\Json10\Filter\Media;

abstract class ColorImageFilterValue extends FilterValue
{
    public string $displayType;

    public ?Media $media;

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        return $this;
    }

    public function getDisplayType(): string
    {
        return $this->displayType;
    }

    public function setDisplayType(string $displayType): self
    {
        $this->displayType = $displayType;

        return $this;
    }
}
