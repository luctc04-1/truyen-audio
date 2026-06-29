import { ref } from 'vue';

const LIKE_POP_MS = 220;

export function useLikePop() {
    const likePop = ref(false);

    const triggerLikePop = (handler) => {
        likePop.value = true;
        window.setTimeout(() => {
            likePop.value = false;
        }, LIKE_POP_MS);
        handler?.();
    };

    return { likePop, triggerLikePop };
}
