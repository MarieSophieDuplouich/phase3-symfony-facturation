<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Force HTTPS en production et ajoute l'en-tête HSTS.
 *
 * N'agit qu'en production (APP_ENV=prod) pour ne jamais gêner le développement
 * local en HTTP. Si l'application tourne derrière un reverse proxy / load
 * balancer qui termine le TLS (Cloudflare, un LB cloud, un Nginx en frontal...),
 * pensez à configurer `framework.trusted_proxies` et `trusted_headers` dans
 * config/packages/framework.yaml, sinon $request->isSecure() renverra
 * toujours false côté PHP et provoquera une boucle de redirection infinie.
 */
final class HttpsRedirectSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly string $environment,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || 'prod' !== $this->environment) {
            return;
        }

        $request = $event->getRequest();

        if ($request->isSecure()) {
            return;
        }

        // On ne redirige que les requêtes "sûres" (GET/HEAD) : un POST redirigé
        // perdrait son corps et casserait les formulaires.
        if (!\in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return;
        }

        $httpsUrl = 'https://' . $request->getHost() . $request->getRequestUri();

        $event->setResponse(new RedirectResponse($httpsUrl, RedirectResponse::HTTP_MOVED_PERMANENTLY));
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || 'prod' !== $this->environment) {
            return;
        }

        if (!$event->getRequest()->isSecure()) {
            return;
        }

        // HSTS : demande aux navigateurs de ne plus jamais retenter du HTTP
        // sur ce domaine pendant 1 an, y compris pour les sous-domaines.
        $event->getResponse()->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains'
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 30],
            KernelEvents::RESPONSE => ['onKernelResponse', -10],
        ];
    }
}
