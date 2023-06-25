<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function new(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer) : Response
    {
        #Etape 1 : Créer un objet vide
        $contact = new Contact;

        #Etape 2 : Création du formulaire (Méthode 2)
        $formContact = $this->createForm(ContactType::class, $contact);

        $formContact->handleRequest($request);
        if($formContact->isSubmitted() && $formContact->isValid())
        {
            #Etape 1 : On appel l'entity manager de doctrine
            $entityManager = $doctrine->getManager();

            #Etape 3 : on indique a doctrine que l'on souhaite préparer l'enregistrement d'un nouvel élément
            $entityManager->persist($contact);

            #etape 4: on valide a doctrine que l'on veut enregisterer/persister en BDD
            $entityManager->flush();
            #etape 5: on affiche ou on redirge vers une autre page 
            $this->addFlash('contact',' Nous vous répondrons au plus vite, merci !');

            #Etape 6 : Envoi de l'email contact
        $email = (new TemplatedEmail())
            ->from($contact->getEmail())
            ->to('contact@monsite.com')
            ->subject('Demande de contact')
            ->htmlTemplate('emails/contact.html.twig')

            // pass variables (name => value) to the template
            ->context([
                "contact"=> $contact
            ]);
            $mailer->send($email);

        return $this -> redirectToRoute('home');

        }

        #Etape 3 : On envoie le formulaire dans la vue
        return $this->render('contact.html.twig', [
            'formContact'=> $formContact->createView()
        ]);
    }
}
