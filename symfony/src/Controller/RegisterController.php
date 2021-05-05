<?php

namespace App\Controller;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormError;


class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request,UserPasswordEncoderInterface $passEncoder, UserRepository $UserRepository): Response
    {
        $form = $this->createFormBuilder()
                ->add('name', TextType::class ,['label' => 'Nombre'])
                ->add('lastname', TextType::class,['label' => 'Apellido'])
                ->add('email',EmailType::class, ['label' => 'Correo electrónico'] )
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::Class,
                    'required' => true,
                    'first_options' => ['label' => 'Contraseña'],
                    'second_options' => ['label' => 'Confirmar Contraseña'],
                    'invalid_message' => 'Passwords do not match, please try again!!!',
                    'constraints' => new Length(['min' => 6])
                ])
                ->add('Completar', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-primary float-rigth mt-3'
                    ]
                ])
                ->getForm();
        
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();

                $userExist = $UserRepository->findOneBy(['email'=>$data['email']]);

                if($userExist) { 
                    $form->get('email')->addError(new FormError('email already exist!!'));
                    return $this->render('register/index.html.twig', [
                        'register' => $form->createView()
                    ]);
                }

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

                return $this->redirectToRoute('app_login');
            }

        return $this->render('register/index.html.twig', [
            'register' => $form->createView()
        ]);
    }
}
