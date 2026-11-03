import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

/** Fills the `{name}` placeholders of a translated text. */
export function fill(
    text: string,
    vars: Record<string, string | number | undefined>,
): string {
    return Object.entries(vars).reduce(
        (s, [k, v]) => s.split(`{${k}}`).join(String(v ?? '')),
        text,
    );
}
