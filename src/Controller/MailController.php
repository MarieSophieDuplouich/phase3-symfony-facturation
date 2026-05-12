<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class MailController extends AbstractController
{
    #[Route('/mail', name: 'app_mail')]
    public function index(MailerInterface $mailer): Response
    {
        $file_resource = tmpFile();    //Ouvre un fichier temporaire (similaire à fopen)
        fwrite($file_resource, "Ceci est le contenu du fichier à attacher au mail !");

        $email = new Email();
        $email->from("website@cool.com")
            ->to("ms.duplouichiscod@gmail.com")
            ->subject("Ceci est un mail Test sujet")
            ->attach($file_resource, "bienvenuecheznous.txt")    // ++ AJOUTER UNE PIÈCE JOINTE.
            ->text("Ceci est un mail Test texte");

        $mailer->send($email);


        return $this->render('mail/index.html.twig', [
            'controller_name' => 'MailController',
        ]);
    }
}