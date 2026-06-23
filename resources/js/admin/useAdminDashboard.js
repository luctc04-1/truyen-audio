import { computed, ref } from 'vue'

const sidebarOpen = ref(false)
const search = ref('')
const activeSection = ref('dashboard')
const activeFilter = ref('Tất cả')

const menuItems = [
  { id: 'dashboard', label: 'Tổng quan', icon: 'ri-dashboard-2-line' },
  { id: 'series', label: 'Truyện audio', icon: 'ri-book-open-line', count: '1.2k' },
  { id: 'episodes', label: 'Tập truyện', icon: 'ri-play-circle-line' },
  { id: 'categories', label: 'Danh mục & tag', icon: 'ri-price-tag-3-line' },
  { id: 'users', label: 'Người dùng', icon: 'ri-user-3-line' },
  { id: 'orders', label: 'Gói VIP & đơn', icon: 'ri-vip-crown-line' },
  { id: 'jobs', label: 'Crawl / Upload', icon: 'ri-cloud-line', count: '12' },
  { id: 'comments', label: 'Bình luận', icon: 'ri-chat-3-line', count: '8' },
  { id: 'settings', label: 'Cài đặt hệ thống', icon: 'ri-settings-3-line' }
]

const metrics = [
  { label: 'Tổng truyện', value: '1,248', trend: 12, icon: 'ri-book-2-line', tone: 'blue' },
  { label: 'Tập audio', value: '34,890', trend: 8, icon: 'ri-headphone-line', tone: 'green' },
  { label: 'Người dùng', value: '18,420', trend: 15, icon: 'ri-group-line', tone: 'orange' },
  { label: 'Doanh thu VIP', value: '86.4M', trend: -3, icon: 'ri-bank-card-line', tone: 'pink' }
]

const statusFilters = ['Tất cả', 'Đang phát', 'VIP', 'Nháp']

const stories = [
  {
    title: 'Thần Mộ',
    author: 'Thần Đông',
    narrator: 'MC Huyền Vũ',
    category: 'Tiên hiệp',
    episodes: 681,
    listens: '2.4M',
    status: 'Đang phát',
    statusClass: 'published',
    source: 'truyenfull',
    hot: true,
    seo: 'Đủ',
    published: '23/06/2026',
    cover: '/theme/admin/assets/images/products/img-1.png'
  },
  {
    title: 'Đấu Phá Thương Khung',
    author: 'Thiên Tằm Thổ Đậu',
    narrator: 'Audio Mê Truyện',
    category: 'Huyền huyễn',
    episodes: 1648,
    listens: '6.8M',
    status: 'VIP',
    statusClass: 'premium',
    source: 'metruyencv',
    hot: true,
    seo: 'Đủ',
    published: '22/06/2026',
    cover: '/theme/admin/assets/images/products/img-2.png'
  },
  {
    title: 'Ma Thổi Đèn',
    author: 'Thiên Hạ Bá Xướng',
    narrator: 'Đình Soạn',
    category: 'Trinh thám',
    episodes: 312,
    listens: '980K',
    status: 'Đang phát',
    statusClass: 'published',
    source: 'manual',
    hot: false,
    seo: 'Thiếu OG',
    published: '20/06/2026',
    cover: '/theme/admin/assets/images/products/img-3.png'
  },
  {
    title: 'Lịch sử Việt Nam diễn nghĩa',
    author: 'Nhiều tác giả',
    narrator: 'Ban biên tập',
    category: 'Lịch sử',
    episodes: 42,
    listens: '128K',
    status: 'Nháp',
    statusClass: 'draft',
    source: 'manual',
    hot: false,
    seo: 'Chưa có',
    published: 'Chưa đăng',
    cover: '/theme/admin/assets/images/products/img-4.png'
  }
]

const episodes = [
  { number: 682, title: 'Chương 682: Cửa trời mở', series: 'Thần Mộ', duration: '24:18', plays: '18.4K', audio: 'Sẵn sàng', audioClass: 'published', publishAt: 'Hôm nay 20:00' },
  { number: 1649, title: 'Chương 1649: Dị hỏa hội tụ', series: 'Đấu Phá Thương Khung', duration: '31:05', plays: '42.1K', audio: 'Đang xử lý', audioClass: 'premium', publishAt: '24/06/2026' },
  { number: 313, title: 'Tập 313: Cổ mộ dưới sông', series: 'Ma Thổi Đèn', duration: '27:46', plays: '9.8K', audio: 'Lỗi file', audioClass: 'danger', publishAt: 'Nháp' }
]

const categories = [
  { name: 'Tiên hiệp', count: 328 },
  { name: 'Huyền huyễn', count: 246 },
  { name: 'Trinh thám', count: 118 },
  { name: 'Ngôn tình', count: 404 },
  { name: 'Lịch sử', count: 54 }
]

const tags = ['hot', 'full', 'vip', 'audio-moi', 'de-cu', 'kiem-hiep', 'do-thi', 'kinh-di', 'hai-huoc', 'lich-su']

const users = [
  { name: 'Minh Anh', email: 'minhanh@example.com', plan: 'VIP tháng', devices: 2, listens: '412 giờ', lastLogin: '10 phút trước', admin: false },
  { name: 'Hoàng Nam', email: 'nam@example.com', plan: 'Free', devices: 1, listens: '88 giờ', lastLogin: 'Hôm qua', admin: false },
  { name: 'Lan Phương', email: 'phuong@example.com', plan: 'VIP năm', devices: 4, listens: '1,204 giờ', lastLogin: '23/06/2026', admin: true }
]

const orders = [
  { code: 'TA-260623-001', user: 'Minh Anh', plan: 'VIP tháng', amount: '99.000đ', method: 'Momo', status: 'Đã thanh toán', statusClass: 'published' },
  { code: 'TA-260623-002', user: 'Hoàng Nam', plan: 'VIP quý', amount: '249.000đ', method: 'Banking', status: 'Chờ xử lý', statusClass: 'premium' },
  { code: 'TA-260623-003', user: 'Lan Phương', plan: 'VIP năm', amount: '799.000đ', method: 'VNPAY', status: 'Hoàn tiền', statusClass: 'draft' }
]

const crawlJobs = [
  { source: 'truyenfull.vn', detail: 'Thêm 18 truyện, 420 tập mới', time: 'Đang chạy', statusClass: 'published' },
  { source: 'metruyencv.com', detail: 'Lỗi timeout ở trang 12', time: '15 phút trước', statusClass: 'danger' },
  { source: 'manual import', detail: 'Import CSV hoàn tất', time: '1 giờ trước', statusClass: 'premium' }
]

const comments = [
  { user: 'Minh Anh', text: 'Tập 32 bị nhỏ tiếng đoạn cuối.', story: 'Thần Mộ - Chương 32' },
  { user: 'Hoàng Nam', text: 'Truyện này cuốn quá, admin ra thêm nha.', story: 'Ma Thổi Đèn' },
  { user: 'Lan Phương', text: 'File audio tập 88 không load được.', story: 'Đấu Phá Thương Khung' }
]

const uploadJobs = [
  { name: 'Đấu Phá - chương 1649.mp3', progress: 72, status: 'Đang upload lên storage' },
  { name: 'Thần Mộ - chương 682.mp3', progress: 44, status: 'Đang xử lý transcript' },
  { name: 'Ma Thổi Đèn - tập 313.mp3', progress: 91, status: 'Chờ publish' }
]

const plans = [
  { name: 'VIP tháng', revenue: '38.2M', orders: 418 },
  { name: 'VIP quý', revenue: '26.7M', orders: 164 },
  { name: 'VIP năm', revenue: '21.5M', orders: 52 }
]

const currentTitle = computed(() => menuItems.find((item) => item.id === activeSection.value)?.label || 'Tổng quan')

const filteredStories = computed(() => {
  const keyword = search.value.trim().toLowerCase()
  return stories.filter((story) => {
    const matchesFilter = activeFilter.value === 'Tất cả' || story.status === activeFilter.value
    const matchesSearch = !keyword || [story.title, story.author, story.narrator, story.category].some((value) => value.toLowerCase().includes(keyword))
    return matchesFilter && matchesSearch
  })
})

export function useAdminDashboard() {
  return {
    sidebarOpen,
    search,
    activeSection,
    activeFilter,
    menuItems,
    metrics,
    statusFilters,
    stories,
    episodes,
    categories,
    tags,
    users,
    orders,
    crawlJobs,
    comments,
    uploadJobs,
    plans,
    currentTitle,
    filteredStories
  }
}
