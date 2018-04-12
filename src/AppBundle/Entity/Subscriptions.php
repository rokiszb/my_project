<?php

namespace AppBundle\Entity;

class Subscriptions
{
    private $subscriptionsDirectory;
    private $subscriptionsContents;
    private $subscriptions;

    function __construct() {
        $this->subscriptionsDirectory = __DIR__.'/../../../app/Resources/data/subscriptions.json';
        $this->subscriptionsContents = file_get_contents($this->subscriptionsDirectory);
        $this->subscriptions = json_decode($this->subscriptionsContents, true);
	}

    public function getSubscriptions()
    {
        return $this->subscriptions['subscriptions'];
    }

}