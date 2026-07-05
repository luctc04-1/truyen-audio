import { ref } from 'vue'
import AdminService from '@/services/AdminService'
import { extractApiPayload } from '@/utils/helpers'

export function useAdminSeriesOptions(perPage = 200) {
  const seriesOptions = ref([])

  const loadSeriesOptions = async () => {
    try {
      const res = extractApiPayload(await AdminService.getSeries({ per_page: perPage }))
      seriesOptions.value = res?.items ?? []
    } catch {
      seriesOptions.value = []
    }
  }

  return { seriesOptions, loadSeriesOptions }
}
