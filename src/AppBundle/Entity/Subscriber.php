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
	private $subscribersDir = __DIR__.'/../../../app/Resources/data/subscribers.json';
	private $subscribtionsDir = __DIR__.'/../../../app/Resources/data/subscriptions.json';

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
		$this->subscriber['name'] = $this->name;
		$this->subscriber['email'] = $this->email;
		$this->subscriber['registration_time'] = $this->registration_time;
		$this->subscriber['subscriptions'] = $this->subscriptions;
		$this->subscriber['active'] = $this->active;

		$subscriberJSON =  $this->subscriber;

		return $subscriberJSON;
	}

	public function saveToFile()
	{
        $subscribersDir = __DIR__.'/../../../app/Resources/data/subscribers.json';
		$fileContents = file_get_contents($subscribersDir);
		$decodeJson = json_decode($fileContents, true);
		$decodeJson[] = $this->getSubscriberToJson();
		$jsonData = json_encode($decodeJson, JSON_PRETTY_PRINT);

		if (file_put_contents($subscribersDir, $jsonData )) {
			return true;
		}

		return false;
	}

	public static function getSubscribers()
	{
        // $subscriptionsDirectory = $this->getParameter('subscriptions_directory');
        $subscriptionsContents = file_get_contents($this->subscriptionsDirectory);
        $subscriptions = json_decode($subscriptionsContents, true);
        return $subscriptions['subscriptions'];
	}

}