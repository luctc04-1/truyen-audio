import { createRouter, createWebHistory } from 'vue-router'

// Views
import HomePage from '../../views/HomePage.vue'
import LibraryPage from '../../views/LibraryPage.vue'
import StoryDetailPage from '../../views/StoryDetailPage.vue'
import EpisodePage from '../../views/EpisodePage.vue'
import CommunityPage from '../../views/CommunityPage.vue'
import VIPPage from '../../views/VIPPage.vue'
import ProfilePage from '../../views/ProfilePage.vue'

const routes = [
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
  {
    path: '/episode/:id',
    name: 'Episode',
    component: EpisodePage
  },
  {
    path: '/community',
    name: 'Community',
    component: CommunityPage
  },
  {
    path: '/vip',
    name: 'VIP',
    component: VIPPage
  },
  {
    path: '/profile',
    name: 'Profile',
    component: ProfilePage
  }
]

const router = createRouter({
  history: createWebHistory('/'),
  routes
})

export default router
