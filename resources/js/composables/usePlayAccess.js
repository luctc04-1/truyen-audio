import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import { useToastStore } from '@/stores/toastStore'
import { isEpisodeLocked, requirePlayAccess } from '@/utils/access'

export function usePlayAccess() {
  const auth = useAuthStore()
  const router = useRouter()
  const toast = useToastStore()

  const playBlocked = (episode) => isEpisodeLocked(episode, auth)
  const ensurePlayAccess = (episode) => requirePlayAccess(episode, auth, router, toast)

  return { auth, playBlocked, ensurePlayAccess }
}
