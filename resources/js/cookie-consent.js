/**
 * Cookie consent (EU): Google Analytics is loaded only after the visitor allows analytics cookies.
 * Rejecting is as easy as accepting, the choice can be changed any time ("Nastavenia cookies").
 * The choice is stored in localStorage (technically necessary, not a tracking cookie).
 */
const STORAGE_KEY = 'bv-cookie-consent';
const CONSENT_VERSION = 1;

function readConsent() {
    try {
        const stored = JSON.parse(localStorage.getItem(STORAGE_KEY));

        return stored?.version === CONSENT_VERSION ? stored : null;
    } catch {
        return null;
    }
}

function loadGoogleAnalytics(measurementId) {
    if (!measurementId || window.__bvGaLoaded) {
        return;
    }

    window.__bvGaLoaded = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
        window.dataLayer.push(arguments);
    };

    window.gtag('consent', 'default', {
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied',
        analytics_storage: 'granted',
    });
    window.gtag('js', new Date());
    window.gtag('config', measurementId, { anonymize_ip: true });

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(measurementId)}`;
    document.head.appendChild(script);
}

function removeAnalyticsCookies() {
    document.cookie.split(';').forEach((cookie) => {
        const name = cookie.split('=')[0].trim();

        if (name.startsWith('_ga')) {
            const domain = window.location.hostname.replace(/^www\./, '');
            document.cookie = `${name}=; Max-Age=0; path=/; domain=.${domain}`;
            document.cookie = `${name}=; Max-Age=0; path=/`;
        }
    });
}

export function cookieConsent(measurementId = '') {
    return {
        open: false,
        details: false,
        analytics: false,

        init() {
            const consent = readConsent();

            if (consent) {
                this.analytics = consent.analytics;
                this.apply();
            } else {
                this.open = true;
            }

            window.addEventListener('open-cookie-settings', () => {
                this.details = true;
                this.open = true;
            });
        },

        acceptAll() {
            this.analytics = true;
            this.save();
        },

        reject() {
            this.analytics = false;
            this.save();
        },

        save() {
            localStorage.setItem(
                STORAGE_KEY,
                JSON.stringify({ version: CONSENT_VERSION, analytics: this.analytics, date: new Date().toISOString() }),
            );
            this.open = false;
            this.details = false;
            this.apply();
        },

        apply() {
            if (this.analytics) {
                loadGoogleAnalytics(measurementId);
            } else {
                removeAnalyticsCookies();

                if (window.gtag) {
                    window.gtag('consent', 'update', { analytics_storage: 'denied' });
                }
            }
        },
    };
}
