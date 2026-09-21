import { Controller } from '@hotwired/stimulus';

/**
 * Menu latéral responsive : masqué hors écran sur mobile, ouvert via le
 * bouton hamburger, avec un fond assombri cliquable pour le refermer.
 * Sur desktop (md:+), le CSS garde le menu toujours visible et ce
 * contrôleur n'a aucun effet.
 */
export default class extends Controller {
    static targets = ['panel', 'backdrop', 'button'];

    connect() {
        this.onPanelClick = (event) => {
            if (event.target.closest('a')) {
                this.close();
            }
        };
        this.panelTarget.addEventListener('click', this.onPanelClick);
    }

    disconnect() {
        this.panelTarget.removeEventListener('click', this.onPanelClick);
    }

    open() {
        this.panelTarget.classList.remove('-translate-x-full');
        this.backdropTarget.classList.remove('hidden');
        this.buttonTarget.setAttribute('aria-expanded', 'true');
        document.body.classList.add('overflow-hidden');
    }

    close() {
        this.panelTarget.classList.add('-translate-x-full');
        this.backdropTarget.classList.add('hidden');
        this.buttonTarget.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('overflow-hidden');
    }

    toggle() {
        if (this.panelTarget.classList.contains('-translate-x-full')) {
            this.open();
        } else {
            this.close();
        }
    }
}
