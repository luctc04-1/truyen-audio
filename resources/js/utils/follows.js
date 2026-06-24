const STORAGE_KEY = 'followed_series'

export function getFollowedIds() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    const ids = raw ? JSON.parse(raw) : []
    return Array.isArray(ids) ? ids.map(String) : []
  } catch {
    return []
  }
}

export function isFollowed(id) {
  if (!id) return false
  return getFollowedIds().includes(String(id))
}

export function toggleFollow(id) {
  if (!id) return false
  const sid = String(id)
  const ids = getFollowedIds()
  const index = ids.indexOf(sid)
  if (index >= 0) {
    ids.splice(index, 1)
    localStorage.setItem(STORAGE_KEY, JSON.stringify(ids))
    return false
  }
  ids.push(sid)
  localStorage.setItem(STORAGE_KEY, JSON.stringify(ids))
  return true
}
