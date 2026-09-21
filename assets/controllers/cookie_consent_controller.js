import { Controller } from '@hotwired/stimulus';

/**
 * Bandeau de consentement aux cookies (RGPD).
 *
 * - Rien n'est chargé tant que l'utilisateur n'a pas fait de choix.
 * - Le choix est mémorisé dans le localStorage du navigateur (pas de cookie
 *   nécessaire pour retenir le consentement lui-même).
 * - Un évènement DOM `cookie-consent:changed` est émis à chaque changement,
 *   avec `detail.analytics` (true/false), pour que d'autres scripts
 *   (ex: analytics) puissent réagir sans être couplés à ce contrôleur.
 */
export default class extends Controller {
    static targets = ['banner'];

    static STORAGE_KEY = 'cookie_consent';

    connect() {
        const consent = this.readConsent();

        if (consent === null) {
            this.bannerTarget.classList.remove('hidden');
        } else {
            this.dispatchConsent(consent === 'accepted');
        }
    }

    acceptAll() {
        this.saveConsent('accepted');
    }

    rejectNonEssential() {
        this.saveConsent('rejected');
    }

    saveConsent(value) {
        try {
            window.localStorage.setItem(this.constructor.STORAGE_KEY, value);
        } catch (e) {
            // Stockage indisponible (navigation privée stricte, etc.) : on
            // n'interrompt pas le parcours de l'utilisateur pour autant.
        }

        this.bannerTarget.classList.add('hidden');
        this.dispatchConsent(value === 'accepted');
    }

    readConsent() {
        try {
            return window.localStorage.getItem(this.constructor.STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    dispatchConsent(analyticsAllowed) {
        window.dispatchEvent(new CustomEvent('cookie-consent:changed', {
            detail: { analytics: analyticsAllowed },
        }));
    }
}
