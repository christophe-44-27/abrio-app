import en from './en';
import fr from './fr';
import type { Messages } from './fr';

export type Locale = 'fr' | 'en';

export const messages: Record<Locale, Messages> = { fr, en };

export const landingPath = (locale: Locale): string =>
    locale === 'fr' ? '/' : '/en';
