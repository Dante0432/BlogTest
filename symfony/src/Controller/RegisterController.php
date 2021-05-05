<?php

namespace App\Controller;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Length;


class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request,UserPasswordEncoderInterface $passEncoder): Response
    {
        $form = $this->createFormBuilder()
                ->add('name')
                ->add('lastname')
                ->add('email',EmailType::class)
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::Class,
                    'required' => true,
                    'first_options' => ['label' => 'Password'],
                    'second_options' => ['label' => 'Confirm Password'],
                    'constraints' => new Length(['min' => 6])
                ])
                ->add('register', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-primary float-rigth mt-3'
                    ]
                ])
                ->getForm();
        
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
               

                $user = new User();
                $user->setName($data['name']);
                $user->setLastName($data['lastname']);
                $user->setEmail($data['email']);
                $user->setPassword(
                    $passEncoder->encodePassword($user, $data['password'])
                );
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

            }

        return $this->render('register/index.html.twig', [
            'register' => $form->createView()
        ]);
    }
}
