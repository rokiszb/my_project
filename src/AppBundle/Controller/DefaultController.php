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
			$save = $subscriber->saveToFile();

            if ($save === true) {
				$this->addFlash('notice', 'Subscriber created succesfully');
			} else {
				$this->addFlash('notice', 'Subscriber wasn\'t created');
			}

            return $this->redirectToRoute('form');
        }

        return $this->render('default/form.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/edit/{subscriberId}", name="editSubscriber")
     */
    public function editSubscriberAction($subscriberId, Request $request)
    {
        $subscriptions = new Subscriptions();

        $form = $this->createFormBuilder();
        $form->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('user_id', TextType::class, array(
                'attr'=> array('hidden'=>'hidden')));

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
				'attr'=> array('class'=>'btn btn-primary')))
			->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();
            $subscriber = new Subscriber($form->getData());
			$update = $subscriber->update($formData['user_id']);

			if ($update === true) {
				$this->addFlash('notice', 'User updated succesfully');
			} else {
				$this->addFlash('notice', 'User update unsuccessful');
			}

            return $this->redirectToRoute('editSubscriber', array('subscriberId' => $formData['user_id'] ));
        }

        $subscribersDir = $this->getParameter('subscribers_directory');
        $fileContents = file_get_contents($subscribersDir);
        $decodeJson = json_decode($fileContents, true);

        return $this->render('default/subscriberEdit.html.twig', [
            'form' => $form->createView(),
            'userData' => $decodeJson[$subscriberId],
            'subscriber' => $subscriberId,
        ]);
    }

    /**
     * @Route("/delete/{subscriberId}", name="deleteSubscriber")
     */
    public function deleteSubscriberAction($subscriberId, Request $request)
    {
		$subscriber = Subscriber::delete($subscriberId);

		if ($subscriber['isDeleted']) {
			$this->addFlash('notice', 'User with email '.$subscriber['email'].' was removed from subscribers list.');
		} else {
			$this->addFlash('notice', 'User with email '.$subscriber['email'].' was not removed from subscribers list.');
		}

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
