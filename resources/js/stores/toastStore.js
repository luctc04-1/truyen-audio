import { defineStore } from 'pinia';
import { ref } from 'vue';

let toastId = 0;
const DEFAULT_DURATION = 2800;

export const useToastStore = defineStore('toast', () => {
    const toasts = ref([]);

    const remove = (id) => {
        toasts.value = toasts.value.filter((toast) => toast.id !== id);
    };

    const show = (message, type = 'success', duration = DEFAULT_DURATION) => {
        const id = ++toastId;
        toasts.value.push({ id, message, type });
        window.setTimeout(() => remove(id), duration);
    };

    const success = (message) => show(message, 'success');
    const error = (message) => show(message, 'error', 3200);
    const info = (message) => show(message, 'info');

    return {
        toasts,
        remove,
        success,
        error,
        info,
    };
});
