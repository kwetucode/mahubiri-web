import { createI18n } from 'vue-i18n';
import fr from './locales/fr.json';
import en from './locales/en.json';
import sw from './locales/sw.json';
import ln from './locales/ln.json';

const savedLocale = localStorage.getItem('mahubiri-locale') || 'fr';

// Set html lang attribute on initial load
document.documentElement.setAttribute('lang', savedLocale);

const i18n = createI18n({
    legacy: false,
    locale: savedLocale,
    fallbackLocale: 'fr',
    messages: { fr, en, sw, ln },
    globalInjection: true,
});

export default i18n;

/**
 * Helper to change locale and persist it.
 * Uses the global composer's locale ref directly.
 */
export function setLocale(newLocale) {
    const global = i18n.global;
    // WritableComputedRef in vue-i18n v11 (legacy: false)
    global.locale.value = newLocale;
    localStorage.setItem('mahubiri-locale', newLocale);
    document.documentElement.setAttribute('lang', newLocale);
}

/**
 * Available locales with labels + flags.
 */
export const availableLocales = [
    { code: 'fr', label: 'Français', flag: '🇫🇷' },
    { code: 'en', label: 'English', flag: '🇬🇧' },
    { code: 'sw', label: 'Kiswahili', flag: '🇹🇿' },
    { code: 'ln', label: 'Lingála', flag: '🇨🇩' },
];
