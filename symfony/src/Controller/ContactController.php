<?php

namespace App\Controller;
use App\Entity\Contact;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function index(Request $request): Response
    {
        $form = $this->createFormBuilder()
        ->add('name',null,['label' => 'Nombre'])
        ->add('email', EmailType::class,['label' => 'Correo'])
        ->add('message', TextareaType::class,['label' => 'Mensaje'])
        ->add('Enviar', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary float-rigth mt-3'
            ]
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();

        $contact = new Contact();
        $contact->setName($data['name']);
        $contact->setCreationDate(new \DateTime());
        $contact->setEmail($data['email']);
        $contact->setMessage($data['message']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($contact);
        $entityManager->flush();

        $this->getRedirectLater($this->generateUrl('index'));
        return $this->render('contact/index.html.twig', [
            'emailSend' => true,
            'form' => $form->createView(),
        ]);
    }
    
        return $this->render('contact/index.html.twig', [
            'emailSend' => false,
            'form' => $form->createView(),
        ]);


    }
    private function getRedirectLater($url, $seconds=5)
{
    $response = new Response;
    $response->headers->set('Refresh', $seconds.'; url='. $url);

    return $response;
}
}
