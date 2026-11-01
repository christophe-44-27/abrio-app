import type { VisitOptions } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from '@/composables/useI18n';

export type SaveStatus = 'saved' | 'dirty' | 'saving' | 'error' | 'incomplete';

type Options = {
    /** Plain-data view of what gets saved, to tell whether anything changed. */
    snapshot: () => unknown;
    /** False while a row is half filled in: nothing is sent until it's complete. */
    ready: () => boolean;
    /** Performs the request (staying on the page) with the given visit options. */
    send: (options: VisitOptions) => void;
};

/**
 * Explicit saving: nothing is sent until the user asks. Tracks whether there
 * are unsaved changes, exposes a status for the page, and warns before the
 * user leaves with changes that would be lost.
 */
export function useManualSave({ snapshot, ready, send }: Options) {
    const { t } = useI18n();

    const status = ref<SaveStatus>('saved');
    const savedAt = ref<Date | null>(null);

    let lastSaved = JSON.stringify(snapshot());

    const hasUnsavedChanges = () => JSON.stringify(snapshot()) !== lastSaved;

    watch(
        () => JSON.stringify(snapshot()),
        (current) => {
            if (status.value === 'saving') {
                return;
            }

            status.value = current === lastSaved ? 'saved' : 'dirty';
        },
    );

    const saveNow = () => {
        if (status.value === 'saving' || !hasUnsavedChanges()) {
            return;
        }

        if (!ready()) {
            status.value = 'incomplete';

            return;
        }

        const current = JSON.stringify(snapshot());
        status.value = 'saving';

        send({
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                lastSaved = current;
                savedAt.value = new Date();
                status.value = hasUnsavedChanges() ? 'dirty' : 'saved';
            },
            onError: () => {
                status.value = 'error';
            },
        });
    };

    // Ask before leaving through a link (GET); saving is a PUT so it passes.
    let removeGuard: (() => void) | undefined;

    const onBeforeUnload = (event: BeforeUnloadEvent) => {
        if (hasUnsavedChanges()) {
            event.preventDefault();
        }
    };

    onMounted(() => {
        removeGuard = router.on('before', (event) => {
            const isNavigation = event.detail.visit.method === 'get';

            if (isNavigation && hasUnsavedChanges()) {
                return window.confirm(t.value.save.leave);
            }
        });
        window.addEventListener('beforeunload', onBeforeUnload);
    });

    onBeforeUnmount(() => {
        removeGuard?.();
        window.removeEventListener('beforeunload', onBeforeUnload);
    });

    return { status, savedAt, saveNow };
}
