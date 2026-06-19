import { ref } from 'vue';

/**
 * Composable for API errors
 *
 * @returns {Object}
 */
export const useError = () => {
    const error = ref(null);

    /**
     * Set error message
     *
     * @param {string} message
     */
    const setError = (message) => {
        error.value = message;
    };

    /**
     * Clear error
     */
    const clearError = () => {
        error.value = null;
    };

    /**
     * Handle error
     *
     * @param {Error|string} err
     */
    const handleError = (err) => {
        if (err instanceof Error) {
            setError(err.message);
        } else {
            setError(err);
        }
    };

    return {
        error,
        setError,
        clearError,
        handleError,
    };
};
