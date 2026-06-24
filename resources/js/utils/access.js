/**
 * Kiểm tra quyền nghe tập dựa trên is_premium của nội dung và gói VIP của user.
 */
export function canPlayEpisode(episode, auth) {
  if (!episode?.is_premium) return true
  return !!auth?.isAuthenticated && !!auth?.isPremium
}

export function isEpisodeLocked(episode, auth) {
  return !!episode?.is_premium && !canPlayEpisode(episode, auth)
}

/**
 * Chặn phát và hướng user tới đăng nhập hoặc trang VIP.
 * @returns {boolean} true nếu được phép phát
 */
export function requirePlayAccess(episode, auth, router, toast) {
  if (canPlayEpisode(episode, auth)) {
    return true
  }

  if (!auth?.isAuthenticated) {
    toast?.info?.('Vui lòng đăng nhập để nghe tập VIP')
    router?.push('/auth')
    return false
  }

  toast?.info?.('Nâng cấp VIP để mở khóa tập này')
  router?.push('/vip')
  return false
}
