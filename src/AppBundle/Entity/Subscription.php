<?php

namespace AppBundle\Entity;

class Subscription
{
    protected $subscription;
    protected $name = [];

    public function getSubscription()
    {
        return $this->subscription;
    }

    public function setSubscription($subscription)
    {
        $this->subscription = $subscription;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name[] = $name;
    }
}