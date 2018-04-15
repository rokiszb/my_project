<?php

namespace AppBundle\Entity;

class Subscriber
{
	private $subscriber = [];
    private $name;
    private $email;
    private $registration_time;
    private $active = true;
    private $subscriptions = [];

    function __construct($formData) {
		$this->name = $formData['name'];
		$this->email = $formData['email'];
		$this->registration_time = date("Y-m-d H:i:s");

		foreach ($formData as $name => $value) {
			if (is_bool($value)) {
				$this->subscriptions[$name] = $value;
			}
		}
	}

    public function getSubscriberToJson()
    {
		$subscriber['name'] = $this->name;
		$subscriber['email'] = $this->email;
		$subscriber['registration_time'] = $this->registration_time;
		$subscriber['subscriptions'] = $this->subscriptions;
		$subscriber['active'] = $this->active;

		return $subscriber;
    }

}