/**
 * Utility for managing dynamic document title, meta tags, OpenGraph, Canonical link, and JSON-LD Schema on client side.
 */

const DEFAULT_TITLE = 'Truyện Audio Hay | Nghe Truyện Hay Chọn Lọc Online'
const DEFAULT_DESCRIPTION = 'Truyện Audio Hay - Website nghe truyện audio chọn lọc hay nhất, đọc truyện đêm khuya, ngôn tình, tiên hiệp, trinh thám mượt mà chất lượng cao.'
const DEFAULT_KEYWORDS = 'truyện audio hay, nghe truyện audio, truyên audio hay, truyện đọc đêm khuya, nghe truyện online, audio truyện hay'
const DEFAULT_IMAGE = '/favicon.ico'

function setOrUpdateMeta(selector, attrName, attrValue, content) {
  let element = document.head.querySelector(selector)
  if (!element) {
    element = document.createElement('meta')
    element.setAttribute(attrName, attrValue)
    document.head.appendChild(element)
  }
  element.setAttribute('content', content || '')
}

function setOrUpdateLink(rel, href) {
  let element = document.head.querySelector(`link[rel="${rel}"]`)
  if (!element) {
    element = document.createElement('link')
    element.setAttribute('rel', rel)
    document.head.appendChild(element)
  }
  element.setAttribute('href', href || window.location.href)
}

function setOrUpdateJsonLd(schemaData) {
  let script = document.getElementById('seo-jsonld')
  if (!schemaData) {
    if (script) script.remove()
    return
  }

  if (!script) {
    script = document.createElement('script')
    script.id = 'seo-jsonld'
    script.type = 'application/ld+json'
    document.head.appendChild(script)
  }
  script.textContent = typeof schemaData === 'string' ? schemaData : JSON.stringify(schemaData)
}

/**
 * Update client SEO metadata
 * @param {Object} options
 * @param {string} [options.title]
 * @param {string} [options.description]
 * @param {string} [options.keywords]
 * @param {string} [options.image]
 * @param {string} [options.url]
 * @param {string} [options.type]
 * @param {Object|Array} [options.schema]
 */
export function setSeoMeta({ title, description, keywords, image, url, type = 'website', schema = null } = {}) {
  const metaTitle = title ? (title.includes('Truyện Audio Hay') ? title : `${title} - Truyện Audio Hay`) : DEFAULT_TITLE
  const metaDesc = description || DEFAULT_DESCRIPTION
  const metaKeywords = keywords || DEFAULT_KEYWORDS
  const metaImage = image || DEFAULT_IMAGE
  const metaUrl = url || window.location.href

  // Title
  document.title = metaTitle

  // Basic Meta
  setOrUpdateMeta('meta[name="description"]', 'name', 'description', metaDesc)
  setOrUpdateMeta('meta[name="keywords"]', 'name', 'keywords', metaKeywords)

  // OpenGraph
  setOrUpdateMeta('meta[property="og:site_name"]', 'property', 'og:site_name', 'Truyện Audio Hay')
  setOrUpdateMeta('meta[property="og:title"]', 'property', 'og:title', metaTitle)
  setOrUpdateMeta('meta[property="og:description"]', 'property', 'og:description', metaDesc)
  setOrUpdateMeta('meta[property="og:image"]', 'property', 'og:image', metaImage)
  setOrUpdateMeta('meta[property="og:url"]', 'property', 'og:url', metaUrl)
  setOrUpdateMeta('meta[property="og:type"]', 'property', 'og:type', type)

  // Twitter Cards
  setOrUpdateMeta('meta[name="twitter:card"]', 'name', 'twitter:card', 'summary_large_image')
  setOrUpdateMeta('meta[name="twitter:title"]', 'name', 'twitter:title', metaTitle)
  setOrUpdateMeta('meta[name="twitter:description"]', 'name', 'twitter:description', metaDesc)
  setOrUpdateMeta('meta[name="twitter:image"]', 'name', 'twitter:image', metaImage)

  // Canonical Link
  setOrUpdateLink('canonical', metaUrl)

  // Schema JSON-LD
  setOrUpdateJsonLd(schema)
}

export function resetSeoMeta() {
  setSeoMeta({
    title: '',
    description: DEFAULT_DESCRIPTION,
    image: DEFAULT_IMAGE,
  })
}

