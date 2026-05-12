<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Sensiolabs\GotenbergBundle\Processor\TempfileProcessor;

final class MailController extends AbstractController
{
    #[Route('/mail', name: 'app_mail')]
    public function index(
        MailerInterface $mailer,
        GotenbergPdfInterface $gotenberg
    ): Response {

        $filePdf = $gotenberg->html()
            ->content('test/index.html.twig')
            ->processor(new TempfileProcessor())
            ->generate()
            ->process();

        $email = (new Email())
            ->from('website@cool.com')
            ->to('ms.duplouichiscod@gmail.com')
            ->subject('Mail !!!!!!!!!!!! avec PDF')
            ->text('Bonjour, voici votre PDF en pièce jointe.')

            ->attach(
                $filePdf,
                'document.pdf',
                'application/pdf'
            );

        $mailer->send($email);

        return $this->render('mail/index.html.twig');
    }
}