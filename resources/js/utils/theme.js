export function applyThemeToDocument(theme) {
  const root = document.documentElement
  if (theme === 'system') {
    root.setAttribute('data-theme', window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
  } else {
    root.setAttribute('data-theme', theme)
  }
}
