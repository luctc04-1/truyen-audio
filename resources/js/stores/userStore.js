import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useUserStore = defineStore('user', () => {
  // State
  const user = ref({
    id: 1,
    name: 'Người dùng',
    email: 'user@example.com',
    avatar: 'N',
    isVIP: false,
    vipExpireDate: null,
    listenHistory: []
  })

  const isAuthenticated = ref(false)

  // Methods
  const login = (email, password) => {
    isAuthenticated.value = true
    user.value.email = email
  }

  const logout = () => {
    isAuthenticated.value = false
    user.value = {
      id: 1,
      name: 'Người dùng',
      email: '',
      avatar: 'N',
      isVIP: false,
      vipExpireDate: null,
      listenHistory: []
    }
  }

  const subscribeVIP = (plan) => {
    user.value.isVIP = true
    const date = new Date()
    switch (plan) {
      case '1month':
        date.setMonth(date.getMonth() + 1)
        break
      case '3months':
        date.setMonth(date.getMonth() + 3)
        break
      case '6months':
        date.setMonth(date.getMonth() + 6)
        break
      case '12months':
        date.setFullYear(date.getFullYear() + 1)
        break
    }
    user.value.vipExpireDate = date
  }

  const addToHistory = (story, episode) => {
    user.value.listenHistory.unshift({
      story,
      episode,
      timestamp: new Date()
    })
  }

  return {
    user,
    isAuthenticated,
    login,
    logout,
    subscribeVIP,
    addToHistory
  }
})
