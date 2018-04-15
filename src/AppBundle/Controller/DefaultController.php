<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\FileLocator;
use AppBundle\Entity\Subscriptions;
use AppBundle\Entity\Subscriber;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="form")
     */
    public function indexAction(Request $request)
    {
		$subscriptions = new Subscriptions();

        $form = $this->createFormBuilder();
        $form->add('name', TextType::class)
			 ->add('email', EmailType::class);

        foreach ($subscriptions->getSubscriptions() as $key => $value) {
            $form->add($value, CheckboxType::class, array(
                'label'    => $value,
                'required' => false,
                'attr'=> array('class'=>'subscriptions')
            ));
        }

        $form = $form
			->add('save', SubmitType::class, array(
				'label' => 'Submit',
				'attr'=> array('class'=>'btn btn-primary')
			))->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
			$subscriber = new Subscriber($form->getData());

            $subscribersDir = $this->getParameter('subscribers_directory');
            // $jsonData = json_encode(array('0' => $subscriber->getSubscriberToJson()));
			$jsonData = $subscriber->getSubscriberToJson();
			// var_dump($jsonData); die;
            $fileContents = file_get_contents($subscribersDir);
            $decodeJson = json_decode($fileContents, true);
            $decodeJson[] = $subscriber->getSubscriberToJson();
            $jsonData = json_encode($decodeJson, JSON_PRETTY_PRINT);

            if (file_put_contents($subscribersDir, $jsonData )) {
				$this->addFlash(
					'notice',
					'Subscriber created succesfully'
				);
			} else {
				$this->addFlash(
					'notice',
					'Subscriber wasn\'t created'
				);
			}

            return $this->redirectToRoute('form');
        }

        return $this->render('default/form.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/edit/{subscriber}", name="editSubscriber")
     */
    public function editSubscriberAction($subscriber, Request $request)
    {
        $form = $this->createFormBuilder();
        $form->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('user_id', TextType::class, array(
                'attr'=> array('hidden'=>'hidden')))
            ;

        $subscriptionsDirectory = $this->getParameter('subscriptions_directory');
        $subscriptionsContents = file_get_contents($subscriptionsDirectory);
        $subscriptions = json_decode($subscriptionsContents, true);
        foreach ($subscriptions['subscriptions'] as $key => $value) {
            $form->add($value, CheckboxType::class, array(
                'label'    => $value,
                'required' => false,
                'attr'=> array('class'=>'subscriptions')
            ));
        }

        $form = $form
        ->add('save', SubmitType::class, array(
            'label' => 'Submit',
            'attr'=> array('class'=>'btn btn-primary')))
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $data['name'] = $formData['name'];
            $data['email'] = $formData['email'];
            foreach ($formData as $name => $value) {
                if (is_bool($value)) {
                    $data['subscriptions'][$name] = $value;
                }
            }

            $subscribersDir = $this->getParameter('subscribers_directory');
            $jsonData = json_encode(array('0' => $data));

            $fileContents = file_get_contents($subscribersDir);
            $decodeJson = json_decode($fileContents, true);
            $decodeJson[$formData['user_id']];

            $decodeJson[$formData['user_id']]['name'] = $data['name'];
            $decodeJson[$formData['user_id']]['email'] = $data['email'];
            $decodeJson[$formData['user_id']]['subscriptions'] = $data['subscriptions'];

            $jsonData = json_encode($decodeJson, JSON_PRETTY_PRINT);

            file_put_contents($subscribersDir, $jsonData );

            $this->addFlash(
                'notice',
                'User updated succesfully'
            );

            return $this->redirectToRoute('editSubscriber', array('subscriber' => $formData['user_id'] ));
        }

        $subscribersDir = $this->getParameter('subscribers_directory');
        $fileContents = file_get_contents($subscribersDir);
        $decodeJson = json_decode($fileContents, true);
        return $this->render('default/subscriberEdit.html.twig', [
            'form' => $form->createView(),
            'userData' => $decodeJson[$subscriber],
            'subscriber' => $subscriber,
        ]);
    }

    /**
     * @Route("/delete/{subscriber}", name="deleteSubscriber")
     */
    public function deleteSubscriberAction($subscriber, Request $request)
    {
        $subscribersDir = $this->getParameter('subscribers_directory');
        $fileContents = file_get_contents($subscribersDir);
        $decodeJson = json_decode($fileContents, true);
        $decodeJson[$subscriber]['active'] = false;
        $jsonData = json_encode($decodeJson, JSON_PRETTY_PRINT);
        file_put_contents($subscribersDir, $jsonData);

        $this->addFlash(
            'notice',
            'User with email '.$decodeJson[$subscriber]['email'].' was removed from subscribers list.'
        );

        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/error", name="error")
     */
    public function errorAction()
    {

        $this->addFlash(
            'notice',
            'Your changes were saved!'
        );

        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function adminAction(Request $request)
    {
        $subscriptions = [];
        $subscribersDir = $this->getParameter('subscribers_directory');
        $fileContents = file_get_contents($subscribersDir);
        $decodeJson = json_decode($fileContents, true);

        return $this->render('default/admin.html.twig', [
            'userData' => $decodeJson,
        ]);
    }
}
