import apiService from './ApiService'

class AdminService {
  // ─── Dashboard ──────────────────────────────────────────────────────────
  async getDashboardStats() {
    return apiService.get('/admin/dashboard/stats')
  }

  // ─── Series (Truyện) ───────────────────────────────────────────────────
  async getSeries(params = {}) {
    return apiService.get('/admin/series', params)
  }

  async getSeriesDetail(id) {
    return apiService.get(`/admin/series/${id}`)
  }

  async createSeries(data) {
    return apiService.post('/admin/series', data)
  }

  async updateSeries(id, data) {
    return apiService.patch(`/admin/series/${id}`, data)
  }

  async toggleSeriesHot(id) {
    return apiService.post(`/admin/series/${id}/toggle-hot`)
  }

  async getHotSeries(params = {}) {
    return apiService.get('/admin/series/hot', params)
  }

  async reorderHotSeries(items) {
    return apiService.post('/admin/series/reorder-hot', { items })
  }

  async setSeriesHotOrder(id, hotOrder, isHot = true) {
    return apiService.post(`/admin/series/${id}/set-hot-order`, {
      hot_order: hotOrder,
      is_hot: isHot,
    })
  }

  async toggleSeriesPremium(id) {
    return apiService.post(`/admin/series/${id}/toggle-premium`)
  }

  async deleteSeries(id) {
    return apiService.delete(`/admin/series/${id}`)
  }

  async getCategories() {
    return apiService.get('/admin/series/categories')
  }

  // ─── Episodes (Tập audio) ───────────────────────────────────────────────
  async getEpisodes(params = {}) {
    return apiService.get('/admin/episodes', params)
  }

  async getEpisodeDetail(id) {
    return apiService.get(`/admin/episodes/${id}`)
  }

  async createEpisode(data) {
    return apiService.post('/admin/episodes', data)
  }

  async updateEpisode(id, data) {
    return apiService.patch(`/admin/episodes/${id}`, data)
  }

  async deleteEpisode(id) {
    return apiService.delete(`/admin/episodes/${id}`)
  }

  // ─── Users (Người dùng) ────────────────────────────────────────────────
  async getUsers(params = {}) {
    return apiService.get('/admin/users', params)
  }

  async getUserDetail(id) {
    return apiService.get(`/admin/users/${id}`)
  }

  async toggleUserAdmin(id) {
    return apiService.post(`/admin/users/${id}/toggle-admin`)
  }

  async grantUserVip(id, data) {
    return apiService.post(`/admin/users/${id}/grant-vip`, data)
  }

  // ─── Orders (Đơn hàng VIP) ─────────────────────────────────────────────
  async getOrders(params = {}) {
    return apiService.get('/admin/orders', params)
  }

  async updateOrderStatus(id, status) {
    return apiService.patch(`/admin/orders/${id}/status`, { status })
  }

  // ─── Plans (Gói cước VIP) ──────────────────────────────────────────────
  async getPlans() {
    return apiService.get('/admin/plans')
  }

  async createPlan(data) {
    return apiService.post('/admin/plans', data)
  }

  async updatePlan(id, data) {
    return apiService.patch(`/admin/plans/${id}`, data)
  }

  async deletePlan(id) {
    return apiService.delete(`/admin/plans/${id}`)
  }

  // ─── Community & Comments (Kiểm duyệt Cộng đồng & Bình luận) ───────────
  async getCommunityStats() {
    return apiService.get('/admin/community/stats')
  }

  async getCommunityPosts(params = {}) {
    return apiService.get('/admin/community/posts', params)
  }

  async getCommunityPostDetail(id) {
    return apiService.get(`/admin/community/posts/${id}`)
  }

  async deleteCommunityPost(id) {
    return apiService.delete(`/admin/community/posts/${id}`)
  }

  async batchDeleteCommunityPosts(ids = []) {
    return apiService.post('/admin/community/posts/batch-delete', { ids })
  }

  async getComments(params = {}) {
    return apiService.get('/admin/community/comments', params)
  }

  async deleteComment(id, type = 'series') {
    return apiService.delete(`/admin/community/comments/${id}`, { type })
  }

  async batchDeleteComments(ids = [], type = 'series') {
    return apiService.post('/admin/community/comments/batch-delete', { ids, type })
  }

  // ─── Settings (Cài đặt) ────────────────────────────────────────────────
  async getSettings() {
    return apiService.get('/admin/settings')
  }

  async updateSettings(data) {
    return apiService.post('/admin/settings', data)
  }

  // ─── Sync & Crawler ────────────────────────────────────────────────────
  async getSyncStatus() {
    return apiService.get('/admin/sync/status')
  }

  async syncSeries(authKey = null) {
    return apiService.post('/admin/sync/series', authKey ? { auth_key: authKey } : {})
  }

  async syncEpisodes(seriesId = null, authKey = null) {
    const payload = {}
    if (seriesId) payload.series_id = seriesId
    if (authKey) payload.auth_key = authKey
    return apiService.post('/admin/sync/episodes', payload)
  }

  async syncAll(authKey = null) {
    return apiService.post('/admin/sync/all', authKey ? { auth_key: authKey } : {})
  }
}

export default new AdminService()
