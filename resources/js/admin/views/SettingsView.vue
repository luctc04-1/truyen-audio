<template>
  <div>
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Cài đặt hệ thống</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><router-link to="/admin">Admin</router-link></li>
              <li class="breadcrumb-item active">Cài đặt</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay"><div class="spinner-border text-primary"></div></div>

    <div v-else class="row">
      <div class="col-xl-6">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">Website</h5></div>
          <div class="card-body">
            <label class="form-label">Tên website</label>
            <input v-model="form.site_name" class="form-control mb-3" />
            <label class="form-label">Domain chính</label>
            <input v-model="form.site_url" class="form-control mb-3" />
            <label class="form-label">Storage audio</label>
            <select v-model="form.storage_driver" class="form-select mb-3">
              <option value="local">Local storage</option>
              <option value="s3">S3 compatible</option>
            </select>
            <label class="form-label">Số tập free trước VIP</label>
            <input v-model.number="form.free_episodes_before_vip" type="number" min="0" class="form-control mb-3" />
            <div class="form-check form-switch mb-3">
              <input id="reg" v-model="form.registration_enabled" class="form-check-input" type="checkbox" />
              <label class="form-check-label" for="reg">Bật đăng ký mới</label>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">SEO mặc định</h5></div>
          <div class="card-body">
            <label class="form-label">Meta title</label>
            <input v-model="form.default_meta_title" class="form-control mb-3" />
            <label class="form-label">Meta description</label>
            <textarea v-model="form.default_meta_description" class="form-control mb-3" rows="3"></textarea>
            <label class="form-label">OG image</label>
            <input v-model="form.default_og_image" class="form-control mb-3" />
            <label class="form-label">Robots</label>
            <select v-model="form.robots" class="form-select mb-3">
              <option value="index,follow">index,follow</option>
              <option value="noindex,nofollow">noindex,nofollow</option>
            </select>
          </div>
        </div>
      </div>

      <div class="col-12">
        <button class="btn btn-primary" type="button" :disabled="saving" @click="save">
          <i class="ri-save-3-line me-1"></i>{{ saving ? 'Đang lưu...' : 'Lưu cài đặt' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import AdminService from '@/services/AdminService'
import { extractApiPayload } from '@/utils/helpers'
import { useToastStore } from '@/stores/toastStore'

const toast = useToastStore()
const loading = ref(true)
const saving = ref(false)
const form = reactive({
  site_name: '',
  site_url: '',
  storage_driver: 'local',
  free_episodes_before_vip: 10,
  registration_enabled: true,
  default_meta_title: '',
  default_meta_description: '',
  default_og_image: '',
  robots: 'index,follow',
})

onMounted(async () => {
  try {
    const data = extractApiPayload(await AdminService.getSettings())
    Object.assign(form, data)
  } finally {
    loading.value = false
  }
})

const save = async () => {
  saving.value = true
  try {
    const data = extractApiPayload(await AdminService.updateSettings({ ...form }))
    Object.assign(form, data)
    toast.success('Đã lưu cài đặt')
  } catch {
    toast.error('Không thể lưu cài đặt')
  } finally {
    saving.value = false
  }
}
</script>
