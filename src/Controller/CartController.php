<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="app_cart")
     */
    public function index(SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        #On récupere la session 'panier' si elle existe - sinon elle est créée avec un tableau vide
        $panier = $session->get('panier', []);

        #Variable tableau
        $panierData = [];
        
        #On boucle sur la session 'panier' pour récuperer proprement l'objet (au lieu de l'id) et la quantité
        foreach($panier as $id => $quantity)
        {
            $panierData[] = [
                "product" => $doctrine->getRepository(Article::class)->find($id),
                "quantity" => $quantity
            ];
        }
        //dd($panierData);

        #On calcule le total du panier ici, afin de ne pas a avoir a le faire dans la vue Twig
        $total = 0;
        foreach($panierData as $id => $value)
        {
            $total += $value['product']->getPrix() * $value['quantity'];
        }

        #On calcule le totale des quantités
        $totalQuantity = 0;
        foreach($panierData as $id => $value)
        {
            $totalQuantity += $value['quantity'];
        }
    
        return $this->render('cart/index.html.twig', [
            "items" => $panierData,
            "total" => $total,
            "totalQuantity" => $totalQuantity
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add")
     */
    public function cartAdd($id, SessionInterface $session)
    {
        #ETAPE 1 : On récupere la session 'panier' si elle existe - sinon elle est créée avec un tableau vide
        $panier = $session->get('panier', []);

        #ETAPE 2 : On ajoute la quantité 1, au produit d'id $id
        if(!empty($panier[$id]))
        {
            $panier[$id]++;
        }
        else
        {
            $panier[$id] = 1;
        }

        #ETAPE 3 : On remplace la variable de session panier par le nouveau tableau $panier
        $session->set('panier', $panier);

        $this->addFlash('add_cart', "Le produit a bien été ajouté a votre panier");

        //dd($session->get('panier', []));
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/panier/delete/{id}", name="cart_delete")
     */
    public function delete($id, SessionInterface $session)
    {
        #On récupere la session 'panier' si elle existe - sinon elle est créée avec un tableau vide
        $panier = $session->get('panier', []);
        
        #On supprime de la session celui dont on a passé l'id
        if(!empty($panier[$id]))
        {
            $panier[$id]--;

            if($panier[$id] <= 0)
            {
                unset($panier[$id]); //unset pour dépiler de la session
            }
        }

        #On réaffecte le nouveau panier à la session
        $session->set('panier', $panier);

        #On redirige vers le panier
        return $this->redirectToRoute('app_cart');
    }

    /**
     * @Route("/panier/clear", name="cart_clear")
     */
    public function clearCart(SessionInterface $session)
    {
        #On vide le panier
        $session->remove('panier');

        #On redirige vers le panier
        return $this->redirectToRoute('app_cart');
    }   

}

