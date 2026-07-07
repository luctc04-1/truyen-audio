import { ref } from 'vue'
import { extractApiPayload } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

/**
 * Load admin API data with consistent error / empty handling.
 */
export function useAdminPage() {
  const loading = ref(true)
  const error = ref(null)
  const toast = useToastStore()

  const run = async (loader, { errorMessage = 'Không tải được dữ liệu', silent = false } = {}) => {
    loading.value = true
    error.value = null
    try {
      return extractApiPayload(await loader())
    } catch (e) {
      error.value = e?.message || errorMessage
      if (!silent) {
        toast.error(error.value)
      }
      return null
    } finally {
      loading.value = false
    }
  }

  return { loading, error, run }
}
