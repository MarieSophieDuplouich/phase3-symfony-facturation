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
        
        $file_resource = tmpFile();
        fwrite($file_resource, "Ceci est le contenu du fichier à attacher au mail !");

        $email = new Email();
        $email->from("website@cool.com")
            ->to("ms.duplouichiscod@gmail.com")
            ->subject("Ceci est un mail Test sujet")
            ->attach($file_resource, "bienvenuecheznous.txt")
            ->text("Ceci est un mail Test texte");

        $mailer->send($email);


        return $this->render('mail/index.html.twig', [
            'controller_name' => 'MailController',
        ]);
    }
}
