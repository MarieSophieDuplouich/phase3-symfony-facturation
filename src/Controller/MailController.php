<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Sensiolabs\GotenbergBundle\Processor\TempfileProcessor;
use Symfony\Component\HttpFoundation\Request;


#[IsGranted('ROLE_USER')]
final class MailController extends AbstractController
{
    #[Route('/mail', name: 'app_mail', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('mail/index.html.twig');
    }

    #[Route('/mail/send', name: 'app_mail_send', methods: ['POST'])]
    public function send(
        Request $request,
        MailerInterface $mailer,
        GotenbergPdfInterface $gotenberg
    ): Response {
        if (!$this->isCsrfTokenValid('send_mail', $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

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
            ->attach($filePdf, 'document.pdf', 'application/pdf');

        $mailer->send($email);

        $this->addFlash('success', 'Email envoyé.');

        return $this->redirectToRoute('app_mail');
    }
}