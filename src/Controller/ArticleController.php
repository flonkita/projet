<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\ArticleType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/article/add", name="app_base")
     */
    // public function index(ManagerRegistry $doctrine)
    // {
    //     #Etape 1 : On appel l'entity manager de doctrine
    //     $entityManager = $doctrine->getManager();

    //     #Etape 2 : On crée l'objet et on l'alimente
    //     $article = new Article;
    //     $article-> setNom("Headband Kente");
    //     $article-> setDescription("Il s'agit d'un accessoire de mode, spécifiquement un bandeau de tête, conçu avec un tissu traditionnel africain appelé Kente. Le Kente est un tissu fait à la main originaire du Ghana, qui est généralement tissé en utilisant des motifs et des couleurs vives et audacieuses.

    //     Le bandeau de tête est conçu de manière modulable, ce qui signifie qu'il peut être ajusté pour s'adapter à différentes tailles de tête. Cela le rend très polyvalent et facile à porter pour différentes occasions, que ce soit pour un look décontracté ou plus habillé.
        
    //     En plus de sa fonctionnalité, le bandeau de tête Kente est également un bel exemple de mode éthique, car il est fabriqué à la main par des artisans au Ghana. L'achat de cet accessoire de mode soutient donc non seulement la mode éthique et durable, mais également l'artisanat traditionnel africain.");
    //     // $article->setImageNom("Headband_Kente.jpeg");
    //     $article-> setPrix(24.99);

    //     #Etape 3 : on indique a doctrine que l'on souhaite préparer l'enregistrement d'un nouvel élément
    //     $entityManager->persist($article);

    //     #etape 4: on valide a doctrine que l'on veut enregisterer/persister en BDD
    //      $entityManager->flush();
    //     #etape 5: on affiche ou on redirge vers une autre page 
    //     //return new Response("<h1>Bravo le livre a été ajouté !");
    //     return $this -> redirectToRoute('home');
    // }
        /**
         * @Route ("/article/edit/{id}", name ="article_edit")
         */
        
        # COMMENT MODIFIER UNE DESCRIPTION DANS UNE BASE DE DONNEES (EXEMPLE DE LIVRE)
        public function edit($id, Request $request, ManagerRegistry $doctrine) 
        {
            #Etape 1 : On appelle l'entity manager de doctrine
            $entityManager = $doctrine->getManager();

            #Etape 2 : On récupère (grâce au repository de doctrine) l'objet que l'on souhaite modifier
            $article = $doctrine->getRepository(Article::class)->find($id);

            #Etape 3 : On modifie les valeurs de l'objet que l'on souhaite modifier
            $formArticle = $this->createForm(ArticleType::class, $article);

        $formArticle->handleRequest($request);
            // $Article->setTitre("Corneille et bernie");
            // $Article->setDescription("Voici l'histoire de Corneille et Bernieeee...");

            #Etape 4 : On valide les modifications
            $entityManager->flush();

            if($formArticle->isSubmitted() && $formArticle->isValid())
            #Afficher un flash avec un petit message
            {
                $this->addFlash('edit','Petite retouche des pages de '.$article->getNom().' !');
                 return $this->redirectToRoute('article_list');
            }
            #Etape 5 : On affiche ou on redirige vers une autre page 
            return $this->render('form-edit.html.twig', [
                'formArticle'=> $formArticle->createView()
            ]);
        }


        #COMMENT EFFACER UNE 
        /**
         * @Route ("/article/delete/{id}", name ="article_delete")
        */
        public function delete($id, ManagerRegistry $doctrine) 
        {
            #Etape 1 : On appelle l'entity manager de doctrine
            $entityManager = $doctrine->getManager();

            #Etape 2 : On récupère (grâce au repository de doctrine) l'objet que l'on souhaite modifier
            $article = $doctrine->getRepository(article::class)->find($id);

            #Etape 3 : On supprime à l'aide de l'entity manager 
            $entityManager->remove($article);

            #Etape 4 : On valide les modifications
            $entityManager->flush();

            $this->addFlash('delete','Un article a été victime de son succès...');
            #Etape 5 : On affiche ou on redirige vers une autre page 
            
            return $this->redirectToRoute('article_list');
        }

    ##READ:ALL
    /**
     * @Route("/article/list", name="article_list")
     */
    public function readAll(ManagerRegistry $doctrine)
    {
        #Etape 1 : Récupérer tout les livres
        $articles = $doctrine->getRepository(Article::class)->findAll();

        return $this->render('list.html.twig',[
        "articles" => $articles]);

    }


    /**
     * @Route("/article/{id}", name="article_detail", requirements={"id":"\d+"})
     */
    public function detail($id, ManagerRegistry $doctrine)
    {
        #Etape 1 : Récupérer un livre 
        $article = $doctrine->getRepository(Article::class)->find($id);

        return $this->render('article.html.twig',[
        "article" => $article]);
    }
    /**
     * @Route("/article/new", name="article_new")
     */
    public function new(Request $request, ManagerRegistry $doctrine)
    {
        
        #Etape 1 : Créer un objet vide
        $article = new Article;

        #Etape 2 : Créer le formulaire
        $formArticle = $this->createForm(ArticleType::class, $article);

        $formArticle->handleRequest($request);
        if($formArticle->isSubmitted() && $formArticle->isValid())
        {
            #Etape 1 : On appel l'entity manager de doctrine
            $entityManager = $doctrine->getManager();

            #Etape 3 : on indique a doctrine que l'on souhaite préparer l'enregistrement d'un nouvel élément
            $entityManager->persist($article);

            #etape 4: on valide a doctrine que l'on veut enregisterer/persister en BDD
            $entityManager->flush();
            #etape 5: on affiche ou on redirge vers une autre page 
            $this->addFlash('create','Un article a fait son apparition !');
        return $this -> redirectToRoute('article_list');

        }

        #Etape 3 : On envoie le formulaire dans la vue
        return $this->render('form-new.html.twig', [
            'formArticle'=> $formArticle->createView()
        ]);
    }

    ##READ:ALL
    /**
     * @Route("/user/list", name="user_list")
     */
    public function readUserAll(ManagerRegistry $doctrine)
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN'); #N'autorise pas l'accès à la suite de la fonction si on n'as pas le rôle User

        #Etape 1 : Récupérer tout les livres
        $users = $doctrine->getRepository(User::class)->findAll();

        return $this->render('users.html.twig',[
        "users" => $users]);

    }
}
