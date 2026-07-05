import { useAuthStore } from '@/stores/authStore'

const requiresAdmin = (to) => to.matched.some((r) => r.meta.requiresAdmin)

const requiresClientAuth = (to) => to.matched.some((r) => r.meta.requiresAuth && !r.meta.requiresAdmin)

const handlers = {
  async bootstrap() {
    await useAuthStore().bootstrap()
    return true
  },

  guest({ to }) {
    const auth = useAuthStore()
    if (to.meta.guestOnly && auth.isAuthenticated) {
      return { name: 'Home' }
    }
    return true
  },

  client({ to }) {
    const auth = useAuthStore()
    if (requiresClientAuth(to) && !auth.isAuthenticated) {
      return { name: 'Auth', query: { redirect: to.fullPath } }
    }
    return true
  },

  admin({ to }) {
    if (!requiresAdmin(to)) {
      return true
    }
    const auth = useAuthStore()
    if (!auth.isAuthenticated || !auth.isAdmin) {
      return { name: 'Home' }
    }
    return true
  },
}

export function resolveMiddleware(to) {
  const chain = ['bootstrap', 'guest']
  if (requiresAdmin(to)) {
    chain.push('admin')
  } else if (requiresClientAuth(to)) {
    chain.push('client')
  }
  return chain
}

export async function runMiddleware(to, from, names) {
  for (const name of names) {
    const result = await handlers[name]?.({ to, from })
    if (result !== true && result !== undefined) {
      return result
    }
  }
  return true
}
