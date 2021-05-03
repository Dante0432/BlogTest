<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository): Response
    {
        $post=$postRepository->findOne();
        return $this->show($post, $postRepository);
    }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $currentUser=$this->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }
        
        $form = $this->createFormBuilder()
            ->add('title',null,['label' => 'Titulo'])
            ->add('url_image', UrlType::class,['label' => 'Url imagen'])
            ->add('content', TextareaType::class,['label' => 'Contenido'])
            ->add('Crear', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary float-rigth mt-1'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $post = new Post();
            $post->setAuthor($currentUser);
            $post->setCreationDate(new \DateTime());
            $post->setTitle($data['title']);
            $post->setUrlImg($data['url_image']);
            $post->setContent($data['content']);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('post_index');
        }
        
        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="post_show", methods={"GET"})
     */
    public function show(Post $post=null, PostRepository $postRepository): Response
    {
        return $this->render('post/show.html.twig', [
            'posts' => $postRepository->findAll(),
            'post' => $post,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Post $post): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createFormBuilder()
        ->add('title',null,['label' => 'Titulo', 'data' => "{$post->getTitle()}"])
        ->add('url_image', UrlType::class,['label' => 'Url Imagen', 'data' => "{$post->getUrlImg()}" ],)
        ->add('content', TextareaType::class,['label' => 'Contenido', 'data' => "{$post->getContent()}"])
        ->add('Actualizar', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary float-rigth mt-1'
            ]
        ])
        ->getForm();

        $form->handleRequest($request);
                
        if ($form->isSubmitted() && $form->isValid()) {
            
            $data = $form->getData();
           
            
            $entityManager = $this->getDoctrine()->getManager();
            $post = $entityManager->getRepository(Post::class)->find($post->getId());

            $post->setTitle($data['title']);
            $post->setUrlImg($data['url_image']);
            $post->setContent($data['content']);
            $entityManager->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="post_delete", methods={"POST"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_index');
    }
}
