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

class DefaultController extends Controller
{
    /**
     * @Route("/", name="form")
     */
    public function indexAction(Request $request)
    {

        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('checkbox', CheckboxType::class, array(
                'label'    => 'Label1',
            ))
            ->add('save', SubmitType::class, array('label' => 'Submit'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $data = $form->getData();
            $data['registration_time'] = date("Y-m-d H:i:s");
            $data['unix_timestamp'] = time();
            // var_dump($data);die();

            $subscribersDir = $this->getParameter('subscribers_directory');
            $jsonData = json_encode(array('0' => $data));

            $fileContents = file_get_contents($subscribersDir);
            $decodeJson = json_decode($fileContents, true);
            $decodeJson[] = $data;
            $jsonData = json_encode($decodeJson, JSON_PRETTY_PRINT);

            file_put_contents($subscribersDir, $jsonData );

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $em = $this->getDoctrine()->getManager();
            // $em->persist($task);
            // $em->flush();

            return $this->redirectToRoute('form');
        }


        return $this->render('default/form.html.twig', array(
            'form' => $form->createView(),
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
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/{note}/notes", name="admin")
     * @Method("GET")
     */
    public function getInfoAction()
    {
        $notes = [
            ['id' => 1, 'username' => 'Jesus', 'title' => 'Clean your room'],
            ['id' => 1, 'username' => 'Jesus', 'title' => 'Clean your room'],
        ];

        $data = [
            'notes' => $notes,
        ];

        return new JsonResponse($data);
    }
}
