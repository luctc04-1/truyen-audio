# 📖 Vue 3 Frontend Components & Pages Overview

## 🎯 Cấu trúc đã tạo

### 1️⃣ Entry Point

**`resources/js/main.js`**
- Khởi tạo Vue app
- Setup Pinia stores
- Setup Vue Router

**`resources/js/App.vue`**
- Root component
- CSS variables (dark theme)
- Layout: Header + Main + Player

---

## 🧩 Components (Reusable)

### AppHeader.vue
```vue
<template>
  <header class="header">
    <nav> <!-- Home, Library, Community, VIP -->
    <div> <!-- Notification, Profile buttons -->
</template>
```
- Sticky header
- Navigation links với active state
- Icons buttons

### AudioPlayer.vue
```vue
<template>
  <div class="audio-player">
    <progress-bar />
    <thumbnail />
    <title-info />
    <controls /> <!-- Play, pause, etc -->
</template>
```
- Fixed bottom player
- Progress bar
- Play/pause controls
- Displays current story

### StoryCard.vue
```vue
<template>
  <router-link class="story-card">
    <thumbnail>
    <title>
    <author>
    <meta> <!-- plays, episodes -->
</template>
```
- Responsive card
- Hover effects
- Link to detail page
- Status badge

---

## 📄 Views (Pages)

### 🏠 HomePage.vue

**URL:** `/`

**Components:**
- Hero section (banner + CTA)
- Genre pills (horizontal scroll)
- Trending stories grid

**Features:**
- "Khám phá ngay" button → /library
- "Đăng ký VIP" button → /vip
- Genre filter
- Story cards grid

**Data:**
- Mock stories from store
- Genre list

---

### 📚 LibraryPage.vue

**URL:** `/library`

**Components:**
- Search bar
- Sort dropdown
- Genre sidebar (desktop)
- Stories grid/list
- Pagination

**Features:**
- Search by title/author
- Sort: trending, newest, popular, rating
- Filter by genre
- Paginate results (12 per page)
- Responsive: sidebar hidden on mobile

**Data:**
- Stories filtered by genre
- Genre count
- Pagination state

---

### 📖 StoryDetailPage.vue

**URL:** `/story/:id`

**Components:**
- Back link
- Cover image
- Info section
  - Badges (genre, status, completed)
  - Title, author
  - Stats (plays, episodes, rating)
  - Action buttons
- Tabs: Episodes | Reviews
- Episodes list

**Features:**
- Get story by ID from router param
- Play/Pause button
- Follow/Unfollow button
- Share button
- Episodes list with play buttons
- Episode metadata (duration)

**Data:**
- Story details
- Episodes list
- Follow status

---

### 🎧 EpisodePage.vue

**URL:** `/episode/:id`

**Status:** 🚧 Skeleton (placeholder)

**Will include:**
- Full episode player
- Episode metadata
- Next/Previous buttons
- Comment section

---

### 💬 CommunityPage.vue

**URL:** `/community`

**Status:** 🚧 Skeleton (placeholder)

**Will include:**
- Write post textarea
- Post tags (discussion, suggestion, question, bug)
- Filter tabs
- Posts list
- Like/Comment actions

---

### ✨ VIPPage.vue

**URL:** `/vip`

**Status:** 🚧 Skeleton (placeholder)

**Will include:**
- VIP hero section
- Plans grid (1 month, 3 months, 6 months, 12 months)
- Features list
- Payment methods
- Subscribe button

---

### 👤 ProfilePage.vue

**URL:** `/profile`

**Status:** 🚧 Skeleton (placeholder)

**Will include:**
- User info (avatar, name, email)
- Stats (listen count, favorite stories)
- VIP status card
- Listen history
- Settings

---

## 🔀 Router Configuration

**`resources/js/router/index.js`**

```javascript
{
  path: '/',
  name: 'Home',
  component: HomePage
},
{
  path: '/library',
  name: 'Library',
  component: LibraryPage
},
{
  path: '/story/:id',
  name: 'StoryDetail',
  component: StoryDetailPage
},
// ... more routes
```

**Navigation:**
```javascript
// Programmatic
this.$router.push({ name: 'StoryDetail', params: { id: 1 } })

// Template
<router-link to="/library">Kho truyện</router-link>
```

---

## 📦 State Management (Pinia)

### storyStore.js

**State:**
```javascript
stories              // Array of all stories
filteredStories      // Filtered by genre
selectedGenre        // Current genre filter
currentStory         // Now playing
currentEpisode       // Current episode
isPlaying            // Play/pause state
currentTime          // Playback time
duration             // Total duration
```

**Actions:**
```javascript
loadStories()           // Fetch all stories
filterStories()         // Apply genre filter
setGenre(genreId)       // Change genre
getStoryById(id)        // Get single story
playStory(story)        // Start playing
pause() / resume()      // Control playback
setCurrentTime(time)    // Update position
```

**Mock Data:**
- 2 sample stories with episodes
- 11 genres
- Plays count, ratings, status

---

### userStore.js

**State:**
```javascript
user {
  id
  name
  email
  avatar
  isVIP
  vipExpireDate
  listenHistory
}
isAuthenticated       // Login state
```

**Actions:**
```javascript
login(email, password)        // Authenticate
logout()                      // Clear session
subscribeVIP(plan)            // Subscribe
addToHistory(story, episode)  // Log listen
```

---

## 🎨 Styling System

### CSS Variables (Dark Theme)

```css
:root {
  /* Colors */
  --bg: #09090b              /* Background */
  --bg-card: #111113         /* Card background */
  --text: #fafafa            /* Text */
  --text-muted: #a1a1aa      /* Muted text */
  --primary: #a855f7         /* Purple accent */
  --success: #22c55e         /* Green */
  
  /* Gradients */
  --gradient-premium: linear-gradient(135deg, #a855f7, #ec4899, #f97316)
  
  /* Sizing */
  --radius-sm: 8px
  --radius-lg: 16px
  --nav-height: 56px
  --player-height: 80px
  --container: 1200px
  
  /* Effects */
  --shadow-md: 0 4px 12px rgba(0,0,0,0.4)
}
```

### Utility Classes

```css
.btn-primary              /* Primary button */
.btn-outline              /* Outline button */
.badge                    /* Status badge */
.text-gradient            /* Gradient text */
.container                /* Responsive container */
```

---

## 📱 Responsive Breakpoints

```css
Mobile (default)     < 640px
Tablet              640px - 1024px
Desktop             > 1024px
```

**Examples:**
- Library: Sidebar hidden on mobile
- Grid: 1 col mobile → 4 col desktop
- Nav: Icons only → Icons + text

---

## 🔗 Integration Points

### API Endpoints needed

```
GET  /api/stories              → List all stories
GET  /api/stories/:id          → Get single story
GET  /api/stories/:id/episodes → Get episodes
POST /api/login                → User login
POST /api/vip/subscribe        → Subscribe VIP
```

### Update Mock → Real Data

```javascript
// Current (mock)
const mockStories = [...]
loadStories() { stories.value = mockStories }

// Update to (API)
async loadStories() {
  const res = await fetch('/api/stories')
  stories.value = await res.json()
}
```

---

## ✨ Features Ready

✅ **Routing** - SPA with Vue Router
✅ **State** - Pinia store management
✅ **Components** - Reusable UI components
✅ **Dark Theme** - CSS variables based
✅ **Responsive** - Mobile-first design
✅ **Search/Filter** - Story library
✅ **Pagination** - Handle large lists
✅ **Mock Data** - Ready for testing

---

## 🚀 Development Workflow

```bash
# 1. Start dev server
npm run dev

# 2. Open browser
http://localhost:5173

# 3. Edit files (hot reload works)
# Changes appear instantly

# 4. Build production
npm run build
# Output: public/build/

# 5. Deploy
# Copy public/build/ to production
```

---

## 📋 File Checklist

- ✅ `App.vue` - Root component + global styles
- ✅ `main.js` - App entry point
- ✅ `router/index.js` - Route definitions
- ✅ `stores/storyStore.js` - Story state
- ✅ `stores/userStore.js` - User state
- ✅ `components/AppHeader.vue` - Navigation header
- ✅ `components/AudioPlayer.vue` - Audio player
- ✅ `components/StoryCard.vue` - Story card
- ✅ `views/HomePage.vue` - Home page
- ✅ `views/LibraryPage.vue` - Library page
- ✅ `views/StoryDetailPage.vue` - Story detail
- ✅ `views/EpisodePage.vue` - Episode player (skeleton)
- ✅ `views/CommunityPage.vue` - Community (skeleton)
- ✅ `views/VIPPage.vue` - VIP plans (skeleton)
- ✅ `views/ProfilePage.vue` - Profile (skeleton)
- ✅ `resources/views/app.blade.php` - Vue mount
- ✅ `vite.config.js` - Vite config
- ✅ `package.json` - Dependencies
- ✅ `routes/web.php` - Laravel routes
- ✅ `SETUP_GUIDE.md` - Setup instructions
- ✅ `QUICK_START.md` - Quick start guide

---

**All files ready! Run `npm run dev` to start coding! 🎉**
