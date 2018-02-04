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
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\FileLocator;
use AppBundle\Entity\Subscription;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="form")
     */
    public function indexAction(Request $request)
    {

        $subscriptionsDirectory = $this->getParameter('subscriptions_directory');
        $subscriptionsContents = file_get_contents($subscriptionsDirectory);
        $subscriptions = json_decode($subscriptionsContents, true);
        $subscription = new Subscription();
        // foreach ($subscriptions['subscriptions'] as $key => $value) {
        //     $subscription->setName($value);
        // }

        $form = $this->createFormBuilder();
        $form->add('name', TextType::class)
            ->add('email', EmailType::class)
            // ->add('checkbox', CheckboxType::class)
            ;

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
            'attr'=> array('class'=>'btn btn-primary')
        ))->getForm();
        // var_dump(($form)); die();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            $formData = $form->getData();
            $data['name'] = $formData['name'];
            $data['email'] = $formData['email'];
            $data['registration_time'] = date("Y-m-d H:i:s");
            $data['unix_timestamp'] = time();
            foreach ($formData as $name => $value) {
                if (is_bool($value)) {
                    $data['subscriptions'][$name] = $value;
                }
            }

            $subscribersDir = $this->getParameter('subscribers_directory');
            $jsonData = json_encode(array('0' => $data));

            $fileContents = file_get_contents($subscribersDir);
            $decodeJson = json_decode($fileContents, true);
            $decodeJson[] = $data;
            $jsonData = json_encode($decodeJson, JSON_PRETTY_PRINT);

            file_put_contents($subscribersDir, $jsonData );

            return $this->redirectToRoute('form');
        }

        return $this->render('default/form.html.twig', array(
            'form' => $form->createView()
            // 'subscriptions' => $subscription
        ));
    }

    /**
     * @Route("/form/subscribe", name="form_subscribe")
     * @Method("POST")
     */
    public function postFormAction(Request $request)
    {
        return new Response($request);
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

        // var_dump($decodeJson);die();


        return $this->render('default/admin.html.twig', [
            'userData' => $decodeJson,
        ]);
    }
}
