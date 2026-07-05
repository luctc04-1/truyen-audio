export const SERIES_FALLBACK_COVER =
    'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=120&q=80';

export const formatTime = (seconds) => {
    if (!seconds || seconds === 0) return '0:00';

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);

    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    return `${minutes}:${secs.toString().padStart(2, '0')}`;
};

/**
 * Nhãn tập: episode_number = 0 → "Full", còn lại → "Tập N".
 */
export const formatEpisodeLabel = (episodeNumber) => {
    if (episodeNumber === 0 || episodeNumber === '0') return 'Full';
    if (episodeNumber == null || episodeNumber === '') return '';
    return `Tập ${episodeNumber}`;
};

/** Ghép nhãn tập + tên: "Tập 3 • Tên truyện" */
export const formatEpisodeWithTitle = (episodeNumber, title) => {
    const label = formatEpisodeLabel(episodeNumber);
    const name = (title ?? '').trim();
    if (!label) return name;
    if (!name) return label;
    return `${label} • ${name}`;
};

/**
 * Format bytes utility
 *
 * @param {number} bytes
 * @param {number} decimals
 * @returns {string}
 */
export const formatBytes = (bytes, decimals = 2) => {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
};

/**
 * Debounce utility
 *
 * @param {Function} func
 * @param {number} wait
 * @returns {Function}
 */
export const debounce = (func, wait) => {
    let timeout;

    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };

        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

/**
 * Throttle utility
 *
 * @param {Function} func
 * @param {number} limit
 * @returns {Function}
 */
export const throttle = (func, limit) => {
    let inThrottle;

    return function (...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => (inThrottle = false), limit);
        }
    };
};

/** Unwrap Laravel API payload: { success, data } → data */
export const extractApiPayload = (response) => response?.data ?? response;

export const formatMoney = (n) => `${Number(n || 0).toLocaleString('vi-VN')}đ`;

export const formatAdminDate = (iso, { dateOnly = false } = {}) => {
  if (!iso) return '—';
  return dateOnly
    ? new Date(iso).toLocaleDateString('vi-VN')
    : new Date(iso).toLocaleString('vi-VN');
};

export const formatCompactCount = (n) => {
  const num = Number(n || 0);
  if (num >= 1_000_000) return `${(num / 1_000_000).toFixed(1)}M`;
  if (num >= 1_000) return `${(num / 1_000).toFixed(1)}K`;
  return num.toLocaleString('vi-VN');
};

const ORDER_STATUS_LABELS = {
  paid: 'Đã thanh toán',
  pending: 'Chờ xử lý',
  cancelled: 'Đã hủy',
  failed: 'Thất bại',
};

export const orderStatusLabel = (status) => ORDER_STATUS_LABELS[status] || status;

export const orderStatusClass = (status) => ({
  paid: 'badge bg-success-subtle text-success',
  pending: 'badge bg-warning-subtle text-warning',
  cancelled: 'badge bg-secondary-subtle text-secondary',
  failed: 'badge bg-danger-subtle text-danger',
}[status] || 'badge bg-light text-dark');

/**
 * Thời gian tương đối tiếng Việt.
 */
export const formatRelativeTime = (iso) => {
    if (!iso) return '';

    const diff = Date.now() - new Date(iso).getTime();
    const mins = Math.floor(diff / 60000);

    if (mins < 1) return 'Vừa xong';
    if (mins < 60) return `${mins} phút trước`;

    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours} giờ trước`;

    const days = Math.floor(hours / 24);
    if (days < 30) return `${days} ngày trước`;

    const months = Math.floor(days / 30);
    if (months < 12) return `${months} tháng trước`;

    const years = Math.floor(months / 12);
    return `${years} năm trước`;
};

/**
 * Chia sẻ truyện — Web Share API hoặc sao chép link.
 * @returns {'shared'|'copied'|'cancelled'|'failed'}
 */
export const shareStory = async (story) => {
    if (!story?.id) return 'failed';

    const url = `${window.location.origin}/story/${story.id}`;
    const title = story.title || 'Truyện audio';
    const text = `Nghe truyện audio: ${title}`;

    if (navigator.share) {
        try {
            await navigator.share({ title, text, url });
            return 'shared';
        } catch (err) {
            if (err?.name === 'AbortError') return 'cancelled';
        }
    }

    try {
        await navigator.clipboard.writeText(url);
        return 'copied';
    } catch {
        const input = document.createElement('textarea');
        input.value = url;
        input.setAttribute('readonly', '');
        input.style.position = 'absolute';
        input.style.left = '-9999px';
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        return 'copied';
    }
};
