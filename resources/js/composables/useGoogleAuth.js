import { nextTick, ref } from 'vue';

const GSI_SCRIPT_URL = 'https://accounts.google.com/gsi/client';
const GSI_SCRIPT_SELECTOR = 'script[data-google-gsi]';

export function useGoogleAuth(onCredential) {
    const buttonRef = ref(null);
    const clientId = import.meta.env.VITE_GOOGLE_CLIENT_ID || '';

    const renderButton = () => {
        if (!clientId || !window.google?.accounts?.id || !buttonRef.value) {
            return;
        }

        const shell = buttonRef.value.parentElement;
        const buttonWidth = shell?.clientWidth || 360;

        buttonRef.value.innerHTML = '';

        window.google.accounts.id.initialize({
            client_id: clientId,
            callback: onCredential,
            auto_select: false,
        });

        window.google.accounts.id.renderButton(buttonRef.value, {
            type: 'standard',
            theme: 'outline',
            size: 'large',
            text: 'signin_with',
            shape: 'rectangular',
            width: buttonWidth,
            locale: 'vi',
        });
    };

    const mount = async () => {
        if (!clientId) {
            return;
        }

        await nextTick();

        if (window.google?.accounts?.id) {
            renderButton();
            return;
        }

        const existing = document.querySelector(GSI_SCRIPT_SELECTOR);
        if (existing) {
            existing.addEventListener('load', () => nextTick().then(renderButton), { once: true });
            return;
        }

        const script = document.createElement('script');
        script.src = GSI_SCRIPT_URL;
        script.async = true;
        script.defer = true;
        script.dataset.googleGsi = 'true';
        script.onload = () => nextTick().then(renderButton);
        document.head.appendChild(script);
    };

    return {
        buttonRef,
        clientId,
        mount,
    };
}
