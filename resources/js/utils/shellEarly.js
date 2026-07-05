/** URL đang ở khu admin (dùng trước khi router resolve xong). */
export function isAdminPath(path = window.location.pathname) {
  return path === '/admin' || path.startsWith('/admin/')
}

const ADMIN_HTML_ATTRS = {
  'data-bs-theme': 'light',
  'data-layout': 'vertical',
  'data-topbar': 'light',
  'data-sidebar': 'dark',
  'data-sidebar-size': 'lg',
}

/** Đồng bộ shell admin (nền đã set sẵn trong app.blade.php). */
export function applyShellEarly() {
  if (!isAdminPath()) {
    return
  }

  document.body.classList.add('admin-route')
  const root = document.documentElement
  Object.entries(ADMIN_HTML_ATTRS).forEach(([key, value]) => {
    root.setAttribute(key, value)
  })
}
