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
        $email = (new Email())
            ->from('test@test.com')
            ->to('ms.duplouichiscod@gmail.com')
            ->subject('Test Mailpit')
            ->text('Bonjour depuis Symfony');

        $mailer->send($email);

        return new Response('Mail envoyé');
    }
}