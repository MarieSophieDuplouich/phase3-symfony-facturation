<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SeoController extends AbstractController
{
    /**
     * Pages publiques à référencer dans le sitemap.
     */
    private const PUBLIC_ROUTES = [
        'app_login',
        'app_register',
        'app_terms',
        'app_privacy',
    ];

    #[Route('/robots.txt', name: 'app_robots', methods: ['GET'])]
    public function robots(UrlGeneratorInterface $urlGenerator): Response
    {
        $sitemapUrl = $urlGenerator->generate('app_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $lines = [
            'User-agent: *',
            // L'application (dashboard, factures, clients, produits...) est un espace
            // privé derrière authentification : on évite de faire perdre du temps
            // au crawler dessus, il ne pourrait de toute façon pas s'y connecter.
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /client',
            'Disallow: /invoice',
            'Disallow: /product',
            'Disallow: /mail',
            'Disallow: /user',
            '',
            'Sitemap: ' . $sitemapUrl,
        ];

        return new Response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'])]
    public function sitemap(UrlGeneratorInterface $urlGenerator): Response
    {
        $urls = array_map(
            fn (string $routeName) => $urlGenerator->generate($routeName, [], UrlGeneratorInterface::ABSOLUTE_URL),
            self::PUBLIC_ROUTES
        );

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        foreach ($urls as $url) {
            $entry = $xml->addChild('url');
            $entry->addChild('loc', htmlspecialchars($url, ENT_XML1));
            $entry->addChild('changefreq', 'monthly');
        }

        return new Response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
