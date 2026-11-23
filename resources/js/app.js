import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { bookingForm } from './booking';
import { cookieConsent } from './cookie-consent';

// Bundle static images so Vite fingerprints them (Vite::asset in Blade).
import.meta.glob(['../images/**'], { eager: true });

Alpine.plugin(collapse);
Alpine.data('bookingForm', bookingForm);
Alpine.data('cookieConsent', cookieConsent);

window.Alpine = Alpine;
Alpine.start();
