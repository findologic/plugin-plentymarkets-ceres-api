<?php

declare(strict_types=1);

namespace Findologic\Api\Response\Json10\Filter;

class RatingFilter extends Filter
{
    public float $maxPoints = 0;

    public function setMaxPoints(float $maxPoints): RatingFilter
    {
        $this->maxPoints = $maxPoints;

        return $this;
    }

    public function getMaxPoints(): float
    {
        return $this->maxPoints;
    }
}
