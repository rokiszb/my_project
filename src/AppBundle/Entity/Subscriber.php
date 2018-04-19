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
	private static $subscribersDirectory = __DIR__.'/../../../app/Resources/data/subscribers.json';
	private static $subscriptionsDirectory = __DIR__.'/../../../app/Resources/data/subscriptions.json';

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

    public function getUpdateSubscriberToJson()
    {
		$this->subscriber['name'] = $this->name;
		$this->subscriber['email'] = $this->email;
		$this->subscriber['subscriptions'] = $this->subscriptions;

		$subscriberJSON =  $this->subscriber;

		return $subscriberJSON;
	}

	public function update($subscriberId)
	{
		$fileContents = file_get_contents(self::$subscribersDirectory);;
		$subscribersList = json_decode($fileContents, true);

		$subscriber = $this->getUpdateSubscriberToJson();

		$subscribersList[$subscriberId]['name'] = $subscriber['name'];
		$subscribersList[$subscriberId]['email'] = $subscriber['email'];
		$subscribersList[$subscriberId]['subscriptions'] = $subscriber['subscriptions'];

		$jsonData = json_encode($subscribersList, JSON_PRETTY_PRINT);

		if ( file_put_contents(self::$subscribersDirectory, $jsonData ) ) {
			return true;
		}
		return false;
	}

	public function saveToFile()
	{
		$fileContents = file_get_contents(self::$subscribersDirectory);
		$decodeJson = json_decode($fileContents, true);
		$decodeJson[] = $this->getSubscriberToJson();
		$jsonData = json_encode($decodeJson, JSON_PRETTY_PRINT);

		if (file_put_contents(self::$subscribersDirectory, $jsonData )) {
			return true;
		}

		return false;
	}

	public static function getSubscribers()
	{
        // $subscriptionsDirectory = $this->getParameter('subscriptions_directory');
        $subscriptionsContents = file_get_contents(self::$subscriptionsDirectory);
        $subscriptions = json_decode($subscriptionsContents, true);
        return $subscriptions['subscriptions'];
	}

	public static function delete($subscriberId)
	{
        $fileContents = file_get_contents(self::$subscribersDirectory);
        $decodeJson = json_decode($fileContents, true);
        $decodeJson[$subscriberId]['active'] = false;
		$jsonData = json_encode($decodeJson, JSON_PRETTY_PRINT);

		if (file_put_contents(self::$subscribersDirectory, $jsonData)) {
			return  [ 'isDeleted' => true, 'email' => $decodeJson[$subscriberId]['email']];
		}

		return [ 'isDeleted' => false, 'email' => $decodeJson[$subscriberId]['email']];
	}

}