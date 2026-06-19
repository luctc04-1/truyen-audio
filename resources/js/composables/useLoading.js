import { ref } from 'vue';

/**
 * Composable for loading state
 *
 * @returns {Object}
 */
export const useLoading = () => {
    const loading = ref(false);

    /**
     * Set loading state
     *
     * @param {boolean} state
     */
    const setLoading = (state) => {
        loading.value = state;
    };

    /**
     * Execute async function with loading state
     *
     * @param {Function} callback
     * @returns {Promise}
     */
    const withLoading = async (callback) => {
        try {
            setLoading(true);
            return await callback();
        } finally {
            setLoading(false);
        }
    };

    return {
        loading,
        setLoading,
        withLoading,
    };
};
