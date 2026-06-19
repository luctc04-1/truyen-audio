# 🚀 SETUP HƯỚNG DẪN - Vue 3 Frontend

## Bước 1: Cài đặt Dependencies

```bash
npm install
```

Hoặc nếu dùng Composer:
```bash
composer require --dev @vitejs/plugin-vue
```

## Bước 2: Cấu hình Route (routes/web.php)

Thêm route cho app:

```php
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
```

## Bước 3: Chạy Development Server

**Terminal 1 - Vite Dev Server:**
```bash
npm run dev
```

**Terminal 2 - Laravel Dev Server (nếu cần):**
```bash
php artisan serve
```

Vite sẽ chạy trên `http://localhost:5173`
Laravel sẽ chạy trên `http://localhost:8000`

## Bước 4: Build cho Production

```bash
npm run build
```

Kết quả sẽ được output vào `public/build/`

## 📁 Cấu trúc File

```
resources/
├── js/
│   ├── main.js                      # Entry point
│   ├── App.vue                      # Root component
│   ├── router/
│   │   └── index.js                 # Vue Router
│   ├── stores/
│   │   ├── storyStore.js            # Pinia store cho stories
│   │   └── userStore.js             # Pinia store cho user
│   ├── components/
│   │   ├── AppHeader.vue            # Header component
│   │   ├── AudioPlayer.vue          # Audio player
│   │   └── StoryCard.vue            # Story card
│   └── views/
│       ├── HomePage.vue             # 🏠 Trang chủ
│       ├── LibraryPage.vue          # 📚 Kho truyện
│       ├── StoryDetailPage.vue      # 📖 Chi tiết truyện
│       ├── EpisodePage.vue          # 🎧 Phát audio
│       ├── CommunityPage.vue        # 💬 Cộng đồng
│       ├── VIPPage.vue              # ✨ VIP
│       └── ProfilePage.vue          # 👤 Tài khoản
├── views/
│   └── app.blade.php                # Laravel template
└── css/
    └── app.css
```

## 🎨 Giao diện chính

### Trang Chủ
- Hero banner với CTA
- Danh sách thể loại (genres)
- Truyện xu hướng

### Kho Truyện
- Tìm kiếm
- Lọc theo thể loại
- Sắp xếp (trending, newest, popular, rating)
- Grid view / List view
- Phân trang

### Chi tiết Truyện
- Thông tin truyện (cover, title, author)
- Stats (plays, episodes, rating)
- Nút action (nghe, theo dõi, chia sẻ)
- Danh sách tập (episodes)
- Tab đánh giá (reviews)

### Phát Audio
- Audio player với controls
- Progress bar
- Danh sách tập

### Cộng đồng
- Form viết bài
- Danh sách posts
- Filter posts (discussion, suggestion, question, bug)
- Likes và comments

### VIP
- Các gói VIP với giá
- So sánh lợi ích
- Thanh toán

### Tài khoản
- Thông tin user
- Lịch sử nghe
- Trạng thái VIP
- Settings

## 🌐 API Integration

Các stores hiện dùng mock data. Update để call API:

```javascript
// storyStore.js
const loadStories = async () => {
  try {
    const response = await fetch('/api/stories')
    const data = await response.json()
    stories.value = data
  } catch (error) {
    console.error('Error loading stories:', error)
  }
}
```

Backend endpoints cần:
- `GET /api/stories` - Danh sách truyện
- `GET /api/stories/{id}` - Chi tiết truyện
- `GET /api/stories/{id}/episodes` - Tập của truyện
- `POST /api/login` - Đăng nhập
- `POST /api/vip/subscribe` - Đăng ký VIP

## 🎯 Tính năng

✅ Responsive Design (Mobile-first)
✅ Dark Theme by default
✅ Vue Router SPA routing
✅ Pinia State Management
✅ Mock Data for Testing
✅ Audio Player Component
✅ Search & Filter
✅ Pagination

## 🔧 Troubleshooting

### HMR không hoạt động
Update `vite.config.js`:
```javascript
server: {
  hmr: {
    host: 'localhost',
    port: 5173,
  },
},
```

### Build error
- Xóa `node_modules/` và `package-lock.json`
- Chạy lại `npm install`
- Chạy `npm run build`

### CSS không load
- Check `resources/js/main.js` import styles
- Rebuild với `npm run build`

## 📞 Hỗ trợ

Để debug:
- Mở DevTools (F12)
- Check Console for errors
- Kiểm tra Network tab

## ✅ Checklist

- [ ] Cài npm packages
- [ ] Cấu hình routes/web.php
- [ ] Chạy dev server
- [ ] Test các pages
- [ ] Integrate với API backend
- [ ] Build production
