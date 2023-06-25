<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(ManagerRegistry $doctrine)
    {
        $articles = $doctrine->getRepository(Article::class)->findAll();

        return $this->render('home/home.html.twig',[
            "articles" => $articles,
        ]);
    }
}
