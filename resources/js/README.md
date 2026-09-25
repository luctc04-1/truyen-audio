# Vue 3 Frontend - Truyện Audio

Giao diện frontend Vue 3 cho ứng dụng nghe truyện audio.

## 📁 Cấu trúc dự án

```
resources/
├── js/
│   ├── App.vue                 # Shell layout gốc
│   ├── main.js                 # Entry point duy nhất
│   ├── admin/                  # Module quản trị khép kín
│   │   ├── AdminPage.vue       # Trang Admin chính
│   │   └── components/         # Các components quản trị
│   ├── components/             # Components client chia theo domain
│   │   ├── common/             # UI atoms (Badge, Avatar, StarRating, Spinner...)
│   │   ├── feedback/           # Modals, Dialogs, Toasts, WebPush
│   │   ├── layout/             # Header, AudioPlayer, NowPlaying, Notifications
│   │   ├── skeletons/          # Loading skeletons
│   │   ├── story/              # Story cards, episodes, reviews, comments
│   │   ├── community/          # Bài đăng cộng đồng, bình luận thảo luận
│   │   └── index.js            # Barrel re-export thuận tiện
│   ├── views/                  # Client pages (Home, Library, StoryDetail, VIP...)
│   ├── router/                 # Vue Router config với navigation guards
│   ├── stores/                 # Pinia stores (auth, audio, story, notif, toast)
│   ├── services/               # API Services & WebSocket (Echo)
│   ├── composables/            # Composition API hooks tái sử dụng
│   └── utils/                  # Helpers, SEO, Access control
├── css/
│   ├── variables.css           # Toàn bộ CSS variables (Dark/Light theme)
│   ├── base.css                # CSS resets, fonts, custom scrollbars
│   ├── app.css                 # File tổng hợp được bundle bởi Vite
│   └── admin/                  # Stylesheet riêng cho Admin
└── views/
    └── app.blade.php           # Laravel blade template mount #app
```

## 🚀 Setup

### 1. Cài đặt dependencies

```bash
npm install
```

Các packages cần:
- `vue@3`
- `vue-router@4`
- `pinia`
- `vite`

### 2. Update `package.json`

```json
{
  "private": true,
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  },
  "devDependencies": {
    "@vitejs/plugin-vue": "^4.0.0",
    "vite": "^4.0.0"
  },
  "dependencies": {
    "pinia": "^2.1.0",
    "vue": "^3.3.0",
    "vue-router": "^4.2.0"
  }
}
```

### 3. Update `vite.config.js`

```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    port: 5173,
  },
  build: {
    outDir: 'public/dist',
    manifest: true,
  }
})
```

### 4. Update `resources/views/app.blade.php`

```html
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#09090b">
    <title>Truyện Audio</title>
    @vite(['resources/js/main.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
```

## 📱 Pages

### 🏠 Trang Chủ (HomePage)
- Hero section với banner
- Danh sách thể loại (genres)
- Truyện đang xu hướng

### 📚 Kho Truyện (LibraryPage)
- Tìm kiếm truyện
- Lọc theo thể loại
- Sắp xếp (trending, newest, popular, rating)
- Phân trang

### 📖 Chi Tiết Truyện (StoryDetailPage)
- Thông tin truyện
- Danh sách tập (episodes)
- Đánh giá (reviews)
- Nút nghe, theo dõi, chia sẻ

### 🎧 Phát Audio (EpisodePage)
- Player audio
- Điều khiển playback

### 💬 Cộng Đồng (CommunityPage)
- Viết bài đăng
- Các bộ lọc (discussion, suggestion, question, bug)
- Bình luận

### ✨ VIP (VIPPage)
- Các gói VIP
- Phương thức thanh toán
- Lợi ích VIP

### 👤 Tài Khoản (ProfilePage)
- Thông tin người dùng
- Lịch sử nghe
- Trạng thái VIP

## 🎨 Styling

Toàn bộ ứng dụng sử dụng CSS variables được định nghĩa trong `App.vue`:

```css
:root {
  --bg: #09090b;
  --text: #fafafa;
  --primary: #a855f7;
  --gradient-premium: linear-gradient(135deg, #a855f7 0%, #ec4899 50%, #f97316 100%);
  /* ... more variables */
}
```

Dark theme với design system modern.

## 📦 State Management (Pinia)

### storyStore
- `stories` - Danh sách truyện
- `filteredStories` - Truyện đã lọc
- `selectedGenre` - Thể loại hiện tại
- `currentStory` - Truyện đang phát

### userStore
- `user` - Thông tin người dùng
- `isAuthenticated` - Đăng nhập?
- Methods: `login`, `logout`, `subscribeVIP`

## 🔗 Router

Routes tự động được setup trong `router/index.js`:
- `/` - Home
- `/library` - Kho truyện
- `/story/:id` - Chi tiết truyện
- `/episode/:id` - Phát audio
- `/community` - Cộng đồng
- `/vip` - VIP
- `/profile` - Tài khoản

## 🌐 Integration với Laravel

Backend API endpoints sẽ được call từ các stores. Update mock data trong stores với actual API calls:

```javascript
// storyStore.js
const loadStories = async () => {
  const response = await fetch('/api/stories')
  stories.value = await response.json()
}
```

## 📝 Notes

- Hiện tại sử dụng mock data
- Cần integrate với backend API
- Responsive design (mobile-first)
- Dark theme by default
- Modern UI components

## 🛠️ Development

```bash
npm run dev       # Start dev server
npm run build     # Build for production
npm run preview   # Preview production build
```

Dev server chạy tại `http://localhost:5173`
