<?php

namespace Findologic\Api\Response\Request;

class Order
{

    private string $field;

    private bool $relevanceBased;

    private string $direction;

    public function __construct(array $order)
    {
        $this->field = $order['field'];
        $this->relevanceBased = $order['relevanceBased'];
        $this->direction = $order['direction'];
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
