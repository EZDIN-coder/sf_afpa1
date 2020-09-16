<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Commentaire;
use App\Form\CommentaireType;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="article_index", methods={"GET"})
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $articles = $entityManager->getRepository(Article::class)->getLastInserted('App:article', 5);

        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="article_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if (!$this->getUser()){
           $this->addFlash("danger","Merci de vous connecter");
          return $this->redirectToRoute('app_login');

        }
       
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $article->setUser($this->getuser());

            $entityManager->persist($article);
            $entityManager->flush();

            if (!$this->getUser()){
                $this->addFlash("danger");
               return $this->redirectToRoute('app_login');


            //return $this->redirectToRoute('article_index');
        }
    }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_show",  methods={"GET","POST"})
     */
    public function show(Article $article, Request $request): Response
    {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            

            $commentaire->setUser($this->getUser());
            $commentaire->setArticle($article);



            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('article_show', ['id'=>$article->getId()]);
        }

        $liste_commentaires = $entityManager ->getRepository('App:Commentaire') ->findByArticle($article);

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'form'   => $form ->createView(),
            'liste_commentaires' => $liste_commentaires,
        ]);
    }
        //return $this->render('article/show.html.twig', [
            //'article' => $article,
        //]);
    

    /**
     * @Route("/{id}/edit", name="article_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Article $article): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_index');
    }
}
