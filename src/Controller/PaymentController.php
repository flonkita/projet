<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
    /**
     * @Route("/payment", name="app_payment")
     */
    public function index(): Response
    {
        return $this->render('payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }

    /**
     * @Route("/checkout", name="payment_checkout")
     */
    public function checkout( SessionInterface $session, ManagerRegistry $doctrine)
    {
        #On met en paramètre la clé API (défini dans le .env et configuré dans le ficher services.yaml)
        Stripe::setApiKey('sk_test_51NBHawC1dWGKvd0WtIgxR8Ub0t4q3HzLEtLipBaqFBDoVCgn73Jyr65z6wsCQ4a2t8f8UuojDvRrRUCRmiI7rw4X00s5GOUdZ6');

        #On récupere la session 'panier' si elle existe - sinon elle est créée avec un tableau vide
        $panier = $session->get('panier', []);

        #Ici on formate un tableau appelé panierData pour que les données soient plus facilement lisibles
        $panierData = [];
        foreach($panier as $id => $quantity)
        {
            #On enrichi le tableau avec l'objet (qui contient toutes les informations du produit) + la quantité
            $panierData[] = [
                "product" => $doctrine->getRepository(Article::class)->find($id),
                "quantity" => $quantity
            ];
        }

        #On construit le line_items pour envoyer ce format a Stripe, afin qu'il puisse afficher correctement dans le module de paiement Stripe.
        foreach($panierData as $id => $value)
        {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $value['product']->getNom(),
                        'images' => $value['product']->getImageName(), // Lien ABSOLU (qui commence par "http(s)://") ; Pas obligatoire
                    ],
                    'unit_amount' => $value['product']->getPrix()*100, //Attention: bien mettre le format sans virgule et collé avec les centimes => dans notre cas, le prix est un entier donc ici on multiplie simplement par 100 (exemple 20€ donne 2000)
                    ],
                    'quantity' => $value['quantity'],                
                ];
        }

        $session = Session::create([
            'line_items' => [
                $line_items //On place le tableau construit juste au-dessus, pour les line_items.
            ],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('success_url', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('cancel_url', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        //dd($session);
        return $this->redirect($session->url, 303);
    }

    /**
     * @Route("/payment/success", name="success_url")
     */
    public function successUrl(SessionInterface $session)
    {
        #Après le paiement success : libre a vous de vider les sessions et donc le panier.
        //$session->clear();
        $session->remove('panier');

        return $this->render("payment/success.html.twig");
    }

    /**
     * @Route("/payment/cancel", name="cancel_url")
     */
    public function cancelUrl()
    {
        return $this->render("payment/cancel.html.twig");
    }
}
