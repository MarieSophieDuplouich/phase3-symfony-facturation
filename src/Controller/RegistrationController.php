<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


class RegistrationController extends AbstractController
{
    private const SESSION_KEY_RENDERED_AT = 'registration_form_rendered_at';

    /** Délai minimum (secondes) entre l'affichage du formulaire et sa soumission. */
    private const MIN_SUBMIT_DELAY_SECONDS = 2;

    /** Nombre maximum d'inscriptions tentées depuis une même IP par heure. */
    private const MAX_ATTEMPTS_PER_HOUR = 10;

    public function __construct(
        #[Autowire(service: 'cache.app')]
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $session = $request->hasSession() ? $request->getSession() : null;

        if ($request->isMethod('GET') && $session !== null) {
            // On mémorise l'heure d'affichage du formulaire pour détecter les
            // soumissions trop rapides pour être humaines (bots).
            $session->set(self::SESSION_KEY_RENDERED_AT, time());
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isRateLimited($request)) {
                $this->addFlash('error', 'Trop de tentatives. Merci de réessayer plus tard.');

                return $this->redirectToRoute('app_register');
            }

            if ($this->isSubmittedTooFast($session)) {
                // Comportement probable d'un bot : on n'enregistre rien, mais on
                // ne révèle pas la raison exacte pour ne pas aider à contourner
                // la protection, et on renvoie une page de succès classique.
                return $this->redirectToRoute('app_login');
            }

            $this->registerAttempt($request);

            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            //MSD le user.is_verified qui pose problème
            // $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    private function isSubmittedTooFast(?\Symfony\Component\HttpFoundation\Session\SessionInterface $session): bool
    {
        if ($session === null || !$session->has(self::SESSION_KEY_RENDERED_AT)) {
            // Pas d'info fiable : on laisse passer plutôt que de bloquer un
            // utilisateur légitime (session expirée, cookies désactivés...).
            return false;
        }

        $renderedAt = (int) $session->get(self::SESSION_KEY_RENDERED_AT);

        return (time() - $renderedAt) < self::MIN_SUBMIT_DELAY_SECONDS;
    }

    private function isRateLimited(Request $request): bool
    {
        $item = $this->cache->getItem($this->rateLimitCacheKey($request));

        return $item->isHit() && (int) $item->get() >= self::MAX_ATTEMPTS_PER_HOUR;
    }

    private function registerAttempt(Request $request): void
    {
        $item = $this->cache->getItem($this->rateLimitCacheKey($request));
        $count = $item->isHit() ? (int) $item->get() : 0;

        $item->set($count + 1);
        $item->expiresAfter(3600);

        $this->cache->save($item);
    }

    private function rateLimitCacheKey(Request $request): string
    {
        return 'register_attempts_' . md5($request->getClientIp() ?? 'unknown');
    }
}