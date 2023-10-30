<?php

namespace Findologic\Api\Response\Request;

class Order
{
    const API_NAMESPACE = 'FINDOLOGIC\Api\Responses\Json10\Properties\Order';

    private string $field;

    private bool $relevanceBased;

    private string $direction;

    public function __construct(array $order)
    {
        $this->field = $order[self::API_NAMESPACE . 'field'];
        $this->relevanceBased = $order[self::API_NAMESPACE . 'relevanceBased'];
        $this->direction = $order[self::API_NAMESPACE . 'direction'];
    }

    /**
     * May return "salesfrequency dynamic DESC" or similar.
     *
     * @return string
     */
    public function __toString()
    {
        $dynamic = $this->relevanceBased ? 'dynamic ' : '';

        return sprintf('%s %s%s', $this->field, $dynamic, $this->direction);
    }

    /**
     * Get the value of field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Get the value of relevanceBased
     */
    public function getRelevanceBased()
    {
        return $this->relevanceBased;
    }

    /**
     * Get the value of direction
     */
    public function getDirection()
    {
        return $this->direction;
    }
}
