<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Commentaire;
use App\Form\CommentaireType;

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
        $entityManager = $this->getDoctrine()->getManager();

        $posts = $entityManager->getRepository(Post::class)->getLastInserted('App:Post', 5);

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$this->getUser()){
            
            $this->addFlash("danger", "Merci de vous connecter");

            return $this->redirectToRoute('app_login');
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $post->setUser($this->getuser());

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash("success", "Youpi!!! ");

            return $this->redirectToRoute('post_index');

        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="post_show", methods={"GET","POST"})
     */
    public function show(Post $post, Request $request): Response
    {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {

            $commentaire->setUser($this->getUser());

            $commentaire->setPost($post);

            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('post_show', ['id' =>$post->getId()]);
        }

        $liste_commentaires = $entityManager->getRepository('App:Commentaire')->findByPost($post);


        return $this->render('post/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'liste_commentaires' => $liste_commentaires,
        ]);
        /*return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);*/
    }

    /**
     * @Route("/{id}/edit", name="post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="post_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_index');
    }
}
