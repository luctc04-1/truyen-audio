<template>
  <div class="card admin-datatable-card">
    <div v-if="title || $slots.filters" class="card-header border-0">
      <h5 v-if="title" class="card-title mb-0">{{ title }}</h5>
      <div v-if="$slots.filters" class="mt-3">
        <slot name="filters" />
      </div>
    </div>

    <div class="card-body">
      <div v-if="loading" class="loading-overlay">
        <div class="spinner-border text-primary" role="status" />
      </div>

      <div v-else-if="error" class="admin-error">
        <i class="ri-error-warning-line fs-1 text-danger"></i>
        <p class="mb-0">{{ error }}</p>
        <button class="btn btn-primary btn-sm" type="button" @click="$emit('retry')">Thử lại</button>
      </div>

      <div v-else-if="!rows.length" class="admin-empty text-muted">
        <i v-if="emptyIcon" :class="[emptyIcon, 'fs-1']"></i>
        <p class="mb-0">{{ emptyText }}</p>
      </div>

      <div v-else class="dataTables_wrapper dt-bootstrap5 no-footer">
        <div v-if="showLengthMenu" class="row align-items-center mb-3 g-2">
          <div class="col-sm-12 col-md-6">
            <div class="dataTables_length">
              <label class="d-flex align-items-center gap-2 mb-0">
                <span>Hiển thị</span>
                <select
                  class="form-select form-select-sm"
                  style="width: auto"
                  :value="perPage"
                  @change="onPerPageChange"
                >
                  <option v-for="size in pageSizes" :key="size" :value="size">{{ size }}</option>
                </select>
                <span>bản ghi</span>
              </label>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-striped align-middle nowrap w-100 admin-dt-table mb-0">
            <thead class="table-light">
              <tr>
                <th
                  v-for="col in columns"
                  :key="col.key"
                  :class="col.thClass"
                  :style="col.width ? { width: col.width } : undefined"
                >
                  {{ col.label }}
                </th>
              </tr>
            </thead>
            <tbody>
              <template v-for="(row, index) in rows" :key="resolveKey(row, index)">
                <tr>
                  <td
                    v-for="col in columns"
                    :key="col.key"
                    :class="col.tdClass"
                  >
                    <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]" :expanded="isExpanded(row, index)">
                      {{ formatCell(row, col) }}
                    </slot>
                  </td>
                </tr>
                <tr v-if="hasExpandSlot && isExpanded(row, index)" class="admin-dt-expand-row">
                  <td :colspan="columns.length" class="p-0 border-0">
                    <slot name="row-expand" :row="row" />
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <div v-if="lastPage > 1 || showLengthMenu" class="row align-items-center mt-3 g-2">
          <div v-if="lastPage > 1" class="col-sm-12 col-md-5">
            <div class="dataTables_info text-muted">
              Hiển thị {{ rangeFrom }} đến {{ rangeTo }} trong tổng {{ total }} bản ghi
            </div>
          </div>
          <div v-if="lastPage > 1" class="col-sm-12 col-md-7">
            <div class="dataTables_paginate paging_simple_numbers">
              <ul class="pagination pagination-separated justify-content-md-end justify-content-center mb-0">
                <li class="page-item" :class="{ disabled: currentPage <= 1 }">
                  <button class="page-link" type="button" :disabled="currentPage <= 1" @click="goPage(currentPage - 1)">
                    Trước
                  </button>
                </li>
                <li
                  v-for="page in visiblePages"
                  :key="page"
                  class="page-item"
                  :class="{ active: page === currentPage, disabled: page === '...' }"
                >
                  <button
                    v-if="page !== '...'"
                    class="page-link"
                    type="button"
                    @click="goPage(page)"
                  >
                    {{ page }}
                  </button>
                  <span v-else class="page-link">…</span>
                </li>
                <li class="page-item" :class="{ disabled: currentPage >= lastPage }">
                  <button class="page-link" type="button" :disabled="currentPage >= lastPage" @click="goPage(currentPage + 1)">
                    Sau
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, useSlots } from 'vue'

const props = defineProps({
  title: { type: String, default: '' },
  columns: { type: Array, required: true },
  rows: { type: Array, default: () => [] },
  rowKey: { type: String, default: 'id' },
  expandedKeys: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  error: { type: String, default: '' },
  emptyText: { type: String, default: 'Không có dữ liệu' },
  emptyIcon: { type: String, default: 'ri-inbox-line' },
  pagination: {
    type: Object,
    default: () => ({ current_page: 1, last_page: 1, total: 0, per_page: 20, from: 0, to: 0 }),
  },
  perPage: { type: Number, default: 20 },
  pageSizes: { type: Array, default: () => [10, 20, 50, 100] },
  showLengthMenu: { type: Boolean, default: true },
})

const emit = defineEmits(['page-change', 'update:perPage', 'retry'])

const slots = useSlots()
const hasExpandSlot = computed(() => !!slots['row-expand'])

const isExpanded = (row, index) => {
  const key = resolveKey(row, index)
  return props.expandedKeys.includes(key)
}

const resolveKey = (row, index) => row[props.rowKey] ?? index
const lastPage = computed(() => props.pagination.last_page || 1)
const total = computed(() => props.pagination.total ?? props.rows.length)
const rangeFrom = computed(() => props.pagination.from || (props.rows.length ? 1 : 0))
const rangeTo = computed(() => props.pagination.to || props.rows.length)

const visiblePages = computed(() => {
  const totalPages = lastPage.value
  const current = currentPage.value
  if (totalPages <= 7) {
    return Array.from({ length: totalPages }, (_, i) => i + 1)
  }

  const pages = [1]
  if (current > 3) pages.push('...')
  const start = Math.max(2, current - 1)
  const end = Math.min(totalPages - 1, current + 1)
  for (let i = start; i <= end; i += 1) pages.push(i)
  if (current < totalPages - 2) pages.push('...')
  pages.push(totalPages)
  return pages
})

const currentPage = computed(() => props.pagination.current_page || 1)

const formatCell = (row, col) => {
  const value = row[col.key]
  if (value === null || value === undefined || value === '') return '—'
  return value
}

const goPage = (page) => {
  if (page < 1 || page > lastPage.value || page === currentPage.value) return
  emit('page-change', page)
}

const onPerPageChange = (event) => {
  emit('update:perPage', Number(event.target.value))
  emit('page-change', 1)
}
</script>
